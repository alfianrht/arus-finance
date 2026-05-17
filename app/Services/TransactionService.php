<?php

namespace App\Services;

use App\Models\TransactionModel;
use CodeIgniter\Database\BaseConnection;
use Config\Database;
use RuntimeException;

class TransactionService
{
    private BaseConnection $db;

    private TransactionModel $transactions;

    public function __construct(?BaseConnection $db = null, ?TransactionModel $transactions = null)
    {
        $this->db = $db ?? Database::connect();
        $this->transactions = $transactions ?? new TransactionModel();
    }

    public function getAccountBalance(int $accountId, ?string $untilDate = null): float
    {
        $account = $this->db->table('accounts')
            ->select('report_position_id')
            ->where('id', $accountId)
            ->get()
            ->getRowArray();

        $openingBalance = 0.0;
        $reportPositionId = (int) ($account['report_position_id'] ?? 0);

        if ($reportPositionId > 0) {
            $openingRow = $this->db->table('opening_balances')
                ->selectSum('amount', 'balance')
                ->where('report_position_id', $reportPositionId)
                ->get()
                ->getRowArray();

            $openingBalance = (float) ($openingRow['balance'] ?? 0);
        }

        $builder = $this->db->table('transactions');
        $builder->select(
            "SUM(CASE
                WHEN to_account_id = {$accountId} AND type IN ('masuk','pindah') THEN amount
                WHEN from_account_id = {$accountId} AND type IN ('keluar','pindah','honor') THEN -1 * (amount + admin_fee)
                WHEN from_account_id = {$accountId} AND type = 'masuk' THEN -1 * admin_fee
                WHEN to_account_id = {$accountId} AND type = 'masuk' THEN -1 * admin_fee
                ELSE 0
            END) as balance",
            false
        );
        $builder->where('deleted_at', null);
        $builder->groupStart()
            ->where('from_account_id', $accountId)
            ->orWhere('to_account_id', $accountId)
            ->groupEnd();

        if ($untilDate !== null) {
            $builder->where('transaction_date <=', $untilDate);
        }

        $row = $builder->get()->getRowArray();

        return $openingBalance + (float) ($row['balance'] ?? 0);
    }

    public function assertSufficientBalance(int $accountId, float $amount, float $adminFee = 0): void
    {
        $balance = $this->getAccountBalance($accountId);

        if ($balance < ($amount + $adminFee)) {
            throw new RuntimeException('Saldo rekening tidak mencukupi untuk transaksi ini.');
        }
    }

    public function create(array $payload): int
    {
        $type = (string) ($payload['type'] ?? '');
        $amount = (float) ($payload['amount'] ?? 0);
        $adminFee = (float) ($payload['admin_fee'] ?? 0);

        if (in_array($type, ['keluar', 'pindah', 'honor'], true) && ! empty($payload['from_account_id'])) {
            $this->assertSufficientBalance((int) $payload['from_account_id'], $amount, $adminFee);
        }

        $this->db->transStart();
        $transactionId = $this->transactions->insert($payload, true);
        $this->db->transComplete();

        if (! $this->db->transStatus() || $transactionId === false) {
            throw new RuntimeException('Transaksi gagal disimpan.');
        }

        return (int) $transactionId;
    }
}
