<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class RateLimitFilter implements FilterInterface
{
    private const MAX_ATTEMPTS = 5;
    private const WINDOW_SECONDS = 120;

    public function before(RequestInterface $request, $arguments = null)
    {
        if (ENVIRONMENT === 'development') {
            return null;
        }

        $whatsapp = (string) ($request->getPost('whatsapp') ?? '');
        $pendingAuth = service('session')->get('pending_auth');
        $fallbackWhatsapp = is_array($pendingAuth) ? (string) ($pendingAuth['whatsapp'] ?? '') : '';
        $source = $whatsapp !== '' ? $whatsapp : $fallbackWhatsapp;
        $digits = preg_replace('/\D+/', '', $source) ?? '';
        $digits = $digits !== '' ? $digits : 'guest';
        $cache = cache();
        $key = self::cacheKey($digits);
        $count = (int) ($cache->get($key) ?? 0);

        if ($count >= self::MAX_ATTEMPTS) {
            return redirect()->back()->withInput()->with('error', 'Permintaan OTP terlalu sering. Tunggu sebentar lalu coba lagi.');
        }

        $cache->save($key, $count + 1, self::WINDOW_SECONDS);

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return null;
    }

    public static function clearForWhatsapp(?string $whatsapp): void
    {
        $digits = preg_replace('/\D+/', '', (string) $whatsapp) ?? '';
        if ($digits === '') {
            return;
        }

        cache()->delete(self::cacheKey($digits));
    }

    private static function cacheKey(string $digits): string
    {
        return 'otp_request_' . $digits;
    }
}
