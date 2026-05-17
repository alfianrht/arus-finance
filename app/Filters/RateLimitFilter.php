<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class RateLimitFilter implements FilterInterface
{
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
        $key = 'otp_request_' . $digits;
        $count = (int) ($cache->get($key) ?? 0);

        if ($count >= 3) {
            return redirect()->back()->withInput()->with('error', 'Permintaan OTP terlalu sering. Coba lagi beberapa menit lagi.');
        }

        $cache->save($key, $count + 1, 300);

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return null;
    }
}
