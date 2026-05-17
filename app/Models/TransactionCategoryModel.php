<?php

namespace App\Models;

use CodeIgniter\Model;

class TransactionCategoryModel extends Model
{
    protected $table = 'transaction_categories';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $useTimestamps = true;
    protected $allowedFields = [
        'institution_id',
        'name',
        'kind',
        'report_position_id',
        'is_quick',
        'chip_label',
        'is_active',
        'sort_order',
    ];
}
