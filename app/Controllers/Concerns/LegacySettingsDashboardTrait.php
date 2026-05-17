<?php

namespace App\Controllers\Concerns;

use App\Models\InstitutionModel;
use CodeIgniter\HTTP\RedirectResponse;

trait LegacySettingsDashboardTrait
{
    private function buildSettingsShortcuts(array $data): array
    {
        return [
            [
                'group' => 'Operasional Harian',
                'title' => 'Profil Lembaga',
                'description' => 'Identitas utama aplikasi dan lembaga yang memakai Arus.',
                'meta' => $data['institutionName'],
                'href' => site_url('pengaturan/profil-lembaga'),
                'icon' => 'badge',
            ],
            [
                'group' => 'Operasional Harian',
                'title' => 'Unit / Program',
                'description' => 'Struktur usaha atau layanan yang menaungi kegiatan.',
                'meta' => count($data['units']) . ' unit',
                'href' => site_url('pengaturan/unit-program'),
                'icon' => 'domain',
            ],
            [
                'group' => 'Operasional Harian',
                'title' => 'Kegiatan',
                'description' => 'Turunan dari unit yang dipakai sebagai konteks aktif pencatatan.',
                'meta' => count($data['activitySummaries']) . ' kegiatan',
                'href' => site_url('pengaturan/kegiatan'),
                'icon' => 'workspaces',
            ],
            [
                'group' => 'Operasional Harian',
                'title' => 'Rekening / Dompet',
                'description' => 'Sumber dan tujuan uang bergerak saat transaksi dicatat.',
                'meta' => count($data['accounts']) . ' rekening / dompet',
                'href' => site_url('pengaturan/rekening-dompet'),
                'icon' => 'account_balance_wallet',
            ],
            [
                'group' => 'Operasional Harian',
                'title' => 'Kategori Transaksi',
                'description' => 'Satu master kategori untuk uang masuk dan uang keluar, langsung terhubung ke pos laporan.',
                'meta' => count($data['transactionCategories']) . ' kategori',
                'href' => site_url('pengaturan/kategori-biaya'),
                'icon' => 'inventory_2',
            ],
            [
                'group' => 'Operasional Harian',
                'title' => 'Penerima / Kontak',
                'description' => 'Master data kontak, tim, vendor, atau klien pihak yang bertransaksi.',
                'meta' => count($data['receivers']) . ' penerima',
                'href' => site_url('pengaturan/penerima'),
                'icon' => 'person',
            ],
            [
                'group' => 'Fondasi Laporan Tahunan',
                'title' => 'Pos Laporan',
                'description' => 'Struktur pendapatan, beban, aset, hutang, dan modal untuk laporan tahunan.',
                'meta' => count($data['reportPositions']) . ' pos',
                'href' => site_url('pengaturan/pos-laporan'),
                'icon' => 'account_tree',
            ],
            [
                'group' => 'Fondasi Laporan Tahunan',
                'title' => 'Tahun Buku',
                'description' => 'Periode resmi yang nanti dipakai untuk saldo awal dan laporan tahunan.',
                'meta' => count($data['bookPeriods']) . ' periode',
                'href' => site_url('pengaturan/tahun-buku'),
                'icon' => 'calendar_month',
            ],
            [
                'group' => 'Fondasi Laporan Tahunan',
                'title' => 'Saldo Awal',
                'description' => 'Titik awal neraca agar laporan tahunan bisa dieksekusi tanpa bongkar data ulang.',
                'meta' => count($data['openingBalances']) . ' saldo',
                'href' => site_url('pengaturan/saldo-awal'),
                'icon' => 'stacked_line_chart',
            ],
        ];
    }

    public function pengaturan(): string
    {
        $institution = $this->currentInstitution();
        $units = $this->loadUnitProgramRows();
        $activitySummaries = $this->loadActivityRows();
        $accounts = $this->loadAccountRows();
        $transactionCategories = $this->loadTransactionCategoryRows();
        $reportPositions = $this->loadReportPositionRows();
        $bookPeriods = $this->loadBookPeriodRows();
        $openingBalances = $this->loadOpeningBalanceRows();
        $receivers = $this->loadReceiverRows();

        $data = [
            'appName'               => $institution['app_name'] ?? 'Arusdana',
            'pageTitle'             => 'Pengaturan',
            'activeNav'             => 'beranda',
            'backUrl'               => site_url('beranda'),
            'institutionName'       => $institution['name'],
            'institutionLogo'       => $institution['logo'] ?? '',
            'units'                 => $units,
            'activitySummaries'     => $activitySummaries,
            'accounts'              => $accounts,
            'transactionCategories' => $transactionCategories,
            'reportPositions'       => $reportPositions,
            'bookPeriods'           => $bookPeriods,
            'openingBalances'       => $openingBalances,
            'receivers'             => $receivers,
        ];

        $data['settingsShortcuts'] = $this->buildSettingsShortcuts([
            'institutionName'       => $institution['name'],
            'units'                 => $units,
            'activitySummaries'     => $activitySummaries,
            'accounts'              => $accounts,
            'transactionCategories' => $transactionCategories,
            'reportPositions'       => $reportPositions,
            'bookPeriods'           => $bookPeriods,
            'openingBalances'       => $openingBalances,
            'receivers'             => $receivers,
        ]);

        return view('pages/settings', $data);
    }

    public function profilLembaga(): string
    {
        $data = [];
        $institution = $this->currentInstitution();

        $data['pageTitle'] = 'Profil Lembaga';
        $data['activeNav'] = 'beranda';
        $data['backUrl'] = site_url('pengaturan');
        $data['editUrl'] = site_url('pengaturan/profil-lembaga/edit');
        $data['institutionName'] = $institution['name'];
        $data['institutionLogo'] = $institution['logo'] ?? '';
        $data['profileSections'] = [
            ['label' => 'Nama Lembaga', 'value' => $institution['name']],
            ['label' => 'Nama Aplikasi', 'value' => $institution['app_name']],
            ['label' => 'Jenis Lembaga', 'value' => $institution['type']],
            ['label' => 'Email Operasional', 'value' => $institution['email'] ?: '-'],
            ['label' => 'Nomor WhatsApp', 'value' => $institution['whatsapp'] ?: '-'],
            ['label' => 'Alamat Singkat', 'value' => $institution['address'] ?: '-'],
        ];

        return view('pages/master/profile', $data);
    }

    public function editProfilLembaga(): string
    {
        return view('pages/master/form', $this->buildInstitutionFormData());
    }

    public function simpanProfilLembaga(): RedirectResponse
    {
        $institution = $this->currentInstitution();
        $name = trim((string) $this->request->getPost('name'));
        $appName = trim((string) $this->request->getPost('app_name'));

        if ($name === '' || $appName === '') {
            return redirect()->back()->withInput()->with('error', 'Nama lembaga dan nama aplikasi wajib diisi.');
        }

        $logoAsset = $institution['logo'] ?? '';
        $logoFile = $this->request->getFile('logo_file');
        if ($logoFile !== null && $logoFile->isValid() && ! $logoFile->hasMoved()) {
            $uploadDir = FCPATH . 'uploads/institutions';
            if (! is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $newName = $logoFile->getRandomName();
            $logoFile->move($uploadDir, $newName);
            $logoAsset = 'uploads/institutions/' . $newName;

            $oldLogo = $institution['logo'] ?? '';
            if ($oldLogo !== '' && is_file(FCPATH . $oldLogo)) {
                @unlink(FCPATH . $oldLogo);
            }
        }

        (new InstitutionModel())->update((int) $institution['id'], [
            'name' => $name,
            'app_name' => $appName,
            'type' => trim((string) $this->request->getPost('type')) ?: 'Lembaga',
            'address' => trim((string) $this->request->getPost('address')),
            'email' => trim((string) $this->request->getPost('email')),
            'whatsapp' => trim((string) $this->request->getPost('whatsapp')),
            'logo' => $logoAsset,
        ]);

        return redirect()->to(site_url('pengaturan/profil-lembaga/edit'))
            ->with('success', 'Profil lembaga berhasil diperbarui.');
    }

    private function buildInstitutionFormData(): array
    {
        $institution = $this->currentInstitution();

        return [
            'pageTitle' => 'Form Profil Lembaga',
            'activeNav' => 'beranda',
            'backUrl' => site_url('pengaturan/profil-lembaga'),
            'formMode' => 'Edit Profil',
            'formTitle' => 'Form Profil Lembaga',
            'formDescription' => 'Profil lembaga sekarang tersimpan ke database dan menjadi identitas utama aplikasi.',
            'saveLabel' => 'Simpan Profil',
            'formAction' => site_url('pengaturan/profil-lembaga'),
            'formMethod' => 'post',
            'formFields' => [
                ['type' => 'text', 'name' => 'name', 'label' => 'Nama Lembaga', 'value' => old('name', $institution['name'])],
                ['type' => 'text', 'name' => 'app_name', 'label' => 'Nama Aplikasi', 'value' => old('app_name', $institution['app_name'])],
                ['type' => 'select', 'name' => 'type', 'label' => 'Jenis Lembaga', 'value' => old('type', $institution['type']), 'options' => ['PT', 'Yayasan', 'Lembaga', 'Komunitas']],
                ['type' => 'textarea', 'name' => 'address', 'label' => 'Alamat Singkat', 'value' => old('address', $institution['address'] ?? '')],
                ['type' => 'text', 'name' => 'email', 'label' => 'Email Operasional', 'value' => old('email', $institution['email'] ?? '')],
                ['type' => 'text', 'name' => 'whatsapp', 'label' => 'Nomor WhatsApp', 'value' => old('whatsapp', $institution['whatsapp'] ?? '')],
                ['type' => 'file', 'name' => 'logo_file', 'label' => 'Logo Lembaga', 'value' => $institution['logo'] ?? ''],
            ],
        ];
    }
}
