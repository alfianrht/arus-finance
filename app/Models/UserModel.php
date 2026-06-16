<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $useTimestamps = true;
    protected $allowedFields = [
        'institution_id',
        'name',
        'email',
        'whatsapp',
        'google_id',
        'auth_provider',
        'avatar_url',
        'otp_code',
        'otp_expires_at',
        'otp_attempts',
        'role',
        'is_active',
        'last_login_at',
    ];

    public function findActiveById(int $userId): ?array
    {
        $user = $this->where('id', $userId)->where('is_active', 1)->first();

        return is_array($user) ? $user : null;
    }

    public function findActiveByGoogleId(string $googleId): ?array
    {
        $user = $this->where('google_id', trim($googleId))->where('is_active', 1)->first();

        return is_array($user) ? $user : null;
    }

    public function findActiveByEmail(string $email): ?array
    {
        $normalized = strtolower(trim($email));
        $user = $this->where('email', $normalized)->where('is_active', 1)->first();

        return is_array($user) ? $user : null;
    }

    public function findAnyByEmail(string $email): ?array
    {
        $normalized = strtolower(trim($email));
        $user = $this->where('email', $normalized)->first();

        return is_array($user) ? $user : null;
    }
}
