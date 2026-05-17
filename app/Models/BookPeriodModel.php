<?php

namespace App\Models;

use CodeIgniter\Model;

class BookPeriodModel extends Model
{
    protected $table = 'book_periods';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'institution_id',
        'name',
        'slug',
        'start_date',
        'end_date',
        'is_active',
        'is_locked',
    ];
}
