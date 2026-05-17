<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ArusOperationalSeeder extends Seeder
{
    public function run(): void
    {
        $institution = $this->db->table('institutions')->get()->getFirstRow('array');

        if (! is_array($institution)) {
            return;
        }

        $institutionId = (int) $institution['id'];
        $reportPositions = $this->buildReportPositionMap($institutionId);

        $this->seedUnitsAndActivities($institutionId);
        $this->seedAccounts($institutionId, $reportPositions);
        $this->seedReceivers($institutionId);
        $this->seedDefaultAdmin($institutionId);
        $this->seedOpeningBalances($reportPositions);
        $this->seedTransactions($institutionId);
    }

    private function buildReportPositionMap(int $institutionId): array
    {
        $rows = $this->db->table('report_positions')
            ->where('institution_id', $institutionId)
            ->get()
            ->getResultArray();

        $map = [];

        foreach ($rows as $row) {
            $map[$row['name']] = (int) $row['id'];
        }

        return $map;
    }

    private function seedUnitsAndActivities(int $institutionId): void
    {
        $units = [
            [
                'name' => 'SIMPAUD',
                'slug' => 'simpaud',
                'short_name' => 'SMPD',
                'sort_order' => 10,
                'activities' => [
                    ['name' => 'Jualan Aplikasi Semesteran', 'slug' => 'jualan-aplikasi-semesteran', 'short_name' => 'JAS', 'sort_order' => 10],
                    ['name' => 'Pelatihan SIMPAUD', 'slug' => 'pelatihan-simpaud', 'short_name' => 'PS', 'sort_order' => 20],
                    ['name' => 'Operasional SIMPAUD', 'slug' => 'operasional-simpaud', 'short_name' => 'OPS', 'sort_order' => 30],
                ],
            ],
            [
                'name' => 'Konsultan Pendidikan',
                'slug' => 'konsultan-pendidikan',
                'short_name' => 'KP',
                'sort_order' => 20,
                'activities' => [
                    ['name' => 'Perizinan LKP Tax Session', 'slug' => 'perizinan-lkp-tax-session', 'short_name' => 'LKP', 'sort_order' => 10],
                    ['name' => 'Perizinan LSP', 'slug' => 'perizinan-lsp', 'short_name' => 'LSP', 'sort_order' => 20],
                    ['name' => 'Pendampingan Sekolah', 'slug' => 'pendampingan-sekolah', 'short_name' => 'PSK', 'sort_order' => 30],
                ],
            ],
            [
                'name' => 'KebagusanCode',
                'slug' => 'kebagusancode',
                'short_name' => 'KBC',
                'sort_order' => 30,
                'activities' => [
                    ['name' => 'Project Website Client', 'slug' => 'project-website-client', 'short_name' => 'PWC', 'sort_order' => 10],
                    ['name' => 'Project Aplikasi Client', 'slug' => 'project-aplikasi-client', 'short_name' => 'PAC', 'sort_order' => 20],
                    ['name' => 'Maintenance Sistem', 'slug' => 'maintenance-sistem', 'short_name' => 'MNT', 'sort_order' => 30],
                ],
            ],
        ];

        foreach ($units as $unit) {
            $existingUnit = $this->db->table('units')
                ->where('institution_id', $institutionId)
                ->where('slug', $unit['slug'])
                ->get()
                ->getFirstRow('array');

            if (is_array($existingUnit)) {
                $unitId = (int) $existingUnit['id'];
            } else {
                $this->db->table('units')->insert([
                    'institution_id' => $institutionId,
                    'name' => $unit['name'],
                    'slug' => $unit['slug'],
                    'short_name' => $unit['short_name'],
                    'is_active' => 1,
                    'sort_order' => $unit['sort_order'],
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
                $unitId = (int) $this->db->insertID();
            }

            foreach ($unit['activities'] as $activity) {
                $existingActivity = $this->db->table('activities')
                    ->where('unit_id', $unitId)
                    ->where('slug', $activity['slug'])
                    ->get()
                    ->getFirstRow('array');

                if (is_array($existingActivity)) {
                    continue;
                }

                $this->db->table('activities')->insert([
                    'unit_id' => $unitId,
                    'name' => $activity['name'],
                    'slug' => $activity['slug'],
                    'short_name' => $activity['short_name'],
                    'is_active' => 1,
                    'sort_order' => $activity['sort_order'],
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
            }
        }
    }

    private function seedAccounts(int $institutionId, array $reportPositions): void
    {
        $accounts = [
            ['name' => 'BRI PT', 'slug' => 'bri-pt', 'kind' => 'Rekening', 'mark' => 'BRI', 'report_position' => 'Kas di Bank BRI', 'sort_order' => 10, 'note' => 'Rekening utama lembaga.'],
            ['name' => 'BCA PT', 'slug' => 'bca-pt', 'kind' => 'Rekening', 'mark' => 'BCA', 'report_position' => 'Kas di Bank BCA', 'sort_order' => 20, 'note' => 'Rekening project client dan pembayaran digital.'],
            ['name' => 'Dana Operasional Cago', 'slug' => 'dana-operasional-cago', 'kind' => 'Dompet Digital', 'mark' => 'DANA', 'report_position' => 'Kas Operasional', 'sort_order' => 30, 'note' => 'Dompet operasional cepat untuk lapangan.'],
            ['name' => 'Kas Tunai', 'slug' => 'kas-tunai', 'kind' => 'Kas Tunai', 'mark' => 'KAS', 'report_position' => 'Kas Tunai', 'sort_order' => 40, 'note' => 'Kas kecil tunai.'],
        ];

        foreach ($accounts as $account) {
            $existing = $this->db->table('accounts')
                ->where('institution_id', $institutionId)
                ->where('slug', $account['slug'])
                ->get()
                ->getFirstRow('array');

            if (is_array($existing)) {
                continue;
            }

            $this->db->table('accounts')->insert([
                'institution_id' => $institutionId,
                'name' => $account['name'],
                'slug' => $account['slug'],
                'kind' => $account['kind'],
                'mark' => $account['mark'],
                'logo_asset' => null,
                'note' => $account['note'],
                'report_position_id' => $reportPositions[$account['report_position']] ?? null,
                'is_active' => 1,
                'sort_order' => $account['sort_order'],
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }

    private function seedReceivers(int $institutionId): void
    {
        $receivers = [
            ['name' => 'Budi Santoso', 'type' => 'Tim Internal', 'nik' => '3271234567890001', 'npwp' => '12.345.678.9-000.000', 'bank_account' => 'BCA 1234567890', 'notes' => 'Staff lapangan'],
            ['name' => 'CV Maju Jaya', 'type' => 'Vendor', 'nik' => null, 'npwp' => '98.765.432.1-111.000', 'bank_account' => 'Mandiri 0987654321', 'notes' => 'Vendor IT dan maintenance'],
            ['name' => 'Toko Laris', 'type' => 'Vendor', 'nik' => null, 'npwp' => null, 'bank_account' => 'BRI 111122223333', 'notes' => 'Vendor ATK rutin'],
            ['name' => 'PT Harapan Bangsa', 'type' => 'Klien', 'nik' => null, 'npwp' => '11.222.333.4-555.000', 'bank_account' => null, 'notes' => 'Klien project website'],
        ];

        foreach ($receivers as $receiver) {
            $existing = $this->db->table('receivers')
                ->where('institution_id', $institutionId)
                ->where('name', $receiver['name'])
                ->get()
                ->getFirstRow('array');

            if (is_array($existing)) {
                continue;
            }

            $this->db->table('receivers')->insert([
                'institution_id' => $institutionId,
                'name' => $receiver['name'],
                'type' => $receiver['type'],
                'nik' => $receiver['nik'],
                'npwp' => $receiver['npwp'],
                'bank_account' => $receiver['bank_account'],
                'notes' => $receiver['notes'],
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }

    private function seedDefaultAdmin(int $institutionId): void
    {
        $existing = $this->db->table('users')
            ->where('whatsapp', '6281234567890')
            ->get()
            ->getFirstRow('array');

        if (is_array($existing)) {
            return;
        }

        $this->db->table('users')->insert([
            'institution_id' => $institutionId,
            'name' => 'Admin Arus',
            'whatsapp' => '6281234567890',
            'otp_code' => null,
            'otp_expires_at' => null,
            'otp_attempts' => 0,
            'role' => 'admin',
            'is_active' => 1,
            'last_login_at' => null,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    private function seedOpeningBalances(array $reportPositions): void
    {
        $balances = [
            ['report_position' => 'Kas di Bank BRI', 'label' => 'Saldo awal BRI PT', 'amount' => 15000000],
            ['report_position' => 'Kas di Bank BCA', 'label' => 'Saldo awal BCA PT', 'amount' => 5000000],
            ['report_position' => 'Kas Operasional', 'label' => 'Saldo awal Dana Operasional Cago', 'amount' => 3000000],
            ['report_position' => 'Kas Tunai', 'label' => 'Saldo awal Kas Tunai', 'amount' => 1000000],
        ];

        $period = $this->db->table('book_periods')->where('is_active', 1)->get()->getFirstRow('array');
        if (! is_array($period)) {
            return;
        }

        foreach ($balances as $balance) {
            $reportPositionId = $reportPositions[$balance['report_position']] ?? null;
            if ($reportPositionId === null) {
                continue;
            }

            $exists = $this->db->table('opening_balances')
                ->where('book_period_id', (int) $period['id'])
                ->where('report_position_id', (int) $reportPositionId)
                ->where('source_label', $balance['label'])
                ->get()
                ->getFirstRow('array');

            if (is_array($exists)) {
                continue;
            }

            $this->db->table('opening_balances')->insert([
                'book_period_id' => (int) $period['id'],
                'report_position_id' => (int) $reportPositionId,
                'source_label' => $balance['label'],
                'amount' => $balance['amount'],
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }

    private function seedTransactions(int $institutionId): void
    {
        $period = $this->db->table('book_periods')->where('institution_id', $institutionId)->where('is_active', 1)->get()->getFirstRow('array');
        $user = $this->db->table('users')->where('institution_id', $institutionId)->where('whatsapp', '6281234567890')->get()->getFirstRow('array');

        if (! is_array($period) || ! is_array($user)) {
            return;
        }

        $units = $this->keyBySlug('units', $institutionId);
        $activities = $this->keyActivitiesBySlug($units);
        $accounts = $this->keyBySlug('accounts', $institutionId);
        $categories = $this->keyCategoriesByName($institutionId);

        $transactions = [
            [
                'type' => 'masuk',
                'amount' => 58000000,
                'admin_fee' => 0,
                'unit_slug' => 'konsultan-pendidikan',
                'activity_slug' => 'perizinan-lkp-tax-session',
                'category' => 'Jasa Konsultasi',
                'to_account' => 'bri-pt',
                'date' => date('Y-m-d', strtotime('-5 days')),
                'notes' => 'Pembayaran klien LKP Tax Session',
            ],
            [
                'type' => 'pindah',
                'amount' => 10000000,
                'admin_fee' => 2500,
                'unit_slug' => 'konsultan-pendidikan',
                'activity_slug' => 'perizinan-lkp-tax-session',
                'from_account' => 'bri-pt',
                'to_account' => 'dana-operasional-cago',
                'date' => date('Y-m-d', strtotime('-4 days')),
                'notes' => 'Pindah dana operasional lapangan',
            ],
            [
                'type' => 'keluar',
                'amount' => 250000,
                'admin_fee' => 0,
                'unit_slug' => 'konsultan-pendidikan',
                'activity_slug' => 'perizinan-lkp-tax-session',
                'category' => 'Transport',
                'from_account' => 'dana-operasional-cago',
                'date' => date('Y-m-d', strtotime('-3 days')),
                'notes' => 'Transport survei lapangan',
            ],
            [
                'type' => 'keluar',
                'amount' => 500000,
                'admin_fee' => 0,
                'unit_slug' => 'konsultan-pendidikan',
                'activity_slug' => 'perizinan-lkp-tax-session',
                'category' => 'Konsumsi',
                'from_account' => 'dana-operasional-cago',
                'date' => date('Y-m-d', strtotime('-2 days')),
                'notes' => 'Konsumsi meeting tim',
            ],
            [
                'type' => 'masuk',
                'amount' => 12000000,
                'admin_fee' => 0,
                'unit_slug' => 'kebagusancode',
                'activity_slug' => 'project-website-client',
                'category' => 'Project Client',
                'to_account' => 'bca-pt',
                'date' => date('Y-m-d', strtotime('-1 days')),
                'notes' => 'Termin pertama project website client',
            ],
        ];

        foreach ($transactions as $transaction) {
            $activityId = $activities[$transaction['activity_slug']] ?? null;
            $unitId = $units[$transaction['unit_slug']] ?? null;

            if ($activityId === null || $unitId === null) {
                continue;
            }

            $exists = $this->db->table('transactions')
                ->where('institution_id', $institutionId)
                ->where('type', $transaction['type'])
                ->where('unit_id', $unitId)
                ->where('activity_id', $activityId)
                ->where('amount', $transaction['amount'])
                ->where('transaction_date', $transaction['date'])
                ->where('notes', $transaction['notes'])
                ->get()
                ->getFirstRow('array');

            if (is_array($exists)) {
                continue;
            }

            $this->db->table('transactions')->insert([
                'institution_id' => $institutionId,
                'book_period_id' => (int) $period['id'],
                'type' => $transaction['type'],
                'amount' => $transaction['amount'],
                'admin_fee' => $transaction['admin_fee'],
                'unit_id' => $unitId,
                'activity_id' => $activityId,
                'category_id' => isset($transaction['category']) ? ($categories[$transaction['category']] ?? null) : null,
                'from_account_id' => isset($transaction['from_account']) ? ($accounts[$transaction['from_account']] ?? null) : null,
                'to_account_id' => isset($transaction['to_account']) ? ($accounts[$transaction['to_account']] ?? null) : null,
                'receiver_id' => null,
                'transaction_date' => $transaction['date'],
                'transaction_time' => '09:00:00',
                'notes' => $transaction['notes'],
                'proof_image' => null,
                'created_by' => (int) $user['id'],
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }

    private function keyBySlug(string $table, int $institutionId): array
    {
        $rows = $this->db->table($table)->where('institution_id', $institutionId)->get()->getResultArray();
        $map = [];
        foreach ($rows as $row) {
            $map[$row['slug']] = (int) $row['id'];
        }

        return $map;
    }

    private function keyActivitiesBySlug(array $unitMap): array
    {
        if ($unitMap === []) {
            return [];
        }

        $rows = $this->db->table('activities')->whereIn('unit_id', array_values($unitMap))->get()->getResultArray();
        $map = [];
        foreach ($rows as $row) {
            $map[$row['slug']] = (int) $row['id'];
        }

        return $map;
    }

    private function keyCategoriesByName(int $institutionId): array
    {
        $rows = $this->db->table('transaction_categories')->where('institution_id', $institutionId)->get()->getResultArray();
        $map = [];
        foreach ($rows as $row) {
            $map[$row['name']] = (int) $row['id'];
        }

        return $map;
    }
}
