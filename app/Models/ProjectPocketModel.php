<?php

namespace App\Models;

use CodeIgniter\Model;

class ProjectPocketModel extends Model
{
    protected $table = 'project_pockets';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $useTimestamps = true;
    protected $allowedFields = [
        'institution_id',
        'unit_id',
        'activity_id',
        'name',
        'slug',
        'pocket_type',
        'is_active',
        'notes',
        'contract_value',
        'contract_terms_count',
    ];
}
