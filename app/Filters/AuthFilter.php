<?php

namespace App\Filters;

use App\Services\AuthService;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = service('session');

        if ($session->get('auth_user_id') !== null) {
            return null;
        }

        $cookieValue = $request->getCookie(AuthService::REMEMBER_COOKIE);

        if (is_string($cookieValue) && $cookieValue !== '') {
            $authService = new AuthService();
            $user = $authService->restoreFromRememberToken(
                $cookieValue,
                $request->getUserAgent()?->getAgentString(),
                $request->getIPAddress()
            );

            if (is_array($user)) {
                $session->set([
                    'auth_user_id' => $user['id'],
                    'auth_user_name' => $user['name'],
                    'auth_institution_id' => $user['institution_id'],
                    'auth_role' => $user['role'],
                ]);

                return null;
            }

            service('response')->deleteCookie(AuthService::REMEMBER_COOKIE);
        }

        return redirect()->to(site_url('auth/login'))->with('warning', 'Silakan masuk dulu untuk membuka Arus.');
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return null;
    }
}
