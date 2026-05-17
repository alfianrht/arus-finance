<?php

namespace App\Models;

use CodeIgniter\Model;

class ReceiverModel extends Model
{
    protected $table = 'receivers';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $useTimestamps = true;
    protected $allowedFields = [
        'institution_id',
        'name',
        'type',
        'nik',
        'npwp',
        'bank_account',
        'notes',
    ];
}
