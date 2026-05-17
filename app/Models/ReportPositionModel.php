<?php

namespace App\Models;

use CodeIgniter\Model;

class ReportPositionModel extends Model
{
    protected $table = 'report_positions';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'institution_id',
        'name',
        'kind',
        'group',
        'sort_order',
    ];
}
