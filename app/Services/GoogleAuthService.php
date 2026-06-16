<?php

namespace App\Services;

use App\Models\InstitutionModel;
use App\Models\UserModel;
use CodeIgniter\I18n\Time;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\Google;
use RuntimeException;
use Throwable;

class GoogleAuthService
{
    private UserModel $users;

    private InstitutionModel $institutions;

    public function __construct(?UserModel $users = null, ?InstitutionModel $institutions = null)
    {
        $this->users = $users ?? new UserModel();
        $this->institutions = $institutions ?? new InstitutionModel();
    }

    public function isConfigured(): bool
    {
        return $this->clientId() !== ''
            && $this->clientSecret() !== ''
            && $this->redirectUri() !== '';
    }

    /**
     * @return array{url: string, state: string}
     */
    public function buildAuthorizationRequest(): array
    {
        $provider = $this->provider();
        $url = $provider->getAuthorizationUrl([
            'scope' => ['openid', 'email', 'profile'],
        ]);

        return [
            'url' => $url,
            'state' => (string) $provider->getState(),
        ];
    }

    /**
     * @return array{google_id: string, email: string, email_verified: bool, name: string, avatar_url: ?string}
     */
    public function fetchProfileFromCallback(string $code): array
    {
        try {
            $provider = $this->provider();
            $accessToken = $provider->getAccessToken('authorization_code', ['code' => $code]);
            $owner = $provider->getResourceOwner($accessToken);
            $raw = $owner->toArray();
        } catch (IdentityProviderException $exception) {
            throw new RuntimeException('Google Sign-In gagal diverifikasi.');
        } catch (Throwable $exception) {
            throw new RuntimeException('Google Sign-In belum bisa diproses saat ini.');
        }

        $googleId = trim((string) ($raw['sub'] ?? $owner->getId() ?? ''));
        $email = strtolower(trim((string) ($raw['email'] ?? $owner->getEmail() ?? '')));
        $name = trim((string) ($raw['name'] ?? $owner->getName() ?? ''));
        $avatar = trim((string) ($raw['picture'] ?? $owner->getAvatar() ?? ''));
        $emailVerified = filter_var($raw['email_verified'] ?? false, FILTER_VALIDATE_BOOLEAN);

        if ($googleId === '' || $email === '' || $name === '') {
            throw new RuntimeException('Data akun Google tidak lengkap.');
        }

        if (! $emailVerified) {
            throw new RuntimeException('Email Google Anda belum terverifikasi.');
        }

        return [
            'google_id' => $googleId,
            'email' => $email,
            'email_verified' => $emailVerified,
            'name' => $name,
            'avatar_url' => $avatar !== '' ? $avatar : null,
        ];
    }

    public function findOrCreateUser(array $profile): array
    {
        $googleId = (string) ($profile['google_id'] ?? '');
        $email = strtolower(trim((string) ($profile['email'] ?? '')));
        $name = trim((string) ($profile['name'] ?? ''));
        $avatarUrl = $profile['avatar_url'] ?? null;

        if ($googleId === '' || $email === '' || $name === '') {
            throw new RuntimeException('Profil Google belum valid.');
        }

        $user = $this->users->findActiveByGoogleId($googleId);
        if (is_array($user)) {
            $this->users->update((int) $user['id'], [
                'email' => $email,
                'avatar_url' => $avatarUrl,
                'auth_provider' => 'google',
                'last_login_at' => Time::now()->toDateTimeString(),
            ]);

            return $this->users->findActiveById((int) $user['id']) ?? $user;
        }

        $userByEmail = $this->users->findActiveByEmail($email);
        if (is_array($userByEmail)) {
            $this->users->update((int) $userByEmail['id'], [
                'google_id' => $googleId,
                'email' => $email,
                'avatar_url' => $avatarUrl,
                'auth_provider' => 'google',
                'last_login_at' => Time::now()->toDateTimeString(),
            ]);

            return $this->users->findActiveById((int) $userByEmail['id']) ?? $userByEmail;
        }

        $institutionId = $this->createInstitutionForGoogleSignup($name, $email);
        $userId = $this->users->insert([
            'institution_id' => $institutionId,
            'name' => $name,
            'email' => $email,
            'whatsapp' => null,
            'google_id' => $googleId,
            'auth_provider' => 'google',
            'avatar_url' => $avatarUrl,
            'role' => 'admin',
            'is_active' => 1,
            'otp_attempts' => 0,
            'last_login_at' => Time::now()->toDateTimeString(),
        ], true);

        $user = $this->users->findActiveById((int) $userId);
        if (! is_array($user)) {
            throw new RuntimeException('Akun Google gagal dibuat.');
        }

        return $user;
    }

    public function linkUser(int $userId, array $profile): array
    {
        $user = $this->users->findActiveById($userId);
        if (! is_array($user)) {
            throw new RuntimeException('Akun aktif tidak ditemukan.');
        }

        $googleId = (string) ($profile['google_id'] ?? '');
        $email = strtolower(trim((string) ($profile['email'] ?? '')));
        $avatarUrl = $profile['avatar_url'] ?? null;

        if ($googleId === '' || $email === '') {
            throw new RuntimeException('Profil Google belum valid untuk ditautkan.');
        }

        $linkedByGoogleId = $this->users->findActiveByGoogleId($googleId);
        if (is_array($linkedByGoogleId) && (int) $linkedByGoogleId['id'] !== $userId) {
            throw new RuntimeException('Akun Google ini sudah tertaut ke akun Arus lain.');
        }

        $linkedByEmail = $this->users->findAnyByEmail($email);
        if (is_array($linkedByEmail) && (int) $linkedByEmail['id'] !== $userId) {
            throw new RuntimeException('Email Google ini sudah dipakai akun Arus lain.');
        }

        $this->users->update($userId, [
            'email' => $email,
            'google_id' => $googleId,
            'auth_provider' => 'google',
            'avatar_url' => $avatarUrl,
            'last_login_at' => Time::now()->toDateTimeString(),
        ]);

        return $this->users->findActiveById($userId) ?? $user;
    }

    private function provider(): Google
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('Google Sign-In belum dikonfigurasi.');
        }

        return new Google([
            'clientId' => $this->clientId(),
            'clientSecret' => $this->clientSecret(),
            'redirectUri' => $this->redirectUri(),
        ]);
    }

    private function createInstitutionForGoogleSignup(string $name, string $email): int
    {
        return (int) $this->institutions->insert([
            'name' => 'Lembaga ' . $name,
            'app_name' => 'Arus',
            'type' => 'Lembaga',
            'email' => $email,
            'whatsapp' => null,
            'address' => null,
            'logo' => null,
        ], true);
    }

    private function clientId(): string
    {
        return trim((string) env('GOOGLE_CLIENT_ID', ''));
    }

    private function clientSecret(): string
    {
        return trim((string) env('GOOGLE_CLIENT_SECRET', ''));
    }

    private function redirectUri(): string
    {
        return trim((string) env('GOOGLE_REDIRECT_URI', ''));
    }
}
