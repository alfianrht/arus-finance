<?php

namespace App\Models;

use CodeIgniter\Model;

class UnitPublicShareModel extends Model
{
    protected $table = 'unit_public_shares';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'unit_id',
        'is_enabled',
        'pin_hash',
        'pin_last_rotated_at',
        'created_by',
        'updated_by',
    ];
}
