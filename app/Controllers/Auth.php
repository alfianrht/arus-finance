<?php

namespace App\Controllers;

use App\Filters\RateLimitFilter;
use App\Services\AuthService;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\I18n\Time;
use RuntimeException;
use Throwable;

class Auth extends BaseController
{
    private AuthService $authService;

    public function __construct()
    {
        $this->authService = new AuthService();
    }

    public function login(): string|RedirectResponse
    {
        if ($this->session->get('auth_user_id') !== null) {
            return redirect()->to(site_url('beranda'));
        }

        $data = [
            'pageTitle' => 'Masuk',
            'appName'   => 'Arus',
        ];

        return view('pages/auth/login', $data);
    }

    public function otp(): string|RedirectResponse
    {
        $pendingAuth = $this->session->get('pending_auth');

        if (! is_array($pendingAuth)) {
            return redirect()->to(site_url('auth/login'))->with('warning', 'Minta kode OTP dulu sebelum verifikasi.');
        }

        $data = [
            'pageTitle' => 'Verifikasi OTP',
            'appName'   => 'Arus',
            'pendingAuth' => $pendingAuth,
            'otpPreview' => $this->authService->currentOtpPreview((int) $pendingAuth['user_id'])
                ?? $this->session->getFlashdata('otp_preview')
                ?? $this->session->get('otp_preview'),
        ];

        return view('pages/auth/otp', $data);
    }

    public function register(): string|RedirectResponse
    {
        if ($this->session->get('auth_user_id') !== null) {
            return redirect()->to(site_url('beranda'));
        }

        $data = [
            'pageTitle' => 'Daftar',
            'appName'   => 'Arus',
        ];

        return view('pages/auth/register', $data);
    }

    public function forgotPassword(): string|RedirectResponse
    {
        $data = [
            'pageTitle' => 'Lupa Sandi',
            'appName'   => 'Arus',
        ];

        return view('pages/auth/forgot_password', $data);
    }

    public function requestOtp(): \CodeIgniter\HTTP\RedirectResponse
    {
        $flow = (string) $this->request->getPost('flow');
        $whatsapp = (string) $this->request->getPost('whatsapp');
        $name = trim((string) $this->request->getPost('name'));
        $rememberDevice = $this->request->getPost('remember_device') !== null;

        try {
            $normalized = $this->authService->normalizeWhatsapp($whatsapp);

            if (! $this->authService->isValidWhatsapp($normalized)) {
                throw new RuntimeException('Nomor WhatsApp belum valid. Gunakan format Indonesia yang aktif.');
            }

            if ($flow === 'register') {
                if ($name === '') {
                    throw new RuntimeException('Nama lengkap wajib diisi untuk pendaftaran.');
                }

                $result = $this->authService->registerAndRequestOtp($name, $normalized);
            } elseif ($flow === 'recovery') {
                $result = $this->authService->requestRecoveryOtp($normalized);
            } else {
                $result = $this->authService->requestLoginOtp($normalized);
            }

            $this->session->set('pending_auth', [
                'user_id' => $result['user']['id'],
                'name' => $result['user']['name'],
                'whatsapp' => $result['user']['whatsapp'],
                'flow' => $result['flow'],
                'expires_at' => $result['expires_at'],
                'remember_device' => $rememberDevice,
            ]);
            $this->session->setFlashdata('success', 'Kode OTP sudah dibuat. Untuk tahap development, kode ditampilkan di halaman berikutnya.');
            $this->session->setFlashdata('otp_preview', $result['otp']);
            $this->session->set('otp_preview', $result['otp']);

            return redirect()->to(site_url('auth/otp'));
        } catch (RuntimeException $exception) {
            return redirect()->back()->withInput()->with('error', $exception->getMessage());
        } catch (Throwable $exception) {
            return redirect()->back()->withInput()->with('error', 'Fondasi database auth belum siap. Jalankan migrasi dan seeder lebih dulu.');
        }
    }

    public function verifyOtp(): \CodeIgniter\HTTP\RedirectResponse
    {
        $pendingAuth = $this->session->get('pending_auth');
        $otp = trim((string) $this->request->getPost('otp_code'));

        if (! is_array($pendingAuth) || ! isset($pendingAuth['user_id'])) {
            return redirect()->to(site_url('auth/login'))->with('warning', 'Sesi OTP tidak ditemukan. Silakan minta kode lagi.');
        }

        try {
            if ($otp === '') {
                throw new RuntimeException('Kode OTP wajib diisi.');
            }

            $user = $this->authService->verifyOtp((int) $pendingAuth['user_id'], $otp);

            $this->session->remove(['pending_auth', 'otp_preview']);
            $this->session->set([
                'auth_user_id' => $user['id'],
                'auth_user_name' => $user['name'],
                'auth_institution_id' => $user['institution_id'],
                'auth_role' => $user['role'],
            ]);
            RateLimitFilter::clearForWhatsapp((string) ($pendingAuth['whatsapp'] ?? ''));

            $redirect = redirect()->to(site_url('beranda'))->with('success', 'Login berhasil.');

            if ((bool) ($pendingAuth['remember_device'] ?? false)) {
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
            return redirect()->back()->withInput()->with('error', $exception->getMessage());
        } catch (Throwable $exception) {
            return redirect()->back()->withInput()->with('error', 'Verifikasi gagal karena fondasi auth belum siap.');
        }
    }

    public function resendOtp(): \CodeIgniter\HTTP\RedirectResponse
    {
        $pendingAuth = $this->session->get('pending_auth');

        if (! is_array($pendingAuth) || ! isset($pendingAuth['flow'], $pendingAuth['whatsapp'], $pendingAuth['name'])) {
            return redirect()->to(site_url('auth/login'))->with('warning', 'Sesi OTP tidak ditemukan.');
        }

        try {
            $result = $this->authService->resendOtpForUser((int) $pendingAuth['user_id'], (string) $pendingAuth['flow']);

            $this->session->set('pending_auth', [
                'user_id' => $result['user']['id'],
                'name' => $result['user']['name'],
                'whatsapp' => $result['user']['whatsapp'],
                'flow' => $result['flow'],
                'expires_at' => $result['expires_at'],
            ]);
            $this->session->setFlashdata('success', 'Kode OTP baru sudah dibuat.');
            $this->session->setFlashdata('otp_preview', $result['otp']);
            $this->session->set('otp_preview', $result['otp']);

            return redirect()->to(site_url('auth/otp'));
        } catch (Throwable $exception) {
            return redirect()->back()->with('error', 'Kode OTP gagal dikirim ulang.');
        }
    }

    public function logout(): \CodeIgniter\HTTP\RedirectResponse
    {
        $this->authService->revokeRememberToken($this->request->getCookie(AuthService::REMEMBER_COOKIE));
        $this->session->destroy();

        $redirect = redirect()->to(site_url('auth/login'))->with('success', 'Anda sudah keluar.');
        $redirect->deleteCookie(AuthService::REMEMBER_COOKIE);

        return $redirect;
    }
}
