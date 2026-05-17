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
        'whatsapp',
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
}
