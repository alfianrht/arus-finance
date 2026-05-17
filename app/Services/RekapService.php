<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;
use Config\Database;

class RekapService
{
    private BaseConnection $db;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? Database::connect();
    }

    public function buildSummary(array $filters): array
    {
        $builder = $this->baseTransactionBuilder($filters);
        $builder->select(
            "SUM(CASE WHEN type = 'masuk' THEN amount ELSE 0 END) as total_masuk,
             SUM(CASE WHEN type IN ('keluar','honor') THEN amount + admin_fee ELSE 0 END) as total_keluar,
             SUM(CASE WHEN type = 'pindah' THEN amount ELSE 0 END) as total_pindah",
            false
        );

        $row = $builder->get()->getRowArray() ?? [];
        $masuk = (float) ($row['total_masuk'] ?? 0);
        $keluar = (float) ($row['total_keluar'] ?? 0);

        return [
            'masuk' => $masuk,
            'keluar' => $keluar,
            'pindah' => (float) ($row['total_pindah'] ?? 0),
            'surplus' => $masuk - $keluar,
        ];
    }

    public function buildAccountSummaries(array $filters): array
    {
        $builder = $this->db->table('accounts a');
        $builder->select(
            "a.id, a.name, a.slug, a.kind, a.mark,
             COALESCE(SUM(CASE
                WHEN t.to_account_id = a.id AND t.type IN ('masuk','pindah') THEN t.amount
                WHEN t.from_account_id = a.id AND t.type IN ('keluar','pindah','honor') THEN -1 * (t.amount + t.admin_fee)
                WHEN t.to_account_id = a.id AND t.type = 'masuk' THEN -1 * t.admin_fee
                ELSE 0
             END), 0) as balance",
            false
        );
        $builder->join('transactions t', 't.deleted_at IS NULL AND (t.from_account_id = a.id OR t.to_account_id = a.id)', 'left');
        $builder->where('a.deleted_at', null);

        if (! empty($filters['institution_id'])) {
            $builder->where('a.institution_id', $filters['institution_id']);
        }

        if (! empty($filters['date_from'])) {
            $builder->where('t.transaction_date >=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $builder->where('t.transaction_date <=', $filters['date_to']);
        }

        $builder->groupBy('a.id');
        $builder->orderBy('a.sort_order', 'ASC');
        $builder->orderBy('a.name', 'ASC');

        return $builder->get()->getResultArray();
    }

    private function baseTransactionBuilder(array $filters)
    {
        $builder = $this->db->table('transactions');
        $builder->where('deleted_at', null);

        if (! empty($filters['institution_id'])) {
            $builder->where('institution_id', $filters['institution_id']);
        }

        if (! empty($filters['unit_id'])) {
            $builder->where('unit_id', $filters['unit_id']);
        }

        if (! empty($filters['activity_id'])) {
            $builder->where('activity_id', $filters['activity_id']);
        }

        if (! empty($filters['date_from'])) {
            $builder->where('transaction_date >=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $builder->where('transaction_date <=', $filters['date_to']);
        }

        return $builder;
    }
}
