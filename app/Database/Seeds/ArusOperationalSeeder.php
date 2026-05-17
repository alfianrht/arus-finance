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
}
