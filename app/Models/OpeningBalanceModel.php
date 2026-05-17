<?php

namespace App\Models;

use CodeIgniter\Model;

class OpeningBalanceModel extends Model
{
    protected $table = 'opening_balances';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $useTimestamps = true;
    protected $allowedFields = [
        'institution_id',
        'account_id',
        'book_period_id',
        'report_position_id',
        'source_label',
        'amount',
    ];
}
