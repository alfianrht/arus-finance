<?php

namespace App\Models;

use CodeIgniter\Model;

class TransactionModel extends Model
{
    protected $table = 'transactions';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $useTimestamps = true;
    protected $allowedFields = [
        'institution_id',
        'book_period_id',
        'type',
        'amount',
        'admin_fee',
        'unit_id',
        'activity_id',
        'category_id',
        'from_account_id',
        'to_account_id',
        'receiver_id',
        'transaction_date',
        'transaction_time',
        'notes',
        'proof_image',
        'created_by',
    ];
}
