<?php

namespace App\Models;

use CodeIgniter\Model;

class InstitutionModel extends Model
{
    protected $table = 'institutions';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'name',
        'app_name',
        'type',
        'email',
        'whatsapp',
        'address',
        'logo',
    ];
}
