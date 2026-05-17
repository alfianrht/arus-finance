<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ArusDefaultSeeder extends Seeder
{
    public function run(): void
    {
        $institutionId = $this->resolveInstitutionId();

        if ($institutionId === null) {
            return;
        }

        $reportPositionIds = $this->seedReportPositions($institutionId);
        $this->seedTransactionCategories($institutionId, $reportPositionIds);
        $this->seedBookPeriod($institutionId);
    }

    private function resolveInstitutionId(): ?int
    {
        $institution = $this->db->table('institutions')->get()->getFirstRow('array');

        if ($institution !== null) {
            return (int) $institution['id'];
        }

        $this->db->table('institutions')->insert([
            'name' => 'PT Maju Pendidikan Bangsa',
            'app_name' => 'Arus',
            'type' => 'PT',
            'email' => 'halo@arus.local',
            'whatsapp' => '6281234567890',
            'address' => 'Alamat default development',
            'logo' => null,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return (int) $this->db->insertID();
    }

    private function seedReportPositions(int $institutionId): array
    {
        $rows = [
            ['name' => 'Pendapatan Jasa', 'kind' => 'Pendapatan', 'group' => 'Laba Rugi', 'sort_order' => 10],
            ['name' => 'Pendapatan Pelatihan', 'kind' => 'Pendapatan', 'group' => 'Laba Rugi', 'sort_order' => 20],
            ['name' => 'Pendapatan Maintenance', 'kind' => 'Pendapatan', 'group' => 'Laba Rugi', 'sort_order' => 30],
            ['name' => 'Beban Transport', 'kind' => 'Beban', 'group' => 'Laba Rugi', 'sort_order' => 110],
            ['name' => 'Beban Konsumsi', 'kind' => 'Beban', 'group' => 'Laba Rugi', 'sort_order' => 120],
            ['name' => 'Beban Cetak Dokumen', 'kind' => 'Beban', 'group' => 'Laba Rugi', 'sort_order' => 130],
            ['name' => 'Beban Honor', 'kind' => 'Beban', 'group' => 'Laba Rugi', 'sort_order' => 140],
            ['name' => 'Beban Pemasaran', 'kind' => 'Beban', 'group' => 'Laba Rugi', 'sort_order' => 150],
            ['name' => 'Beban Komunikasi', 'kind' => 'Beban', 'group' => 'Laba Rugi', 'sort_order' => 160],
            ['name' => 'Beban Sewa Venue', 'kind' => 'Beban', 'group' => 'Laba Rugi', 'sort_order' => 170],
            ['name' => 'Beban ATK', 'kind' => 'Beban', 'group' => 'Laba Rugi', 'sort_order' => 180],
            ['name' => 'Beban Operasional', 'kind' => 'Beban', 'group' => 'Laba Rugi', 'sort_order' => 190],
            ['name' => 'Beban Lainnya', 'kind' => 'Beban', 'group' => 'Laba Rugi', 'sort_order' => 199],
            ['name' => 'Kas di Bank BRI', 'kind' => 'Aset', 'group' => 'Neraca', 'sort_order' => 210],
            ['name' => 'Kas di Bank BCA', 'kind' => 'Aset', 'group' => 'Neraca', 'sort_order' => 220],
            ['name' => 'Kas Operasional', 'kind' => 'Aset', 'group' => 'Neraca', 'sort_order' => 230],
            ['name' => 'Kas Tunai', 'kind' => 'Aset', 'group' => 'Neraca', 'sort_order' => 240],
            ['name' => 'Piutang Usaha', 'kind' => 'Aset', 'group' => 'Neraca', 'sort_order' => 250],
            ['name' => 'Hutang Usaha', 'kind' => 'Kewajiban', 'group' => 'Neraca', 'sort_order' => 310],
            ['name' => 'Pendapatan Diterima Dimuka', 'kind' => 'Kewajiban', 'group' => 'Neraca', 'sort_order' => 320],
            ['name' => 'Modal Awal', 'kind' => 'Modal', 'group' => 'Neraca', 'sort_order' => 410],
        ];

        $positionIds = [];

        foreach ($rows as $row) {
            $existing = $this->db->table('report_positions')
                ->where('institution_id', $institutionId)
                ->where('name', $row['name'])
                ->get()
                ->getFirstRow('array');

            if ($existing !== null) {
                $positionIds[$row['name']] = (int) $existing['id'];
                continue;
            }

            $payload = $row + [
                'institution_id' => $institutionId,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];

            $this->db->table('report_positions')->insert($payload);
            $positionIds[$row['name']] = (int) $this->db->insertID();
        }

        return $positionIds;
    }

    private function seedTransactionCategories(int $institutionId, array $reportPositionIds): void
    {
        $rows = [
            ['name' => 'Jasa Konsultasi', 'kind' => 'Masuk', 'report_position' => 'Pendapatan Jasa', 'is_quick' => 0, 'chip_label' => null, 'sort_order' => 10],
            ['name' => 'Project Client', 'kind' => 'Masuk', 'report_position' => 'Pendapatan Jasa', 'is_quick' => 0, 'chip_label' => null, 'sort_order' => 20],
            ['name' => 'Pelatihan / Workshop', 'kind' => 'Masuk', 'report_position' => 'Pendapatan Pelatihan', 'is_quick' => 0, 'chip_label' => null, 'sort_order' => 30],
            ['name' => 'Maintenance / Retainer', 'kind' => 'Masuk', 'report_position' => 'Pendapatan Maintenance', 'is_quick' => 0, 'chip_label' => null, 'sort_order' => 40],
            ['name' => 'Transport', 'kind' => 'Keluar', 'report_position' => 'Beban Transport', 'is_quick' => 1, 'chip_label' => 'Transport', 'sort_order' => 110],
            ['name' => 'Konsumsi', 'kind' => 'Keluar', 'report_position' => 'Beban Konsumsi', 'is_quick' => 1, 'chip_label' => 'Konsumsi', 'sort_order' => 120],
            ['name' => 'Cetak Dokumen', 'kind' => 'Keluar', 'report_position' => 'Beban Cetak Dokumen', 'is_quick' => 1, 'chip_label' => 'Cetak', 'sort_order' => 130],
            ['name' => 'Honor', 'kind' => 'Keluar', 'report_position' => 'Beban Honor', 'is_quick' => 1, 'chip_label' => 'Honor', 'sort_order' => 140],
            ['name' => 'Iklan', 'kind' => 'Keluar', 'report_position' => 'Beban Pemasaran', 'is_quick' => 0, 'chip_label' => null, 'sort_order' => 150],
            ['name' => 'Internet/Pulsa', 'kind' => 'Keluar', 'report_position' => 'Beban Komunikasi', 'is_quick' => 0, 'chip_label' => null, 'sort_order' => 160],
            ['name' => 'Sewa/Venue', 'kind' => 'Keluar', 'report_position' => 'Beban Sewa Venue', 'is_quick' => 0, 'chip_label' => null, 'sort_order' => 170],
            ['name' => 'ATK', 'kind' => 'Keluar', 'report_position' => 'Beban ATK', 'is_quick' => 0, 'chip_label' => null, 'sort_order' => 180],
            ['name' => 'Operasional', 'kind' => 'Keluar', 'report_position' => 'Beban Operasional', 'is_quick' => 0, 'chip_label' => null, 'sort_order' => 190],
            ['name' => 'Lainnya', 'kind' => 'Keluar', 'report_position' => 'Beban Lainnya', 'is_quick' => 1, 'chip_label' => 'Lainnya', 'sort_order' => 199],
        ];

        foreach ($rows as $row) {
            $existing = $this->db->table('transaction_categories')
                ->where('institution_id', $institutionId)
                ->where('name', $row['name'])
                ->where('kind', $row['kind'])
                ->get()
                ->getFirstRow('array');

            if ($existing !== null) {
                continue;
            }

            $this->db->table('transaction_categories')->insert([
                'institution_id' => $institutionId,
                'name' => $row['name'],
                'kind' => $row['kind'],
                'report_position_id' => $reportPositionIds[$row['report_position']] ?? null,
                'is_quick' => $row['is_quick'],
                'chip_label' => $row['chip_label'],
                'is_active' => 1,
                'sort_order' => $row['sort_order'],
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }

    private function seedBookPeriod(int $institutionId): void
    {
        $currentYear = date('Y');
        $name = 'Tahun Buku ' . $currentYear;
        $slug = 'tahun-buku-' . $currentYear;

        $existing = $this->db->table('book_periods')
            ->where('institution_id', $institutionId)
            ->where('slug', $slug)
            ->get()
            ->getFirstRow('array');

        if ($existing !== null) {
            return;
        }

        $this->db->table('book_periods')->insert([
            'institution_id' => $institutionId,
            'name' => $name,
            'slug' => $slug,
            'start_date' => $currentYear . '-01-01',
            'end_date' => $currentYear . '-12-31',
            'is_active' => 1,
            'is_locked' => 0,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }
}
