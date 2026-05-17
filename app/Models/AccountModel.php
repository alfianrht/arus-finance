<?php

namespace App\Models;

use CodeIgniter\Model;

class AccountModel extends Model
{
    protected $table = 'accounts';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $useTimestamps = true;
    protected $allowedFields = [
        'institution_id',
        'name',
        'slug',
        'kind',
        'mark',
        'account_number',
        'logo_asset',
        'note',
        'report_position_id',
        'is_active',
        'sort_order',
    ];
}
