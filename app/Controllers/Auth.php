<?php

namespace App\Controllers;

use App\Services\AuthService;
use App\Services\GoogleAuthService;
use CodeIgniter\HTTP\RedirectResponse;
use RuntimeException;
use Throwable;

class Auth extends BaseController
{
    private const GOOGLE_STATE_SESSION_KEY = 'google_auth_state';

    private const GOOGLE_REMEMBER_SESSION_KEY = 'google_auth_remember';

    private const GOOGLE_FLOW_SESSION_KEY = 'google_auth_flow';

    private const GOOGLE_LINK_USER_SESSION_KEY = 'google_auth_link_user_id';

    private AuthService $authService;

    private GoogleAuthService $googleAuthService;

    public function __construct()
    {
        $this->authService = new AuthService();
        $this->googleAuthService = new GoogleAuthService();
    }

    public function login(): string|RedirectResponse
    {
        if ($this->session->get('auth_user_id') !== null) {
            return redirect()->to(site_url('beranda'));
        }

        return view('pages/auth/login', [
            'pageTitle' => 'Masuk',
            'appName' => 'Arus',
            'googleEnabled' => $this->googleAuthService->isConfigured(),
        ]);
    }

    public function google(): RedirectResponse
    {
        if ($this->session->get('auth_user_id') !== null) {
            return redirect()->to(site_url('beranda'));
        }

        if (! $this->googleAuthService->isConfigured()) {
            return redirect()->to(site_url('auth/login'))
                ->with('error', 'Google Sign-In belum siap. Hubungi admin untuk melengkapi konfigurasi.');
        }

        try {
            $authorization = $this->googleAuthService->buildAuthorizationRequest();
            $rememberDevice = $this->request->getGet('remember_device') !== null;

            $this->session->set(self::GOOGLE_STATE_SESSION_KEY, $authorization['state']);
            $this->session->set(self::GOOGLE_REMEMBER_SESSION_KEY, $rememberDevice);

            return redirect()->to($authorization['url']);
        } catch (Throwable $exception) {
            log_message('error', 'Google auth redirect failed: {message}', ['message' => $exception->getMessage()]);

            return redirect()->to(site_url('auth/login'))
                ->with('error', 'Google Sign-In belum bisa dibuka sekarang. Coba lagi sebentar lagi.');
        }
    }

    public function linkGoogle(): RedirectResponse
    {
        $authUserId = (int) ($this->session->get('auth_user_id') ?? 0);
        if ($authUserId <= 0) {
            return redirect()->to(site_url('auth/login'))
                ->with('warning', 'Login dulu untuk menautkan akun Google.');
        }

        if (! $this->googleAuthService->isConfigured()) {
            return redirect()->back()
                ->with('error', 'Google Sign-In belum siap. Hubungi admin untuk melengkapi konfigurasi.');
        }

        try {
            $authorization = $this->googleAuthService->buildAuthorizationRequest();
            $this->session->set(self::GOOGLE_STATE_SESSION_KEY, $authorization['state']);
            $this->session->set(self::GOOGLE_FLOW_SESSION_KEY, 'link');
            $this->session->set(self::GOOGLE_LINK_USER_SESSION_KEY, $authUserId);

            return redirect()->to($authorization['url']);
        } catch (Throwable $exception) {
            log_message('error', 'Google account link redirect failed: {message}', ['message' => $exception->getMessage()]);

            return redirect()->back()
                ->with('error', 'Google Sign-In belum bisa dibuka sekarang. Coba lagi sebentar lagi.');
        }
    }

    public function googleCallback(): RedirectResponse
    {
        if (! $this->googleAuthService->isConfigured()) {
            return redirect()->to(site_url('auth/login'))
                ->with('error', 'Google Sign-In belum siap. Hubungi admin untuk melengkapi konfigurasi.');
        }

        $expectedState = (string) $this->session->get(self::GOOGLE_STATE_SESSION_KEY);
        $receivedState = trim((string) $this->request->getGet('state'));
        $code = trim((string) $this->request->getGet('code'));
        $providerError = trim((string) $this->request->getGet('error'));
        $flow = (string) $this->session->get(self::GOOGLE_FLOW_SESSION_KEY);
        $linkUserId = (int) ($this->session->get(self::GOOGLE_LINK_USER_SESSION_KEY) ?? 0);
        $failureRedirect = $flow === 'link' ? site_url('pengaturan/profil-lembaga') : site_url('auth/login');

        $this->session->remove([
            self::GOOGLE_STATE_SESSION_KEY,
            self::GOOGLE_FLOW_SESSION_KEY,
            self::GOOGLE_LINK_USER_SESSION_KEY,
        ]);

        if ($providerError !== '') {
            return redirect()->to($failureRedirect)
                ->with('error', 'Masuk dengan Google dibatalkan atau ditolak.');
        }

        if ($expectedState === '' || $receivedState === '' || ! hash_equals($expectedState, $receivedState)) {
            return redirect()->to($failureRedirect)
                ->with('error', 'Sesi Google Sign-In tidak valid. Silakan coba lagi.');
        }

        if ($code === '') {
            return redirect()->to($failureRedirect)
                ->with('error', 'Google Sign-In belum mengirim kode login yang valid.');
        }

        try {
            $profile = $this->googleAuthService->fetchProfileFromCallback($code);

            if ($flow === 'link') {
                if ($linkUserId <= 0) {
                    throw new RuntimeException('Sesi tautkan Google tidak valid. Coba lagi dari halaman profil.');
                }

                $user = $this->googleAuthService->linkUser($linkUserId, $profile);
                $this->session->set([
                    'auth_user_id' => $user['id'],
                    'auth_user_name' => $user['name'],
                    'auth_institution_id' => $user['institution_id'],
                    'auth_role' => $user['role'],
                ]);

                return redirect()->to(site_url('pengaturan/profil-lembaga'))
                    ->with('success', 'Akun Google berhasil ditautkan.');
            }

            $user = $this->googleAuthService->findOrCreateUser($profile);

            if (! is_array($user) || ! isset($user['id'], $user['institution_id'], $user['role'])) {
                throw new RuntimeException('Data akun Google tidak lengkap.');
            }

            $rememberDevice = (bool) $this->session->get(self::GOOGLE_REMEMBER_SESSION_KEY);
            $this->session->remove(self::GOOGLE_REMEMBER_SESSION_KEY);
            $this->session->regenerate(true);
            $this->session->set([
                'auth_user_id' => $user['id'],
                'auth_user_name' => $user['name'],
                'auth_institution_id' => $user['institution_id'],
                'auth_role' => $user['role'],
            ]);

            $redirect = redirect()->to(site_url('beranda'))->with('success', 'Login berhasil.');

            if ($rememberDevice) {
                $remember = $this->authService->issueRememberToken(
                    (int) $user['id'],
                    $this->request->getUserAgent()?->getAgentString(),
                    $this->request->getIPAddress()
                );

                $redirect->setCookie(
                    AuthService::REMEMBER_COOKIE,
                    $remember['cookie'],
                    $remember['expires']->getTimestamp(),
                    '',
                    '',
                    $this->request->isSecure(),
                    true,
                    false,
                    'Lax'
                );
            }

            return $redirect;
        } catch (RuntimeException $exception) {
            return redirect()->to($failureRedirect)->with('error', $exception->getMessage());
        } catch (Throwable $exception) {
            log_message('error', 'Google auth callback failed: {message}', ['message' => $exception->getMessage()]);

            return redirect()->to($failureRedirect)
                ->with('error', 'Google Sign-In belum bisa diproses. Silakan coba lagi.');
        }
    }

    public function otp(): RedirectResponse
    {
        return $this->redirectLegacyAuth('Login sekarang memakai Google Sign-In.');
    }

    public function register(): RedirectResponse
    {
        return $this->redirectLegacyAuth('Pendaftaran sekarang memakai Google Sign-In.');
    }

    public function forgotPassword(): RedirectResponse
    {
        return $this->redirectLegacyAuth('Pemulihan akun sekarang dibantu lewat login Google.');
    }

    public function requestOtp(): RedirectResponse
    {
        return $this->redirectLegacyAuth('Kode OTP tidak dipakai lagi. Lanjutkan dengan Google.');
    }

    public function verifyOtp(): RedirectResponse
    {
        return $this->redirectLegacyAuth('Verifikasi OTP tidak dipakai lagi. Lanjutkan dengan Google.');
    }

    public function resendOtp(): RedirectResponse
    {
        return $this->redirectLegacyAuth('Kode OTP tidak dipakai lagi. Lanjutkan dengan Google.');
    }

    public function logout(): RedirectResponse
    {
        $this->authService->revokeRememberToken($this->request->getCookie(AuthService::REMEMBER_COOKIE));
        $this->session->destroy();

        $redirect = redirect()->to(site_url('auth/login'))->with('success', 'Anda sudah keluar.');
        $redirect->deleteCookie(AuthService::REMEMBER_COOKIE);

        return $redirect;
    }

    private function redirectLegacyAuth(string $message): RedirectResponse
    {
        return redirect()->to(site_url('auth/login'))->with('warning', $message);
    }
}
