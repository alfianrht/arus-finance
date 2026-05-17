<?php

namespace App\Models;

use CodeIgniter\Model;

class RememberTokenModel extends Model
{
    protected $table = 'remember_tokens';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'user_id',
        'selector',
        'token_hash',
        'expires_at',
        'last_used_at',
        'user_agent',
        'ip_address',
    ];
}
