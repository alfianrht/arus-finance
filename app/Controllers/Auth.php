<?php

namespace App\Controllers;

class Auth extends BaseController
{
    public function login(): string
    {
        $data = [
            'pageTitle' => 'Masuk',
            'appName'   => 'Arus',
        ];

        return view('pages/auth/login', $data);
    }

    public function otp(): string
    {
        $data = [
            'pageTitle' => 'Verifikasi OTP',
            'appName'   => 'Arus',
        ];

        return view('pages/auth/otp', $data);
    }

    public function register(): string
    {
        $data = [
            'pageTitle' => 'Daftar',
            'appName'   => 'Arus',
        ];

        return view('pages/auth/register', $data);
    }

    public function forgotPassword(): string
    {
        $data = [
            'pageTitle' => 'Lupa Sandi',
            'appName'   => 'Arus',
        ];

        return view('pages/auth/forgot_password', $data);
    }
}
