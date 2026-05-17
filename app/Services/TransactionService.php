<?php

namespace App\Services;

use App\Models\AccountModel;
use App\Models\ActivityModel;
use App\Models\ReceiverModel;
use App\Models\TransactionCategoryModel;
use App\Models\TransactionModel;
use App\Models\UnitModel;
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

    public function delete(int $id, int $institutionId): void
    {
        $transaction = $this->transactions->where('id', $id)->where('institution_id', $institutionId)->first();
        if (!$transaction) {
            throw new RuntimeException('Transaksi tidak ditemukan.');
        }

        $this->db->transStart();
        $this->transactions->delete($id);
        $this->db->transComplete();

        if (! $this->db->transStatus()) {
            throw new RuntimeException('Gagal menghapus transaksi.');
        }
    }

    public function loadRecentTransactions(int $institutionId, int $unitId = 0, int $activityId = 0, int $limit = 4, int $bookPeriodId = 0): array
    {
        $builder = (new TransactionModel())
            ->where('institution_id', $institutionId)
            ->where('deleted_at', null)
            ->orderBy('transaction_date', 'DESC')
            ->orderBy('transaction_time', 'DESC')
            ->orderBy('id', 'DESC');

        if ($bookPeriodId > 0) {
            $builder->where('book_period_id', $bookPeriodId);
        }

        if ($unitId > 0) {
            $builder->where('unit_id', $unitId);
        }
        if ($activityId > 0) {
            $builder->where('activity_id', $activityId);
        }

        return $this->formatTransactions($builder->findAll($limit));
    }

    public function loadTransactionHistoryPage(
        int $institutionId,
        int $unitId = 0,
        int $activityId = 0,
        int $bookPeriodId = 0,
        string $typeFilter = 'semua',
        int $page = 1,
        int $perPage = 10
    ): array {
        $page = max(1, $page);
        $perPage = max(1, $perPage);

        $builder = (new TransactionModel())
            ->where('institution_id', $institutionId)
            ->where('deleted_at', null);

        if ($bookPeriodId > 0) {
            $builder->where('book_period_id', $bookPeriodId);
        }

        if ($unitId > 0) {
            $builder->where('unit_id', $unitId);
        }

        if ($activityId > 0) {
            $builder->where('activity_id', $activityId);
        }

        $typeFilter = strtolower(trim($typeFilter));
        if (in_array($typeFilter, ['masuk', 'keluar', 'honor', 'pindah'], true)) {
            $builder->where('type', $typeFilter);
        } else {
            $typeFilter = 'semua';
        }

        $total = $builder->countAllResults(false);
        $totalPages = max(1, (int) ceil($total / $perPage));
        $page = min($page, $totalPages);
        $offset = ($page - 1) * $perPage;

        $rows = $builder
            ->orderBy('transaction_date', 'DESC')
            ->orderBy('transaction_time', 'DESC')
            ->orderBy('id', 'DESC')
            ->findAll($perPage, $offset);

        return [
            'items' => $this->formatTransactions($rows),
            'filter' => $typeFilter,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => $totalPages,
            'hasPrev' => $page > 1,
            'hasNext' => $page < $totalPages,
            'prevPage' => max(1, $page - 1),
            'nextPage' => min($totalPages, $page + 1),
        ];
    }

    public function formatTransactions(array $rows): array
    {
        if ($rows === []) {
            return [];
        }

        $units = $this->indexById((new UnitModel())->findAll());
        $activities = $this->indexById((new ActivityModel())->findAll());
        $categories = $this->indexById((new TransactionCategoryModel())->findAll());
        $accounts = $this->indexById((new AccountModel())->findAll());
        $receivers = $this->indexById((new ReceiverModel())->findAll());

        $items = [];
        foreach ($rows as $row) {
            $type = (string) $row['type'];
            $unit = $units[(int) $row['unit_id']] ?? null;
            $activity = $activities[(int) $row['activity_id']] ?? null;
            $category = $categories[(int) ($row['category_id'] ?? 0)] ?? null;
            $fromAccount = $accounts[(int) ($row['from_account_id'] ?? 0)] ?? null;
            $toAccount = $accounts[(int) ($row['to_account_id'] ?? 0)] ?? null;
            $receiver = $receivers[(int) ($row['receiver_id'] ?? 0)] ?? null;

            $badge = match ($type) {
                'masuk' => ['label' => 'Masuk', 'class' => 'bg-emerald-50 text-emerald-700', 'icon' => 'south'],
                'pindah' => ['label' => 'Pindah Dana', 'class' => 'bg-sky-50 text-sky-700', 'icon' => 'sync_alt'],
                'honor' => ['label' => 'Honor', 'class' => 'bg-orange-50 text-orange-700', 'icon' => 'payments'],
                default => ['label' => 'Biaya', 'class' => 'bg-rose-50 text-rose-600', 'icon' => 'north_east'],
            };

            $headline = match ($type) {
                'masuk' => ($category['name'] ?? 'Uang Masuk') . ' ke ' . ($toAccount['name'] ?? '-'),
                'pindah' => ($fromAccount['name'] ?? '-') . ' ke ' . ($toAccount['name'] ?? '-'),
                'honor' => 'Honor untuk ' . ($receiver['name'] ?? 'Penerima'),
                default => ($category['name'] ?? 'Biaya') . ' dari ' . ($fromAccount['name'] ?? '-'),
            };

            $subline = ($unit['name'] ?? 'Tanpa Unit') . ' / ' . ($activity['name'] ?? 'Tanpa Kegiatan');
            $metaParts = [date('d M Y', strtotime((string) $row['transaction_date']))];
            if (! empty($row['notes'])) {
                $metaParts[] = $row['notes'];
            }

            $items[] = [
                'id' => (string) $row['id'],
                'type' => $type,
                'type_key' => $type,
                'badge_label' => $badge['label'],
                'badge_class' => $badge['class'],
                'icon' => $badge['icon'],
                'headline' => $headline,
                'subline' => $subline,
                'meta' => implode(' · ', array_filter($metaParts)),
                'amount' => (float) $row['amount'],
                'amount_prefix' => in_array($type, ['keluar', 'honor', 'pindah'], true) ? '-' : '+',
                'amount_class' => in_array($type, ['keluar', 'honor', 'pindah'], true) ? 'text-rose-600' : 'text-emerald-600',
                'unit_id' => (int) ($unit['id'] ?? 0),
                'unit_name' => $unit['name'] ?? '',
                'activity_id' => (int) ($activity['id'] ?? 0),
                'activity_name' => $activity['name'] ?? '',
                'category_id' => (int) ($category['id'] ?? 0),
                'category' => $category['name'] ?? '',
                'from_account_id' => (int) ($fromAccount['id'] ?? 0),
                'from_account' => $fromAccount['name'] ?? '',
                'to_account_id' => (int) ($toAccount['id'] ?? 0),
                'to_account' => $toAccount['name'] ?? '',
                'receiver_id' => (int) ($receiver['id'] ?? 0),
                'receiver_name' => $receiver['name'] ?? '',
                'transaction_date' => (string) $row['transaction_date'],
                'transaction_time' => (string) ($row['transaction_time'] ?? ''),
                'notes' => (string) ($row['notes'] ?? ''),
                'admin_fee' => (float) ($row['admin_fee'] ?? 0),
                'proof_image' => $row['proof_image'] ?? null,
            ];
        }

        return $items;
    }

    private function indexById(array $rows): array
    {
        $mapped = [];
        foreach ($rows as $row) {
            $mapped[(int) $row['id']] = $row;
        }

        return $mapped;
    }
}
