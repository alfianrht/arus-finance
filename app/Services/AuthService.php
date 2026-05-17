<?php

namespace App\Services;

use App\Models\InstitutionModel;
use App\Models\RememberTokenModel;
use App\Models\UserModel;
use CodeIgniter\I18n\Time;
use RuntimeException;

class AuthService
{
    public const REMEMBER_COOKIE = 'arus_remember';

    public const REMEMBER_DAYS = 30;

    private UserModel $users;

    private InstitutionModel $institutions;

    private RememberTokenModel $rememberTokens;

    public function __construct(?UserModel $users = null, ?InstitutionModel $institutions = null, ?RememberTokenModel $rememberTokens = null)
    {
        $this->users = $users ?? new UserModel();
        $this->institutions = $institutions ?? new InstitutionModel();
        $this->rememberTokens = $rememberTokens ?? new RememberTokenModel();
    }

    public function normalizeWhatsapp(string $value): string
    {
        $digits = preg_replace('/\D+/', '', $value) ?? '';

        if (str_starts_with($digits, '0')) {
            $digits = '62' . substr($digits, 1);
        }

        if (! str_starts_with($digits, '62')) {
            $digits = '62' . ltrim($digits, '0');
        }

        return $digits;
    }

    public function isValidWhatsapp(string $value): bool
    {
        return (bool) preg_match('/^62\d{8,13}$/', $value);
    }

    public function requestLoginOtp(string $whatsapp): array
    {
        $normalized = $this->normalizeWhatsapp($whatsapp);
        $user = $this->users->where('whatsapp', $normalized)->where('is_active', 1)->first();

        if ($user === null) {
            throw new RuntimeException('Nomor WhatsApp belum terdaftar.');
        }

        return $this->issueOtp($user, 'login');
    }

    public function registerAndRequestOtp(string $name, string $whatsapp): array
    {
        $normalized = $this->normalizeWhatsapp($whatsapp);
        $existing = $this->users->where('whatsapp', $normalized)->first();

        if ($existing !== null) {
            throw new RuntimeException('Nomor WhatsApp sudah terdaftar. Silakan masuk.');
        }

        $institutionId = $this->createInstitutionForRegistration($name, $normalized);
        $userId = $this->users->insert([
            'institution_id' => $institutionId,
            'name' => $name,
            'whatsapp' => $normalized,
            'role' => 'admin',
            'is_active' => 1,
            'otp_attempts' => 0,
        ], true);

        $user = $this->users->find($userId);

        if ($user === null) {
            throw new RuntimeException('User gagal dibuat.');
        }

        return $this->issueOtp($user, 'register');
    }

    public function requestRecoveryOtp(string $whatsapp): array
    {
        $normalized = $this->normalizeWhatsapp($whatsapp);
        $user = $this->users->where('whatsapp', $normalized)->where('is_active', 1)->first();

        if ($user === null) {
            throw new RuntimeException('Nomor WhatsApp tidak ditemukan.');
        }

        return $this->issueOtp($user, 'recovery');
    }

    public function verifyOtp(int $userId, string $otp): array
    {
        $user = $this->users->find($userId);

        if ($user === null) {
            throw new RuntimeException('Data user tidak ditemukan.');
        }

        if (($user['otp_attempts'] ?? 0) >= 3) {
            throw new RuntimeException('Percobaan OTP habis. Silakan minta kode baru.');
        }

        if (($user['otp_expires_at'] ?? null) === null || Time::parse((string) $user['otp_expires_at'])->isBefore(Time::now())) {
            throw new RuntimeException('Kode OTP sudah kadaluarsa.');
        }

        if (($user['otp_code'] ?? '') !== trim($otp)) {
            $this->users->update($userId, [
                'otp_attempts' => ((int) ($user['otp_attempts'] ?? 0)) + 1,
            ]);

            throw new RuntimeException('Kode OTP tidak sesuai.');
        }

        $this->users->update($userId, [
            'otp_code' => null,
            'otp_expires_at' => null,
            'otp_attempts' => 0,
            'last_login_at' => Time::now()->toDateTimeString(),
        ]);

        return $this->users->find($userId) ?? $user;
    }

    public function resendOtpForUser(int $userId, string $flow = 'login'): array
    {
        $user = $this->users->find($userId);

        if ($user === null) {
            throw new RuntimeException('Data user tidak ditemukan.');
        }

        return $this->issueOtp($user, $flow);
    }

    public function currentOtpPreview(int $userId): ?string
    {
        $user = $this->users->find($userId);

        if (! is_array($user)) {
            return null;
        }

        $otp = $user['otp_code'] ?? null;

        return is_string($otp) && $otp !== '' ? $otp : null;
    }

    public function issueRememberToken(int $userId, ?string $userAgent = null, ?string $ipAddress = null): array
    {
        $selector = bin2hex(random_bytes(8));
        $validator = bin2hex(random_bytes(32));
        $expiresAt = Time::now()->addDays(self::REMEMBER_DAYS);

        $this->rememberTokens->where('user_id', $userId)->delete();
        $this->rememberTokens->insert([
            'user_id' => $userId,
            'selector' => $selector,
            'token_hash' => password_hash($validator, PASSWORD_DEFAULT),
            'expires_at' => $expiresAt->toDateTimeString(),
            'last_used_at' => Time::now()->toDateTimeString(),
            'user_agent' => $userAgent,
            'ip_address' => $ipAddress,
        ]);

        return [
            'cookie' => $selector . '.' . $validator,
            'expires' => $expiresAt,
        ];
    }

    public function restoreFromRememberToken(string $cookieValue, ?string $userAgent = null, ?string $ipAddress = null): ?array
    {
        if (! str_contains($cookieValue, '.')) {
            return null;
        }

        [$selector, $validator] = explode('.', $cookieValue, 2);

        if ($selector === '' || $validator === '') {
            return null;
        }

        $token = $this->rememberTokens->where('selector', $selector)->first();

        if (! is_array($token)) {
            return null;
        }

        if (Time::parse((string) $token['expires_at'])->isBefore(Time::now())) {
            $this->rememberTokens->delete($token['id']);

            return null;
        }

        if (! password_verify($validator, (string) $token['token_hash'])) {
            $this->rememberTokens->delete($token['id']);

            return null;
        }

        $user = $this->users->findActiveById((int) $token['user_id']);

        if ($user === null) {
            $this->rememberTokens->delete($token['id']);

            return null;
        }

        $this->rememberTokens->update($token['id'], [
            'last_used_at' => Time::now()->toDateTimeString(),
            'user_agent' => $userAgent,
            'ip_address' => $ipAddress,
        ]);

        return $user;
    }

    public function revokeRememberToken(?string $cookieValue): void
    {
        if ($cookieValue === null || ! str_contains($cookieValue, '.')) {
            return;
        }

        [$selector] = explode('.', $cookieValue, 2);

        if ($selector !== '') {
            $this->rememberTokens->where('selector', $selector)->delete();
        }
    }

    private function issueOtp(array $user, string $flow): array
    {
        $otp = (string) random_int(10000, 99999);
        $expiresAt = Time::now()->addMinutes(5)->toDateTimeString();

        $this->users->update((int) $user['id'], [
            'otp_code' => $otp,
            'otp_expires_at' => $expiresAt,
            'otp_attempts' => 0,
        ]);

        $updatedUser = $this->users->find((int) $user['id']) ?? $user;

        return [
            'user' => $updatedUser,
            'otp' => $otp,
            'expires_at' => $expiresAt,
            'flow' => $flow,
        ];
    }

    private function createInstitutionForRegistration(string $name, string $whatsapp): int
    {
        $institutionId = $this->institutions->insert([
            'name' => 'Lembaga ' . $name,
            'app_name' => 'Arus',
            'type' => 'Lembaga',
            'whatsapp' => $whatsapp,
            'email' => null,
            'address' => null,
            'logo' => null,
        ], true);

        return (int) $institutionId;
    }
}
