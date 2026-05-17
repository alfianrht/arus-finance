<?php

namespace App\Controllers;

use CodeIgniter\Exceptions\PageNotFoundException;

class Arus extends BaseController
{
    public function home(): string
    {
        $data                      = $this->prototypeData();
        $data['pageTitle']         = 'Beranda';
        $data['activeNav']         = 'beranda';
        $data['homeTransactions']  = array_slice($data['transactions'], 0, 4);

        return view('pages/home', $data);
    }

    public function catat(): string
    {
        $data                      = $this->prototypeData();
        $data['pageTitle']         = 'Catat';
        $data['activeNav']         = 'catat';
        $data['recentTransactions'] = array_slice($data['contextTransactions'], 0, 4);
        $data['quickCategories']   = array_values(
            array_map(
                static function (array $category): array {
                    return [
                        'label' => $category['chip_label'] ?? $category['name'],
                        'value' => $category['name'],
                    ];
                },
                array_filter(
                    $data['expenseCategories'],
                    static fn(array $category): bool => (bool) $category['is_quick']
                )
            )
        );

        foreach ($data['quickCategories'] as &$chip) {
            $chip['href'] = route_query(
                'catat/keluar/biaya',
                array_merge($data['activeContext']['query'], ['kategori' => $chip['value']])
            );
        }
        unset($chip);

        return view('pages/catat/index', $data);
    }

    public function catatMasuk(): string
    {
        $data              = $this->prototypeData();
        $data['pageTitle'] = 'Uang Masuk';
        $data['activeNav'] = 'catat';
        $data['backUrl']   = route_query('catat', $data['activeContext']['query']);
        $data['selectedIncomeCategory'] = $data['incomeCategories'][0]['name'] ?? '';

        return view('pages/catat/masuk', $data);
    }

    public function catatKeluar(): string
    {
        $data              = $this->prototypeData();
        $data['pageTitle'] = 'Uang Keluar';
        $data['activeNav'] = 'catat';
        $data['backUrl']   = route_query('catat', $data['activeContext']['query']);

        return view('pages/catat/keluar', $data);
    }

    public function catatBiaya(): string
    {
        $data              = $this->prototypeData();
        $data['pageTitle'] = 'Biaya / Belanja';
        $data['activeNav'] = 'catat';
        $data['backUrl']   = route_query('catat/keluar', $data['activeContext']['query']);

        return view('pages/catat/biaya', $data);
    }

    public function catatPindahDana(): string
    {
        $data              = $this->prototypeData();
        $data['pageTitle'] = 'Pindah Dana';
        $data['activeNav'] = 'catat';
        $data['backUrl']   = route_query('catat/keluar', $data['activeContext']['query']);

        return view('pages/catat/pindah_dana', $data);
    }

    public function rekap(): string
    {
        $data              = $this->prototypeData();
        $data['pageTitle'] = 'Rekap';
        $data['activeNav'] = 'rekap';

        return view('pages/rekap', $data);
    }

    public function pengaturan(): string
    {
        $data                      = $this->prototypeData();
        $data['pageTitle']         = 'Pengaturan';
        $data['activeNav']         = 'beranda';
        $data['backUrl']           = site_url('beranda');
        $data['settingsShortcuts'] = $this->buildSettingsShortcuts($data);

        return view('pages/settings', $data);
    }

    public function profilLembaga(): string
    {
        $data                    = $this->prototypeData();
        $data['pageTitle']       = 'Profil Lembaga';
        $data['activeNav']       = 'beranda';
        $data['backUrl']         = site_url('pengaturan');
        $data['editUrl']         = site_url('pengaturan/profil-lembaga/edit');
        $data['profileSections'] = [
            ['label' => 'Nama Lembaga', 'value' => $data['institutionName']],
            ['label' => 'Nama Aplikasi', 'value' => $data['appName']],
            ['label' => 'Jenis Lembaga', 'value' => 'PT / Badan Usaha'],
            ['label' => 'Email Operasional', 'value' => 'finance@majupendidikanbangsa.id'],
            ['label' => 'Nomor WhatsApp', 'value' => '+62 812-0000-8899'],
            ['label' => 'Alamat Singkat', 'value' => 'Bandung, Jawa Barat'],
        ];

        return view('pages/master/profile', $data);
    }

    public function editProfilLembaga(): string
    {
        $data                    = $this->prototypeData();
        $data['pageTitle']       = 'Form Profil Lembaga';
        $data['activeNav']       = 'beranda';
        $data['backUrl']         = site_url('pengaturan/profil-lembaga');
        $data['formMode']        = 'Edit Profil';
        $data['formTitle']       = 'Form Profil Lembaga';
        $data['formDescription'] = 'Form dummy ini dipakai untuk memvalidasi field dasar identitas lembaga sebelum backend dan penyimpanan data dibuat.';
        $data['saveLabel']       = 'Simpan Profil';
        $data['formFields']      = [
            ['type' => 'text', 'label' => 'Nama Lembaga', 'value' => $data['institutionName']],
            ['type' => 'text', 'label' => 'Nama Aplikasi', 'value' => $data['appName']],
            ['type' => 'select', 'label' => 'Jenis Lembaga', 'value' => 'PT / Badan Usaha', 'options' => ['PT / Badan Usaha', 'Yayasan', 'Lembaga Non-Profit']],
            ['type' => 'textarea', 'label' => 'Alamat Singkat', 'value' => 'Bandung, Jawa Barat'],
            ['type' => 'text', 'label' => 'Email Operasional', 'value' => 'finance@majupendidikanbangsa.id'],
            ['type' => 'text', 'label' => 'Nomor WhatsApp', 'value' => '+62 812-0000-8899'],
            ['type' => 'file', 'label' => 'Logo Lembaga', 'value' => 'Belum ada file baru dipilih'],
        ];

        return view('pages/master/form', $data);
    }

    public function masterUnitProgram(): string
    {
        $data               = $this->prototypeData();
        $data['pageTitle']  = 'Master Unit / Program';
        $data['activeNav']  = 'beranda';
        $data['backUrl']    = site_url('pengaturan');

        return view('pages/master/units', $data);
    }

    public function tambahUnitProgram(): string
    {
        $data                    = $this->prototypeData();
        $data['pageTitle']       = 'Tambah Unit / Program';
        $data['activeNav']       = 'beranda';
        $data['backUrl']         = site_url('pengaturan/unit-program');
        $data['formMode']        = 'Tambah Data';
        $data['formTitle']       = 'Form Unit / Program';
        $data['formDescription'] = 'Digunakan untuk menambah layer utama sebelum kegiatan diturunkan di bawahnya.';
        $data['saveLabel']       = 'Simpan Unit';
        $data['formFields']      = [
            ['type' => 'text', 'label' => 'Nama Unit / Program', 'value' => ''],
            ['type' => 'text', 'label' => 'Singkatan Unit', 'value' => ''],
            ['type' => 'select', 'label' => 'Status', 'value' => 'Aktif', 'options' => ['Aktif', 'Nonaktif']],
            ['type' => 'number', 'label' => 'Urutan Tampil', 'value' => '4'],
            ['type' => 'textarea', 'label' => 'Catatan Singkat', 'value' => ''],
        ];

        return view('pages/master/form', $data);
    }

    public function editUnitProgram(string $slug): string
    {
        $data = $this->prototypeData();
        $unit = $this->findUnit($data['units'], $slug);

        if ($unit === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $data['pageTitle']       = 'Edit ' . $unit['name'];
        $data['activeNav']       = 'beranda';
        $data['backUrl']         = site_url('pengaturan/unit-program');
        $data['formMode']        = 'Edit Data';
        $data['formTitle']       = 'Edit Unit / Program';
        $data['formDescription'] = 'Form dummy ini mengatur identitas unit dan bagaimana unit tampil di daftar utama.';
        $data['saveLabel']       = 'Simpan Unit';
        $data['formFields']      = [
            ['type' => 'text', 'label' => 'Nama Unit / Program', 'value' => $unit['name']],
            ['type' => 'text', 'label' => 'Singkatan Unit', 'value' => $unit['short_name'] ?? ''],
            ['type' => 'select', 'label' => 'Status', 'value' => 'Aktif', 'options' => ['Aktif', 'Nonaktif']],
            ['type' => 'number', 'label' => 'Urutan Tampil', 'value' => (string) ($this->findIndexBySlug($data['units'], $unit['slug']) + 1)],
            ['type' => 'textarea', 'label' => 'Catatan Singkat', 'value' => 'Unit ini dipakai untuk mengelompokkan kegiatan dan ringkasan transaksi.'],
        ];

        return view('pages/master/form', $data);
    }

    public function masterKegiatan(): string
    {
        $data               = $this->prototypeData();
        $data['pageTitle']  = 'Master Kegiatan';
        $data['activeNav']  = 'beranda';
        $data['backUrl']    = site_url('pengaturan');

        return view('pages/master/activities', $data);
    }

    public function tambahKegiatan(): string
    {
        $data                    = $this->prototypeData();
        $data['pageTitle']       = 'Tambah Kegiatan';
        $data['activeNav']       = 'beranda';
        $data['backUrl']         = site_url('pengaturan/kegiatan');
        $data['formMode']        = 'Tambah Data';
        $data['formTitle']       = 'Form Kegiatan';
        $data['formDescription'] = 'Kegiatan adalah konteks aktif yang dipakai langsung saat mencatat transaksi.';
        $data['saveLabel']       = 'Simpan Kegiatan';
        $data['formFields']      = [
            ['type' => 'select', 'label' => 'Unit Induk', 'value' => 'Konsultan Pendidikan', 'options' => array_column($data['units'], 'name')],
            ['type' => 'text', 'label' => 'Nama Kegiatan', 'value' => ''],
            ['type' => 'text', 'label' => 'Singkatan Kegiatan', 'value' => ''],
            ['type' => 'text', 'label' => 'Rekening Terkait', 'value' => 'BRI PT, Dana Operasional Cago'],
            ['type' => 'select', 'label' => 'Default Uang Masuk', 'value' => 'BRI PT', 'options' => array_column($data['accounts'], 'name')],
            ['type' => 'select', 'label' => 'Default Biaya / Belanja', 'value' => 'Dana Operasional Cago', 'options' => array_column($data['accounts'], 'name')],
            ['type' => 'text', 'label' => 'Saldo Terkait Dummy', 'value' => 'Rp 0'],
        ];

        return view('pages/master/form', $data);
    }

    public function editKegiatan(string $slug): string
    {
        $data     = $this->prototypeData();
        $activity = $this->findActivity($data['activitySummaries'], $slug);

        if ($activity === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $rawActivity = $this->findActivityFromUnits($data['units'], $slug);
        $defaultIncome = $rawActivity['default_income_account'] ?? ($activity['related_accounts'][0] ?? '');
        $defaultExpense = $rawActivity['default_expense_account'] ?? ($activity['related_accounts'][0] ?? '');

        $data['pageTitle']       = 'Edit ' . $activity['name'];
        $data['activeNav']       = 'beranda';
        $data['backUrl']         = site_url('pengaturan/kegiatan');
        $data['formMode']        = 'Edit Data';
        $data['formTitle']       = 'Edit Kegiatan';
        $data['formDescription'] = 'Form dummy ini mengatur kegiatan, rekening yang terkait, dan default konteks pencatatan.';
        $data['saveLabel']       = 'Simpan Kegiatan';
        $data['formFields']      = [
            ['type' => 'select', 'label' => 'Unit Induk', 'value' => $activity['unit_name'], 'options' => array_column($data['units'], 'name')],
            ['type' => 'text', 'label' => 'Nama Kegiatan', 'value' => $activity['name']],
            ['type' => 'text', 'label' => 'Singkatan Kegiatan', 'value' => $activity['short_name'] ?? ''],
            ['type' => 'text', 'label' => 'Rekening Terkait', 'value' => implode(', ', $activity['related_accounts'])],
            ['type' => 'select', 'label' => 'Default Uang Masuk', 'value' => $defaultIncome, 'options' => array_column($data['accounts'], 'name')],
            ['type' => 'select', 'label' => 'Default Biaya / Belanja', 'value' => $defaultExpense, 'options' => array_column($data['accounts'], 'name')],
            ['type' => 'text', 'label' => 'Saldo Terkait Dummy', 'value' => rupiah($activity['related_balance'])],
        ];

        return view('pages/master/form', $data);
    }

    public function masterRekeningDompet(): string
    {
        $data               = $this->prototypeData();
        $data['pageTitle']  = 'Master Rekening / Dompet';
        $data['activeNav']  = 'beranda';
        $data['backUrl']    = site_url('pengaturan');

        return view('pages/master/accounts', $data);
    }

    public function tambahRekeningDompet(): string
    {
        $data                    = $this->prototypeData();
        $data['pageTitle']       = 'Tambah Rekening / Dompet';
        $data['activeNav']       = 'beranda';
        $data['backUrl']         = site_url('pengaturan/rekening-dompet');
        $data['formMode']        = 'Tambah Data';
        $data['formTitle']       = 'Form Rekening / Dompet';
        $data['formDescription'] = 'Sumber atau tujuan uang bergerak saat transaksi dicatat. Tetap statis untuk tahap prototype.';
        $data['saveLabel']       = 'Simpan Rekening';
        $data['formFields']      = [
            ['type' => 'text', 'label' => 'Nama Rekening / Dompet', 'value' => ''],
            ['type' => 'select', 'label' => 'Jenis Penyimpanan Dana', 'value' => 'Rekening', 'options' => ['Rekening', 'Dompet', 'Kas Tunai']],
            ['type' => 'text', 'label' => 'Label / Singkatan', 'value' => ''],
            ['type' => 'select', 'label' => 'Pos Laporan Terkait', 'value' => 'Kas di Bank BRI', 'options' => array_column($data['neracaPositions'], 'name')],
            ['type' => 'text', 'label' => 'Saldo Dummy', 'value' => 'Rp 0'],
            ['type' => 'textarea', 'label' => 'Catatan Penggunaan', 'value' => ''],
            ['type' => 'select', 'label' => 'Status', 'value' => 'Aktif', 'options' => ['Aktif', 'Nonaktif']],
        ];

        return view('pages/master/form', $data);
    }

    public function editRekeningDompet(string $slug): string
    {
        $data    = $this->prototypeData();
        $account = $this->findAccount($data['accountSummaries'], $slug);

        if ($account === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $data['pageTitle']       = 'Edit ' . $account['name'];
        $data['activeNav']       = 'beranda';
        $data['backUrl']         = site_url('pengaturan/rekening-dompet');
        $data['formMode']        = 'Edit Data';
        $data['formTitle']       = 'Edit Rekening / Dompet';
        $data['formDescription'] = 'Form dummy ini mengatur nama, jenis, tampilan label, dan saldo awal presentasi rekening atau dompet.';
        $data['saveLabel']       = 'Simpan Rekening';
        $data['formFields']      = [
            ['type' => 'text', 'label' => 'Nama Rekening / Dompet', 'value' => $account['name']],
            ['type' => 'select', 'label' => 'Jenis Penyimpanan Dana', 'value' => $account['kind'], 'options' => ['Rekening', 'Dompet', 'Kas Tunai']],
            ['type' => 'text', 'label' => 'Label / Singkatan', 'value' => $account['mark']],
            ['type' => 'select', 'label' => 'Pos Laporan Terkait', 'value' => $account['report_position_name'] ?? 'Kas dan Bank', 'options' => array_column($data['neracaPositions'], 'name')],
            ['type' => 'text', 'label' => 'Saldo Dummy', 'value' => rupiah($account['balance'])],
            ['type' => 'textarea', 'label' => 'Catatan Penggunaan', 'value' => $account['note']],
            ['type' => 'select', 'label' => 'Status', 'value' => 'Aktif', 'options' => ['Aktif', 'Nonaktif']],
        ];

        return view('pages/master/form', $data);
    }

    public function masterKategoriBiaya(): string
    {
        $data               = $this->prototypeData();
        $data['pageTitle']  = 'Master Kategori Transaksi';
        $data['activeNav']  = 'beranda';
        $data['backUrl']    = site_url('pengaturan');

        return view('pages/master/categories', $data);
    }

    public function tambahKategoriBiaya(): string
    {
        $data                    = $this->prototypeData();
        $data['pageTitle']       = 'Tambah Kategori Transaksi';
        $data['activeNav']       = 'beranda';
        $data['backUrl']         = site_url('pengaturan/kategori-biaya');
        $data['formMode']        = 'Tambah Data';
        $data['formTitle']       = 'Form Kategori Transaksi';
        $data['formDescription'] = 'Satu master ini dipakai untuk uang masuk dan uang keluar. Jenis transaksinya menentukan kategori muncul di form yang mana.';
        $data['saveLabel']       = 'Simpan Kategori';
        $data['formFields']      = [
            ['type' => 'text', 'label' => 'Nama Kategori', 'value' => ''],
            ['type' => 'select', 'label' => 'Jenis Transaksi', 'value' => 'Keluar', 'options' => ['Masuk', 'Keluar']],
            ['type' => 'number', 'label' => 'Urutan Tampil', 'value' => (string) (count($data['transactionCategories']) + 1)],
            ['type' => 'select', 'label' => 'Pos Laporan Terkait', 'value' => 'Beban Operasional', 'options' => array_column($data['transactionPositions'], 'name')],
            ['type' => 'select', 'label' => 'Muncul sebagai kategori cepat', 'value' => 'Ya', 'options' => ['Ya', 'Tidak']],
            ['type' => 'select', 'label' => 'Status', 'value' => 'Aktif', 'options' => ['Aktif', 'Nonaktif']],
            ['type' => 'textarea', 'label' => 'Catatan Penggunaan', 'value' => ''],
        ];

        return view('pages/master/form', $data);
    }

    public function editKategoriBiaya(string $slug): string
    {
        $data     = $this->prototypeData();
        $category = $this->findCategoryItem($data['transactionCategories'], $slug);

        if ($category === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $data['pageTitle']       = 'Edit ' . $category['name'];
        $data['activeNav']       = 'beranda';
        $data['backUrl']         = site_url('pengaturan/kategori-biaya');
        $data['formMode']        = 'Edit Data';
        $data['formTitle']       = 'Edit Kategori Transaksi';
        $data['formDescription'] = 'Kategori ini langsung menentukan apakah item muncul di form uang masuk atau uang keluar, sekaligus terhubung ke pos laporan.';
        $data['saveLabel']       = 'Simpan Kategori';
        $data['formFields']      = [
            ['type' => 'text', 'label' => 'Nama Kategori', 'value' => $category['name']],
            ['type' => 'select', 'label' => 'Jenis Transaksi', 'value' => $category['type'], 'options' => ['Masuk', 'Keluar']],
            ['type' => 'number', 'label' => 'Urutan Tampil', 'value' => (string) $category['order']],
            ['type' => 'select', 'label' => 'Pos Laporan Terkait', 'value' => $category['report_position_name'], 'options' => array_column($data['transactionPositions'], 'name')],
            ['type' => 'select', 'label' => 'Muncul sebagai kategori cepat', 'value' => $category['is_quick'] ? 'Ya' : 'Tidak', 'options' => ['Ya', 'Tidak']],
            ['type' => 'select', 'label' => 'Status', 'value' => 'Aktif', 'options' => ['Aktif', 'Nonaktif']],
            ['type' => 'textarea', 'label' => 'Catatan Penggunaan', 'value' => $category['note']],
        ];

        return view('pages/master/form', $data);
    }

    public function masterPosLaporan(): string
    {
        $data              = $this->prototypeData();
        $data['pageTitle'] = 'Pos Laporan';
        $data['activeNav'] = 'beranda';
        $data['backUrl']   = site_url('pengaturan');

        return view('pages/master/report_positions', $data);
    }

    public function tambahPosLaporan(): string
    {
        $data                    = $this->prototypeData();
        $data['pageTitle']       = 'Tambah Pos Laporan';
        $data['activeNav']       = 'beranda';
        $data['backUrl']         = site_url('pengaturan/pos-laporan');
        $data['formMode']        = 'Tambah Data';
        $data['formTitle']       = 'Form Pos Laporan';
        $data['formDescription'] = 'Pos laporan adalah jembatan antara transaksi harian dengan laporan tahunan. Di sinilah beban, pendapatan, aset, hutang, dan modal disiapkan.';
        $data['saveLabel']       = 'Simpan Pos';
        $data['formFields']      = [
            ['type' => 'text', 'label' => 'Kode Pos', 'value' => ''],
            ['type' => 'text', 'label' => 'Nama Pos Laporan', 'value' => ''],
            ['type' => 'select', 'label' => 'Kelompok Laporan', 'value' => 'Laba Rugi', 'options' => array_column($data['reportGroups'], 'name')],
            ['type' => 'select', 'label' => 'Jenis Pos', 'value' => 'Beban', 'options' => ['Pendapatan', 'Beban', 'Aset', 'Kewajiban', 'Modal']],
            ['type' => 'select', 'label' => 'Saldo Normal', 'value' => 'Debit', 'options' => ['Debit', 'Kredit']],
            ['type' => 'textarea', 'label' => 'Catatan Pos', 'value' => ''],
        ];

        return view('pages/master/form', $data);
    }

    public function editPosLaporan(string $slug): string
    {
        $data         = $this->prototypeData();
        $reportPosition = $this->findReportPosition($data['reportPositions'], $slug);

        if ($reportPosition === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $data['pageTitle']       = 'Edit ' . $reportPosition['name'];
        $data['activeNav']       = 'beranda';
        $data['backUrl']         = site_url('pengaturan/pos-laporan');
        $data['formMode']        = 'Edit Data';
        $data['formTitle']       = 'Edit Pos Laporan';
        $data['formDescription'] = 'Form dummy ini menyiapkan struktur yang nanti dipakai laporan laba rugi, neraca, dan arus kas.';
        $data['saveLabel']       = 'Simpan Pos';
        $data['formFields']      = [
            ['type' => 'text', 'label' => 'Kode Pos', 'value' => $reportPosition['code']],
            ['type' => 'text', 'label' => 'Nama Pos Laporan', 'value' => $reportPosition['name']],
            ['type' => 'select', 'label' => 'Kelompok Laporan', 'value' => $reportPosition['group'], 'options' => array_column($data['reportGroups'], 'name')],
            ['type' => 'select', 'label' => 'Jenis Pos', 'value' => $reportPosition['kind'], 'options' => ['Pendapatan', 'Beban', 'Aset', 'Kewajiban', 'Modal']],
            ['type' => 'select', 'label' => 'Saldo Normal', 'value' => $reportPosition['normal_balance'], 'options' => ['Debit', 'Kredit']],
            ['type' => 'textarea', 'label' => 'Catatan Pos', 'value' => $reportPosition['note']],
        ];

        return view('pages/master/form', $data);
    }

    public function masterTahunBuku(): string
    {
        $data              = $this->prototypeData();
        $data['pageTitle'] = 'Tahun Buku';
        $data['activeNav'] = 'beranda';
        $data['backUrl']   = site_url('pengaturan');

        return view('pages/master/book_periods', $data);
    }

    public function tambahTahunBuku(): string
    {
        $data                    = $this->prototypeData();
        $data['pageTitle']       = 'Tambah Tahun Buku';
        $data['activeNav']       = 'beranda';
        $data['backUrl']         = site_url('pengaturan/tahun-buku');
        $data['formMode']        = 'Tambah Data';
        $data['formTitle']       = 'Form Tahun Buku';
        $data['formDescription'] = 'Tahun buku dipakai untuk mengikat saldo awal dan nanti menjadi dasar filter laporan tahunan.';
        $data['saveLabel']       = 'Simpan Tahun Buku';
        $data['formFields']      = [
            ['type' => 'text', 'label' => 'Nama Tahun Buku', 'value' => ''],
            ['type' => 'text', 'label' => 'Tanggal Mulai', 'value' => '01 Jan 2027'],
            ['type' => 'text', 'label' => 'Tanggal Selesai', 'value' => '31 Des 2027'],
            ['type' => 'select', 'label' => 'Status', 'value' => 'Draft', 'options' => ['Draft', 'Aktif', 'Ditutup']],
            ['type' => 'textarea', 'label' => 'Catatan Periode', 'value' => ''],
        ];

        return view('pages/master/form', $data);
    }

    public function editTahunBuku(string $slug): string
    {
        $data = $this->prototypeData();
        $bookPeriod = $this->findBookPeriod($data['bookPeriods'], $slug);

        if ($bookPeriod === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $data['pageTitle']       = 'Edit ' . $bookPeriod['name'];
        $data['activeNav']       = 'beranda';
        $data['backUrl']         = site_url('pengaturan/tahun-buku');
        $data['formMode']        = 'Edit Data';
        $data['formTitle']       = 'Edit Tahun Buku';
        $data['formDescription'] = 'Form dummy ini mengatur periode buku yang nanti mengikat transaksi, saldo awal, dan laporan tahunan.';
        $data['saveLabel']       = 'Simpan Tahun Buku';
        $data['formFields']      = [
            ['type' => 'text', 'label' => 'Nama Tahun Buku', 'value' => $bookPeriod['name']],
            ['type' => 'text', 'label' => 'Tanggal Mulai', 'value' => $bookPeriod['start']],
            ['type' => 'text', 'label' => 'Tanggal Selesai', 'value' => $bookPeriod['end']],
            ['type' => 'select', 'label' => 'Status', 'value' => $bookPeriod['status'], 'options' => ['Draft', 'Aktif', 'Ditutup']],
            ['type' => 'textarea', 'label' => 'Catatan Periode', 'value' => $bookPeriod['note']],
        ];

        return view('pages/master/form', $data);
    }

    public function masterSaldoAwal(): string
    {
        $data              = $this->prototypeData();
        $data['pageTitle'] = 'Saldo Awal';
        $data['activeNav'] = 'beranda';
        $data['backUrl']   = site_url('pengaturan');

        return view('pages/master/opening_balances', $data);
    }

    public function tambahSaldoAwal(): string
    {
        $data                    = $this->prototypeData();
        $data['pageTitle']       = 'Tambah Saldo Awal';
        $data['activeNav']       = 'beranda';
        $data['backUrl']         = site_url('pengaturan/saldo-awal');
        $data['formMode']        = 'Tambah Data';
        $data['formTitle']       = 'Form Saldo Awal';
        $data['formDescription'] = 'Saldo awal dipakai agar laporan tahunan nantinya punya titik awal yang valid, baik untuk kas maupun pos neraca lain.';
        $data['saveLabel']       = 'Simpan Saldo Awal';
        $data['formFields']      = [
            ['type' => 'select', 'label' => 'Tahun Buku', 'value' => $data['bookPeriods'][0]['name'], 'options' => array_column($data['bookPeriods'], 'name')],
            ['type' => 'select', 'label' => 'Pos / Sumber Saldo', 'value' => $data['openingBalanceSources'][0], 'options' => $data['openingBalanceSources']],
            ['type' => 'select', 'label' => 'Pos Laporan Terkait', 'value' => $data['openingBalancePositions'][0], 'options' => $data['openingBalancePositions']],
            ['type' => 'text', 'label' => 'Nilai Saldo Awal', 'value' => 'Rp 0'],
            ['type' => 'textarea', 'label' => 'Catatan', 'value' => ''],
        ];

        return view('pages/master/form', $data);
    }

    public function editSaldoAwal(string $slug): string
    {
        $data           = $this->prototypeData();
        $openingBalance = $this->findOpeningBalance($data['openingBalances'], $slug);

        if ($openingBalance === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $data['pageTitle']       = 'Edit ' . $openingBalance['label'];
        $data['activeNav']       = 'beranda';
        $data['backUrl']         = site_url('pengaturan/saldo-awal');
        $data['formMode']        = 'Edit Data';
        $data['formTitle']       = 'Edit Saldo Awal';
        $data['formDescription'] = 'Form dummy ini mengikat saldo awal dengan tahun buku dan pos laporan yang benar.';
        $data['saveLabel']       = 'Simpan Saldo Awal';
        $data['formFields']      = [
            ['type' => 'select', 'label' => 'Tahun Buku', 'value' => $openingBalance['book_period_name'], 'options' => array_column($data['bookPeriods'], 'name')],
            ['type' => 'select', 'label' => 'Pos / Sumber Saldo', 'value' => $openingBalance['label'], 'options' => $data['openingBalanceSources']],
            ['type' => 'select', 'label' => 'Pos Laporan Terkait', 'value' => $openingBalance['report_position_name'], 'options' => $data['openingBalancePositions']],
            ['type' => 'text', 'label' => 'Nilai Saldo Awal', 'value' => rupiah($openingBalance['amount'])],
            ['type' => 'textarea', 'label' => 'Catatan', 'value' => $openingBalance['note']],
        ];

        return view('pages/master/form', $data);
    }

    public function transaksi(string $id): string
    {
        $data = $this->prototypeData();
        $transaction = $this->findTransaction($data['transactions'], $id);

        if ($transaction === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $from = (string) $this->request->getGet('from');
        $isLocalFrom = $from !== '' && str_starts_with($from, site_url());

        $data['pageTitle'] = 'Detail Transaksi';
        $data['activeNav'] = 'beranda';
        $data['backUrl'] = $isLocalFrom ? $from : site_url('beranda');
        $data['transaction'] = $transaction;
        $data['isEditMode'] = false;
        $data['editUrl'] = site_url('transaksi/' . $transaction['id'] . '/edit') . '?from=' . rawurlencode($data['backUrl']);
        $data['transactionForm'] = $this->buildTransactionFormData($transaction, $data);

        return view('pages/transaction_detail', $data);
    }

    public function editTransaksi(string $id): string
    {
        $data = $this->prototypeData();
        $transaction = $this->findTransaction($data['transactions'], $id);

        if ($transaction === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $from = (string) $this->request->getGet('from');
        $isLocalFrom = $from !== '' && str_starts_with($from, site_url());

        $data['pageTitle'] = 'Edit Transaksi';
        $data['activeNav'] = 'beranda';
        $data['backUrl'] = $isLocalFrom ? $from : site_url('transaksi/' . $transaction['id']);
        $data['transaction'] = $transaction;
        $data['isEditMode'] = true;
        $data['editUrl'] = site_url('transaksi/' . $transaction['id'] . '/edit') . '?from=' . rawurlencode($data['backUrl']);
        $data['transactionForm'] = $this->buildTransactionFormData($transaction, $data);

        return view('pages/transaction_detail', $data);
    }

    public function rekening(string $slug): string
    {
        $data = $this->prototypeData();

        $account = $this->findAccount($data['accountSummaries'], $slug);

        if ($account === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $accountTransactions = $this->filterAccountTransactions($data['rekapTransactions'], $account['name']);

        $data['pageTitle']           = $account['name'];
        $data['activeNav']           = 'rekap';
        $data['backUrl']             = route_query('rekap', $data['rekapQuery']);
        $data['account']             = $account;
        $data['accountTransactions'] = $accountTransactions;
        $data['accountActivities']   = $this->buildAccountActivityBreakdown($accountTransactions, $account['name']);

        return view('pages/account_detail', $data);
    }

    public function unit(string $slug): string
    {
        $data = $this->prototypeData([
            'unit'     => $slug,
            'activity' => $this->request->getGet('kegiatan'),
        ]);

        $unit = $this->findUnit($data['units'], $slug);

        if ($unit === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $data['pageTitle']        = $unit['name'];
        $data['activeNav']        = 'beranda';
        $data['unit']             = $unit;
        $data['unitTransactions'] = array_values(
            array_filter(
                $data['transactions'],
                static fn(array $transaction): bool => $transaction['unit_slug'] === $slug
            )
        );

        return view('pages/unit_detail', $data);
    }

    public function kegiatan(string $slug): string
    {
        $data = $this->prototypeData([
            'activity' => $slug,
        ]);

        $activity = $this->findActivity($data['activitySummaries'], $slug);

        if ($activity === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $activityTransactions = array_values(
            array_filter(
                $data['transactions'],
                static fn(array $transaction): bool => $transaction['activity_slug'] === $slug
            )
        );

        $categoryBreakdown = $this->buildCategorySummary($data['expenseCategories'], $activityTransactions);
        $transferItems     = array_values(
            array_filter(
                $activityTransactions,
                static fn(array $transaction): bool => $transaction['type'] === 'pindah'
            )
        );

        $data['pageTitle']            = $activity['name'];
        $data['activeNav']            = 'beranda';
        $data['activity']             = $activity;
        $data['activityTransactions'] = $activityTransactions;
        $data['categoryBreakdown']    = $categoryBreakdown;
        $data['transferItems']        = $transferItems;

        return view('pages/activity_detail', $data);
    }

    private function prototypeData(array $options = []): array
    {
        $raw = $this->rawPrototypeData();

        [$units, $unitMap, $activityMap] = $this->buildMaps($raw['units']);

        $transactions = $this->decorateTransactions($raw['transactions']);
        $activeContext = $this->resolveContext(
            $units,
            $activityMap,
            $options['unit'] ?? $this->request->getGet('unit'),
            $options['activity'] ?? $this->request->getGet('kegiatan')
        );

        $activeContext['query']             = [
            'unit'     => $activeContext['unit_slug'],
            'kegiatan' => $activeContext['activity_slug'],
        ];
        $switchParams = $this->request->getGet();
        unset($switchParams['unit'], $switchParams['kegiatan']);
        $activeContext['switch_params']     = $switchParams;
        $activeContext['switch_url']        = site_url(trim($this->request->getUri()->getPath(), '/'));
        $activeContext['unit_url']          = route_query('unit/' . $activeContext['unit_slug'], ['kegiatan' => $activeContext['activity_slug']]);
        $activeContext['activity_url']      = site_url('kegiatan/' . $activeContext['activity_slug']);
        $activeContext['catat_url']         = route_query('catat', $activeContext['query']);
        $activeContext['masuk_url']         = route_query('catat/masuk', $activeContext['query']);
        $activeContext['keluar_url']        = route_query('catat/keluar', $activeContext['query']);
        $activeContext['biaya_url']         = route_query('catat/keluar/biaya', $activeContext['query']);
        $activeContext['pindah_dana_url']   = route_query('catat/keluar/pindah-dana', $activeContext['query']);
        $activeContext['default_income_account'] = $activeContext['default_income_account'] ?? 'BRI PT';
        $activeContext['default_expense_account'] = $activeContext['default_expense_account'] ?? 'Dana Operasional Cago';
        $activeContext['default_transfer_from'] = $activeContext['default_transfer_from'] ?? 'BRI PT';
        $activeContext['default_transfer_to'] = $activeContext['default_transfer_to'] ?? 'Dana Operasional Cago';

        $contextTransactions = $this->filterTransactions(
            $transactions,
            'semua',
            $activeContext['unit_slug'],
            $activeContext['activity_slug']
        );

        $reportGroups = $raw['report_groups'];
        $reportPositions = $raw['report_positions'];
        $transactionCategories = $this->buildTransactionCategories($raw['transaction_categories']);
        $incomeCategories = $this->filterCategoriesByType($transactionCategories, 'Masuk');
        $expenseCategories = $this->filterCategoriesByType($transactionCategories, 'Keluar');
        $bookPeriods = $raw['book_periods'];
        $openingBalances = $raw['opening_balances'];

        $summary = $this->buildSummary($transactions, $raw['accounts']);
        $units = $this->buildUnitSummaries($units, $transactions, $activeContext);
        $activitySummaries = $this->buildActivitySummaries($units, $transactions);
        $selectedCategory = $this->resolveCategory($expenseCategories, $this->request->getGet('kategori'));

        $selectedPeriodSlug = $this->resolvePeriod($raw['periods'], $this->request->getGet('periode'));
        [$selectedUnitSlug, $selectedActivitySlug] = $this->resolveRekapFilters(
            $units,
            $activityMap,
            $this->request->getGet('unit'),
            $this->request->getGet('kegiatan')
        );

        $rekapTransactions = $this->filterTransactions(
            $transactions,
            $selectedPeriodSlug,
            $selectedUnitSlug !== 'semua' ? $selectedUnitSlug : null,
            $selectedActivitySlug !== 'semua' ? $selectedActivitySlug : null
        );

        $rekapQuery = [
            'periode'  => $selectedPeriodSlug,
            'unit'     => $selectedUnitSlug,
            'kegiatan' => $selectedActivitySlug,
        ];

        $accountSummaries = $this->buildAccountSummaries(
            $raw['accounts'],
            $rekapTransactions,
            $rekapQuery
        );

        $rekapAccounts = $this->buildDisplayedAccounts(
            $accountSummaries,
            $rekapTransactions,
            $selectedUnitSlug,
            $selectedActivitySlug,
            $unitMap,
            $activityMap
        );

        $rekapSummary = $this->buildSummary($rekapTransactions, $rekapAccounts);
        $rekapUnits = $this->filterUnitSummaries(
            $this->buildUnitSummaries($units, $rekapTransactions, $activeContext),
            $selectedUnitSlug
        );
        $rekapActivities = $this->filterActivitySummaries(
            $this->buildActivitySummaries($units, $rekapTransactions),
            $selectedUnitSlug,
            $selectedActivitySlug
        );

        $filterActivities = $this->buildFilterActivities($units, $selectedUnitSlug);

        return [
            'appName'            => 'Arus',
            'institutionName'    => 'PT Maju Pendidikan Bangsa',
            'activeContext'      => $activeContext,
            'summary'            => $summary,
            'units'              => $units,
            'accounts'           => $raw['accounts'],
            'transactionCategories' => $transactionCategories,
            'incomeCategories'   => $incomeCategories,
            'expenseCategories'  => $expenseCategories,
            'reportGroups'       => $reportGroups,
            'reportPositions'    => $reportPositions,
            'transactionPositions' => $this->filterReportPositionsByKinds($reportPositions, ['Pendapatan', 'Beban']),
            'neracaPositions'    => $this->filterReportPositionsByGroup($reportPositions, 'Neraca'),
            'bookPeriods'        => $bookPeriods,
            'openingBalances'    => $openingBalances,
            'openingBalanceSources' => $this->buildOpeningBalanceSources($raw['accounts'], $reportPositions),
            'openingBalancePositions' => $this->buildOpeningBalancePositions($reportPositions),
            'selectedCategory'   => $selectedCategory,
            'transactions'       => $transactions,
            'contextTransactions'=> $contextTransactions,
            'activitySummaries'  => $activitySummaries,
            'periods'            => $raw['periods'],
            'selectedPeriodSlug' => $selectedPeriodSlug,
            'selectedUnitSlug'   => $selectedUnitSlug,
            'selectedActivitySlug' => $selectedActivitySlug,
            'rekapSummary'       => $rekapSummary,
            'accountSummaries'   => $accountSummaries,
            'rekapAccounts'      => $rekapAccounts,
            'rekapUnits'         => $rekapUnits,
            'rekapActivities'    => $rekapActivities,
            'rekapTransactions'  => $rekapTransactions,
            'rekapTransferItems' => array_values(
                array_filter(
                    $rekapTransactions,
                    static fn(array $transaction): bool => $transaction['type'] === 'pindah'
                )
            ),
            'filterActivities'   => $filterActivities,
            'rekapQuery'         => $rekapQuery,
            'rekapFilterSummary' => $this->buildRekapFilterSummary(
                $raw['periods'],
                $units,
                $activityMap,
                $selectedPeriodSlug,
                $selectedUnitSlug,
                $selectedActivitySlug
            ),
            'settingsShortcuts'  => $this->buildSettingsShortcuts([
                'institutionName'   => 'PT Maju Pendidikan Bangsa',
                'units'             => $units,
                'activitySummaries' => $activitySummaries,
                'accounts'          => $raw['accounts'],
                'transactionCategories' => $transactionCategories,
                'reportPositions'   => $reportPositions,
                'bookPeriods'       => $bookPeriods,
                'openingBalances'   => $openingBalances,
            ]),
        ];
    }

    private function rawPrototypeData(): array
    {
        return [
            'periods' => [
                ['slug' => 'semua', 'label' => 'Semua Periode'],
                ['slug' => 'mei-2026', 'label' => 'Mei 2026'],
                ['slug' => 'april-2026', 'label' => 'April 2026'],
            ],
            'units' => [
                [
                    'slug'       => 'simpaud',
                    'name'       => 'SIMPAUD',
                    'short_name' => 'SMPD',
                    'activities' => [
                        [
                            'slug'                   => 'jualan-aplikasi-semesteran',
                            'name'                   => 'Jualan Aplikasi Semesteran',
                            'short_name'             => 'JAS',
                            'related_accounts'       => ['BRI PT'],
                            'related_balance'        => 0,
                            'default_income_account' => 'BRI PT',
                            'default_expense_account'=> 'Dana Operasional Cago',
                            'default_transfer_from'  => 'BRI PT',
                            'default_transfer_to'    => 'Dana Operasional Cago',
                        ],
                        [
                            'slug'                   => 'pelatihan-simpaud',
                            'name'                   => 'Pelatihan SIMPAUD',
                            'short_name'             => 'PS',
                            'related_accounts'       => ['BRI PT'],
                            'related_balance'        => 0,
                            'default_income_account' => 'BRI PT',
                            'default_expense_account'=> 'Dana Operasional Cago',
                            'default_transfer_from'  => 'BRI PT',
                            'default_transfer_to'    => 'Dana Operasional Cago',
                        ],
                        [
                            'slug'                   => 'operasional-simpaud',
                            'name'                   => 'Operasional SIMPAUD',
                            'short_name'             => 'OPS',
                            'related_accounts'       => ['Kas Tunai'],
                            'related_balance'        => 0,
                            'default_income_account' => 'Kas Tunai',
                            'default_expense_account'=> 'Kas Tunai',
                            'default_transfer_from'  => 'BRI PT',
                            'default_transfer_to'    => 'Kas Tunai',
                        ],
                    ],
                ],
                [
                    'slug'       => 'konsultan-pendidikan',
                    'name'       => 'Konsultan Pendidikan',
                    'short_name' => 'KP',
                    'activities' => [
                        [
                            'slug'                   => 'perizinan-lkp-tax-session',
                            'name'                   => 'Perizinan LKP Tax Session',
                            'short_name'             => 'LKP',
                            'related_accounts'       => ['BRI PT', 'Dana Operasional Cago'],
                            'related_balance'        => 57250000,
                            'default_income_account' => 'BRI PT',
                            'default_expense_account'=> 'Dana Operasional Cago',
                            'default_transfer_from'  => 'BRI PT',
                            'default_transfer_to'    => 'Dana Operasional Cago',
                        ],
                        [
                            'slug'                   => 'perizinan-lsp',
                            'name'                   => 'Perizinan LSP',
                            'short_name'             => 'LSP',
                            'related_accounts'       => ['BRI PT'],
                            'related_balance'        => 0,
                            'default_income_account' => 'BRI PT',
                            'default_expense_account'=> 'Dana Operasional Cago',
                            'default_transfer_from'  => 'BRI PT',
                            'default_transfer_to'    => 'Dana Operasional Cago',
                        ],
                        [
                            'slug'                   => 'pendampingan-sekolah',
                            'name'                   => 'Pendampingan Sekolah',
                            'short_name'             => 'PSK',
                            'related_accounts'       => ['BRI PT'],
                            'related_balance'        => 0,
                            'default_income_account' => 'BRI PT',
                            'default_expense_account'=> 'Dana Operasional Cago',
                            'default_transfer_from'  => 'BRI PT',
                            'default_transfer_to'    => 'Dana Operasional Cago',
                        ],
                    ],
                ],
                [
                    'slug'       => 'kebagusancode',
                    'name'       => 'KebagusanCode',
                    'short_name' => 'KBC',
                    'activities' => [
                        [
                            'slug'                   => 'project-website-client',
                            'name'                   => 'Project Website Client',
                            'short_name'             => 'PWC',
                            'related_accounts'       => ['BCA PT'],
                            'related_balance'        => 12000000,
                            'default_income_account' => 'BCA PT',
                            'default_expense_account'=> 'BCA PT',
                            'default_transfer_from'  => 'BCA PT',
                            'default_transfer_to'    => 'Dana Operasional Cago',
                        ],
                        [
                            'slug'                   => 'project-aplikasi-client',
                            'name'                   => 'Project Aplikasi Client',
                            'short_name'             => 'PAC',
                            'related_accounts'       => ['BCA PT'],
                            'related_balance'        => 0,
                            'default_income_account' => 'BCA PT',
                            'default_expense_account'=> 'BCA PT',
                            'default_transfer_from'  => 'BCA PT',
                            'default_transfer_to'    => 'Dana Operasional Cago',
                        ],
                        [
                            'slug'                   => 'maintenance-sistem',
                            'name'                   => 'Maintenance Sistem',
                            'short_name'             => 'MTS',
                            'related_accounts'       => ['BCA PT'],
                            'related_balance'        => 0,
                            'default_income_account' => 'BCA PT',
                            'default_expense_account'=> 'BCA PT',
                            'default_transfer_from'  => 'BCA PT',
                            'default_transfer_to'    => 'Dana Operasional Cago',
                        ],
                    ],
                ],
            ],
            'accounts' => [
                ['slug' => 'bri-pt', 'name' => 'BRI PT', 'kind' => 'Rekening', 'mark' => 'BRI', 'logo_asset' => 'images/bri-logo.png', 'balance' => 48000000, 'note' => 'Penerimaan utama proyek dan jasa', 'report_position_slug' => 'kas-bank-bri', 'report_position_name' => 'Kas di Bank BRI', 'report_group' => 'Neraca'],
                ['slug' => 'bca-pt', 'name' => 'BCA PT', 'kind' => 'Rekening', 'mark' => 'BCA', 'balance' => 12000000, 'note' => 'Penerimaan project client digital', 'report_position_slug' => 'kas-bank-bca', 'report_position_name' => 'Kas di Bank BCA', 'report_group' => 'Neraca'],
                ['slug' => 'dana-operasional-cago', 'name' => 'Dana Operasional Cago', 'kind' => 'Dompet', 'mark' => 'DANA', 'balance' => 9250000, 'note' => 'Biaya harian kegiatan dan operasional lapangan', 'report_position_slug' => 'kas-operasional', 'report_position_name' => 'Kas Operasional', 'report_group' => 'Neraca'],
                ['slug' => 'kas-tunai', 'name' => 'Kas Tunai', 'kind' => 'Kas Tunai', 'mark' => 'KAS', 'balance' => 0, 'note' => 'Belum ada saldo tercatat bulan ini', 'report_position_slug' => 'kas-tunai', 'report_position_name' => 'Kas Tunai', 'report_group' => 'Neraca'],
            ],
            'report_groups' => [
                ['slug' => 'laba-rugi', 'name' => 'Laba Rugi', 'description' => 'Pendapatan dan beban untuk membaca kinerja usaha.'],
                ['slug' => 'neraca', 'name' => 'Neraca', 'description' => 'Aset, kewajiban, dan modal untuk membaca posisi keuangan.'],
                ['slug' => 'arus-kas', 'name' => 'Arus Kas', 'description' => 'Jejak kas masuk dan kas keluar menurut aktivitasnya.'],
            ],
            'report_positions' => [
                ['slug' => 'pendapatan-jasa', 'code' => '4-100', 'name' => 'Pendapatan Jasa', 'group' => 'Laba Rugi', 'kind' => 'Pendapatan', 'normal_balance' => 'Kredit', 'note' => 'Untuk jasa konsultasi, project client, dan layanan inti.'],
                ['slug' => 'pendapatan-pelatihan', 'code' => '4-110', 'name' => 'Pendapatan Pelatihan', 'group' => 'Laba Rugi', 'kind' => 'Pendapatan', 'normal_balance' => 'Kredit', 'note' => 'Untuk pelatihan, workshop, dan program pembelajaran.'],
                ['slug' => 'pendapatan-maintenance', 'code' => '4-120', 'name' => 'Pendapatan Maintenance', 'group' => 'Laba Rugi', 'kind' => 'Pendapatan', 'normal_balance' => 'Kredit', 'note' => 'Untuk retainer, maintenance sistem, dan perpanjangan layanan.'],
                ['slug' => 'beban-transport', 'code' => '5-100', 'name' => 'Beban Transport', 'group' => 'Laba Rugi', 'kind' => 'Beban', 'normal_balance' => 'Debit', 'note' => 'Biaya perjalanan, pengiriman, dan transportasi operasional.'],
                ['slug' => 'beban-konsumsi', 'code' => '5-110', 'name' => 'Beban Konsumsi', 'group' => 'Laba Rugi', 'kind' => 'Beban', 'normal_balance' => 'Debit', 'note' => 'Biaya makan, minum, dan konsumsi kegiatan.'],
                ['slug' => 'beban-cetak-dokumen', 'code' => '5-120', 'name' => 'Beban Cetak Dokumen', 'group' => 'Laba Rugi', 'kind' => 'Beban', 'normal_balance' => 'Debit', 'note' => 'Untuk print, fotokopi, legalisasi, dan dokumen fisik.'],
                ['slug' => 'beban-honor', 'code' => '5-130', 'name' => 'Beban Honor', 'group' => 'Laba Rugi', 'kind' => 'Beban', 'normal_balance' => 'Debit', 'note' => 'Untuk fee narasumber, tenaga bantu, dan honorarium.'],
                ['slug' => 'beban-pemasaran', 'code' => '5-140', 'name' => 'Beban Pemasaran', 'group' => 'Laba Rugi', 'kind' => 'Beban', 'normal_balance' => 'Debit', 'note' => 'Untuk iklan, promosi, dan biaya akuisisi.' ],
                ['slug' => 'beban-komunikasi', 'code' => '5-150', 'name' => 'Beban Komunikasi', 'group' => 'Laba Rugi', 'kind' => 'Beban', 'normal_balance' => 'Debit', 'note' => 'Untuk internet, pulsa, dan biaya komunikasi.'],
                ['slug' => 'beban-sewa-venue', 'code' => '5-160', 'name' => 'Beban Sewa Venue', 'group' => 'Laba Rugi', 'kind' => 'Beban', 'normal_balance' => 'Debit', 'note' => 'Untuk sewa ruang, tempat, dan sarana event.'],
                ['slug' => 'beban-atk', 'code' => '5-170', 'name' => 'Beban ATK', 'group' => 'Laba Rugi', 'kind' => 'Beban', 'normal_balance' => 'Debit', 'note' => 'Untuk alat tulis kantor dan kebutuhan administrasi ringan.'],
                ['slug' => 'beban-operasional', 'code' => '5-180', 'name' => 'Beban Operasional', 'group' => 'Laba Rugi', 'kind' => 'Beban', 'normal_balance' => 'Debit', 'note' => 'Untuk biaya operasional umum yang tidak masuk kategori lain.'],
                ['slug' => 'beban-lainnya', 'code' => '5-199', 'name' => 'Beban Lainnya', 'group' => 'Laba Rugi', 'kind' => 'Beban', 'normal_balance' => 'Debit', 'note' => 'Penampung sementara untuk biaya yang belum dipisah lebih rinci.'],
                ['slug' => 'kas-bank-bri', 'code' => '1-110', 'name' => 'Kas di Bank BRI', 'group' => 'Neraca', 'kind' => 'Aset', 'normal_balance' => 'Debit', 'note' => 'Pos neraca untuk rekening BRI utama lembaga.'],
                ['slug' => 'kas-bank-bca', 'code' => '1-120', 'name' => 'Kas di Bank BCA', 'group' => 'Neraca', 'kind' => 'Aset', 'normal_balance' => 'Debit', 'note' => 'Pos neraca untuk rekening BCA project client.'],
                ['slug' => 'kas-operasional', 'code' => '1-130', 'name' => 'Kas Operasional', 'group' => 'Neraca', 'kind' => 'Aset', 'normal_balance' => 'Debit', 'note' => 'Pos neraca untuk dompet operasional lapangan.'],
                ['slug' => 'kas-tunai', 'code' => '1-140', 'name' => 'Kas Tunai', 'group' => 'Neraca', 'kind' => 'Aset', 'normal_balance' => 'Debit', 'note' => 'Kas kecil atau dana tunai yang dipegang langsung.'],
                ['slug' => 'piutang-usaha', 'code' => '1-210', 'name' => 'Piutang Usaha', 'group' => 'Neraca', 'kind' => 'Aset', 'normal_balance' => 'Debit', 'note' => 'Tagihan ke client atau pihak lain yang belum diterima.'],
                ['slug' => 'hutang-usaha', 'code' => '2-110', 'name' => 'Hutang Usaha', 'group' => 'Neraca', 'kind' => 'Kewajiban', 'normal_balance' => 'Kredit', 'note' => 'Kewajiban pembayaran ke vendor atau pihak ketiga.'],
                ['slug' => 'pendapatan-diterima-dimuka', 'code' => '2-120', 'name' => 'Pendapatan Diterima Dimuka', 'group' => 'Neraca', 'kind' => 'Kewajiban', 'normal_balance' => 'Kredit', 'note' => 'DP atau uang muka yang belum sepenuhnya menjadi pendapatan.'],
                ['slug' => 'modal-awal', 'code' => '3-100', 'name' => 'Modal Awal', 'group' => 'Neraca', 'kind' => 'Modal', 'normal_balance' => 'Kredit', 'note' => 'Pos modal pembuka untuk menyeimbangkan saldo awal.'],
            ],
            'transaction_categories' => [
                ['slug' => 'jasa-konsultasi', 'name' => 'Jasa Konsultasi', 'type' => 'Masuk', 'order' => 1, 'is_quick' => false, 'report_position_slug' => 'pendapatan-jasa', 'report_position_name' => 'Pendapatan Jasa', 'report_group' => 'Laba Rugi', 'note' => 'Untuk jasa konsultasi, pendampingan, dan project layanan.'],
                ['slug' => 'project-client', 'name' => 'Project Client', 'type' => 'Masuk', 'order' => 2, 'is_quick' => false, 'report_position_slug' => 'pendapatan-jasa', 'report_position_name' => 'Pendapatan Jasa', 'report_group' => 'Laba Rugi', 'note' => 'Untuk project website, aplikasi, dan kebutuhan client.'],
                ['slug' => 'pelatihan-workshop', 'name' => 'Pelatihan / Workshop', 'type' => 'Masuk', 'order' => 3, 'is_quick' => false, 'report_position_slug' => 'pendapatan-pelatihan', 'report_position_name' => 'Pendapatan Pelatihan', 'report_group' => 'Laba Rugi', 'note' => 'Untuk pelatihan berbayar, workshop, dan program edukasi.'],
                ['slug' => 'maintenance-retainer', 'name' => 'Maintenance / Retainer', 'type' => 'Masuk', 'order' => 4, 'is_quick' => false, 'report_position_slug' => 'pendapatan-maintenance', 'report_position_name' => 'Pendapatan Maintenance', 'report_group' => 'Laba Rugi', 'note' => 'Untuk kontrak maintenance dan perpanjangan layanan.'],
                ['slug' => 'transport', 'name' => 'Transport', 'type' => 'Keluar', 'order' => 5, 'is_quick' => true, 'chip_label' => 'Transport', 'report_position_slug' => 'beban-transport', 'report_position_name' => 'Beban Transport', 'report_group' => 'Laba Rugi', 'note' => 'Biaya perjalanan, pengiriman, dan transportasi operasional.'],
                ['slug' => 'konsumsi', 'name' => 'Konsumsi', 'type' => 'Keluar', 'order' => 6, 'is_quick' => true, 'chip_label' => 'Konsumsi', 'report_position_slug' => 'beban-konsumsi', 'report_position_name' => 'Beban Konsumsi', 'report_group' => 'Laba Rugi', 'note' => 'Biaya makan, minum, dan konsumsi kegiatan.'],
                ['slug' => 'cetak-dokumen', 'name' => 'Cetak Dokumen', 'type' => 'Keluar', 'order' => 7, 'is_quick' => true, 'chip_label' => 'Cetak', 'report_position_slug' => 'beban-cetak-dokumen', 'report_position_name' => 'Beban Cetak Dokumen', 'report_group' => 'Laba Rugi', 'note' => 'Untuk print, fotokopi, legalisasi, dan dokumen fisik.'],
                ['slug' => 'honor', 'name' => 'Honor', 'type' => 'Keluar', 'order' => 8, 'is_quick' => true, 'chip_label' => 'Honor', 'report_position_slug' => 'beban-honor', 'report_position_name' => 'Beban Honor', 'report_group' => 'Laba Rugi', 'note' => 'Untuk fee narasumber, tenaga bantu, dan honorarium.'],
                ['slug' => 'iklan', 'name' => 'Iklan', 'type' => 'Keluar', 'order' => 9, 'is_quick' => false, 'report_position_slug' => 'beban-pemasaran', 'report_position_name' => 'Beban Pemasaran', 'report_group' => 'Laba Rugi', 'note' => 'Untuk iklan, promosi, dan biaya akuisisi.'],
                ['slug' => 'internet-pulsa', 'name' => 'Internet/Pulsa', 'type' => 'Keluar', 'order' => 10, 'is_quick' => false, 'report_position_slug' => 'beban-komunikasi', 'report_position_name' => 'Beban Komunikasi', 'report_group' => 'Laba Rugi', 'note' => 'Untuk internet, pulsa, dan biaya komunikasi.'],
                ['slug' => 'sewa-venue', 'name' => 'Sewa/Venue', 'type' => 'Keluar', 'order' => 11, 'is_quick' => false, 'report_position_slug' => 'beban-sewa-venue', 'report_position_name' => 'Beban Sewa Venue', 'report_group' => 'Laba Rugi', 'note' => 'Untuk sewa ruang, tempat, dan sarana event.'],
                ['slug' => 'atk', 'name' => 'ATK', 'type' => 'Keluar', 'order' => 12, 'is_quick' => false, 'report_position_slug' => 'beban-atk', 'report_position_name' => 'Beban ATK', 'report_group' => 'Laba Rugi', 'note' => 'Untuk alat tulis kantor dan kebutuhan administrasi ringan.'],
                ['slug' => 'operasional', 'name' => 'Operasional', 'type' => 'Keluar', 'order' => 13, 'is_quick' => false, 'report_position_slug' => 'beban-operasional', 'report_position_name' => 'Beban Operasional', 'report_group' => 'Laba Rugi', 'note' => 'Untuk biaya operasional umum yang tidak masuk kategori lain.'],
                ['slug' => 'lainnya', 'name' => 'Lainnya', 'type' => 'Keluar', 'order' => 14, 'is_quick' => true, 'chip_label' => 'Lainnya', 'report_position_slug' => 'beban-lainnya', 'report_position_name' => 'Beban Lainnya', 'report_group' => 'Laba Rugi', 'note' => 'Penampung sementara untuk biaya yang belum dipisah lebih rinci.'],
            ],
            'book_periods' => [
                ['slug' => 'tb-2026', 'name' => 'Tahun Buku 2026', 'start' => '01 Jan 2026', 'end' => '31 Des 2026', 'status' => 'Aktif', 'note' => 'Dipakai untuk seluruh transaksi prototype saat ini.'],
                ['slug' => 'tb-2025', 'name' => 'Tahun Buku 2025', 'start' => '01 Jan 2025', 'end' => '31 Des 2025', 'status' => 'Ditutup', 'note' => 'Periode sebelumnya, sudah final untuk kebutuhan laporan tahunan.'],
            ],
            'opening_balances' => [
                ['slug' => 'saldo-awal-bri-2026', 'label' => 'BRI PT', 'type' => 'Rekening / Dompet', 'report_position_slug' => 'kas-bank-bri', 'report_position_name' => 'Kas di Bank BRI', 'book_period_slug' => 'tb-2026', 'book_period_name' => 'Tahun Buku 2026', 'amount' => 48000000, 'note' => 'Saldo pembuka rekening utama.'],
                ['slug' => 'saldo-awal-bca-2026', 'label' => 'BCA PT', 'type' => 'Rekening / Dompet', 'report_position_slug' => 'kas-bank-bca', 'report_position_name' => 'Kas di Bank BCA', 'book_period_slug' => 'tb-2026', 'book_period_name' => 'Tahun Buku 2026', 'amount' => 12000000, 'note' => 'Saldo pembuka rekening digital project client.'],
                ['slug' => 'saldo-awal-dana-2026', 'label' => 'Dana Operasional Cago', 'type' => 'Rekening / Dompet', 'report_position_slug' => 'kas-operasional', 'report_position_name' => 'Kas Operasional', 'book_period_slug' => 'tb-2026', 'book_period_name' => 'Tahun Buku 2026', 'amount' => 9250000, 'note' => 'Saldo pembuka dompet operasional.'],
                ['slug' => 'saldo-awal-kas-2026', 'label' => 'Kas Tunai', 'type' => 'Rekening / Dompet', 'report_position_slug' => 'kas-tunai', 'report_position_name' => 'Kas Tunai', 'book_period_slug' => 'tb-2026', 'book_period_name' => 'Tahun Buku 2026', 'amount' => 0, 'note' => 'Kas tunai belum memiliki saldo pembuka.'],
                ['slug' => 'saldo-awal-modal-2026', 'label' => 'Modal Awal', 'type' => 'Pos Laporan', 'report_position_slug' => 'modal-awal', 'report_position_name' => 'Modal Awal', 'book_period_slug' => 'tb-2026', 'book_period_name' => 'Tahun Buku 2026', 'amount' => 69250000, 'note' => 'Pos penyeimbang saldo awal untuk prototype.'],
                ['slug' => 'saldo-awal-hutang-2026', 'label' => 'Hutang Usaha', 'type' => 'Pos Laporan', 'report_position_slug' => 'hutang-usaha', 'report_position_name' => 'Hutang Usaha', 'book_period_slug' => 'tb-2026', 'book_period_name' => 'Tahun Buku 2026', 'amount' => 0, 'note' => 'Disiapkan walau belum ada transaksi kewajiban.'],
                ['slug' => 'saldo-awal-piutang-2026', 'label' => 'Piutang Usaha', 'type' => 'Pos Laporan', 'report_position_slug' => 'piutang-usaha', 'report_position_name' => 'Piutang Usaha', 'book_period_slug' => 'tb-2026', 'book_period_name' => 'Tahun Buku 2026', 'amount' => 0, 'note' => 'Disiapkan walau belum ada transaksi tagihan berjalan.'],
            ],
            'transactions' => [
                [
                    'type'          => 'masuk',
                    'period_slug'   => 'mei-2026',
                    'amount'        => 58000000,
                    'category'      => 'Jasa Konsultasi',
                    'unit_slug'     => 'konsultan-pendidikan',
                    'unit_name'     => 'Konsultan Pendidikan',
                    'activity_slug' => 'perizinan-lkp-tax-session',
                    'activity_name' => 'Perizinan LKP Tax Session',
                    'to_account'    => 'BRI PT',
                    'note'          => 'Pembayaran proyek masuk termin April',
                    'date'          => '17 Mei 2026',
                    'time'          => '09.20',
                ],
                [
                    'type'          => 'pindah',
                    'period_slug'   => 'mei-2026',
                    'amount'        => 10000000,
                    'unit_slug'     => 'konsultan-pendidikan',
                    'unit_name'     => 'Konsultan Pendidikan',
                    'activity_slug' => 'perizinan-lkp-tax-session',
                    'activity_name' => 'Perizinan LKP Tax Session',
                    'from_account'  => 'BRI PT',
                    'to_account'    => 'Dana Operasional Cago',
                    'note'          => 'Alokasi dana lapangan dan pengeluaran cepat',
                    'date'          => '16 Mei 2026',
                    'time'          => '14.10',
                ],
                [
                    'type'          => 'biaya',
                    'period_slug'   => 'mei-2026',
                    'amount'        => 250000,
                    'category'      => 'Transport',
                    'unit_slug'     => 'konsultan-pendidikan',
                    'unit_name'     => 'Konsultan Pendidikan',
                    'activity_slug' => 'perizinan-lkp-tax-session',
                    'activity_name' => 'Perizinan LKP Tax Session',
                    'from_account'  => 'Dana Operasional Cago',
                    'note'          => 'Transport koordinasi dan pengantaran dokumen',
                    'date'          => '15 Mei 2026',
                    'time'          => '08.45',
                ],
                [
                    'type'          => 'biaya',
                    'period_slug'   => 'mei-2026',
                    'amount'        => 500000,
                    'category'      => 'Konsumsi',
                    'unit_slug'     => 'konsultan-pendidikan',
                    'unit_name'     => 'Konsultan Pendidikan',
                    'activity_slug' => 'perizinan-lkp-tax-session',
                    'activity_name' => 'Perizinan LKP Tax Session',
                    'from_account'  => 'Dana Operasional Cago',
                    'note'          => 'Konsumsi meeting koordinasi dan visit lapangan',
                    'date'          => '13 Mei 2026',
                    'time'          => '12.30',
                ],
                [
                    'type'          => 'masuk',
                    'period_slug'   => 'mei-2026',
                    'amount'        => 12000000,
                    'category'      => 'Project Client',
                    'unit_slug'     => 'kebagusancode',
                    'unit_name'     => 'KebagusanCode',
                    'activity_slug' => 'project-website-client',
                    'activity_name' => 'Project Website Client',
                    'to_account'    => 'BCA PT',
                    'note'          => 'Down payment project website client',
                    'date'          => '11 Mei 2026',
                    'time'          => '10.05',
                ],
            ],
        ];
    }

    private function buildMaps(array $units): array
    {
        $unitMap     = [];
        $activityMap = [];

        foreach ($units as &$unit) {
            $unitMap[$unit['slug']] = &$unit;

            foreach ($unit['activities'] as &$activity) {
                $activity['unit_slug'] = $unit['slug'];
                $activity['unit_name'] = $unit['name'];
                $activityMap[$activity['slug']] = $activity;
            }
            unset($activity);
        }
        unset($unit);

        return [$units, $unitMap, $activityMap];
    }

    private function decorateTransactions(array $transactions): array
    {
        foreach ($transactions as $index => &$transaction) {
            $transaction['id'] = $transaction['id'] ?? 'trx-' . str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT);

            if ($transaction['type'] === 'masuk') {
                $transaction['badge_label']   = 'Uang Masuk';
                $transaction['badge_class']   = 'bg-emerald-50 text-emerald-700';
                $transaction['amount_class']  = 'text-emerald-600';
                $transaction['amount_prefix'] = '+';
                $transaction['headline']      = 'Masuk ke ' . $transaction['to_account'];
                $transaction['subline']       = $transaction['category'] . ' · ' . $transaction['activity_name'];
                $transaction['meta']          = $transaction['unit_name'] . ' · ' . $transaction['date'] . ' · ' . $transaction['time'];
                $transaction['icon']          = 'arrow_downward';
            }

            if ($transaction['type'] === 'biaya') {
                $transaction['badge_label']   = 'Biaya / Belanja';
                $transaction['badge_class']   = 'bg-rose-50 text-rose-600';
                $transaction['amount_class']  = 'text-rose-500';
                $transaction['amount_prefix'] = '-';
                $transaction['headline']      = $transaction['category'];
                $transaction['subline']       = 'Keluar dari ' . $transaction['from_account'];
                $transaction['meta']          = $transaction['unit_name'] . ' · ' . $transaction['date'] . ' · ' . $transaction['time'];
                $transaction['icon']          = 'receipt_long';
            }

            if ($transaction['type'] === 'pindah') {
                $transaction['badge_label']   = 'Pindah Dana';
                $transaction['badge_class']   = 'bg-sky-50 text-sky-700';
                $transaction['amount_class']  = 'text-zinc-700';
                $transaction['amount_prefix'] = '';
                $transaction['headline']      = $transaction['from_account'] . ' → ' . $transaction['to_account'];
                $transaction['subline']       = 'Tidak dihitung sebagai biaya';
                $transaction['meta']          = $transaction['unit_name'] . ' · ' . $transaction['date'] . ' · ' . $transaction['time'];
                $transaction['icon']          = 'swap_horiz';
            }
        }
        unset($transaction);

        return $transactions;
    }

    private function resolveContext(array $units, array $activityMap, ?string $requestedUnitSlug, ?string $requestedActivitySlug): array
    {
        $defaultUnit = $this->findUnit($units, 'konsultan-pendidikan') ?? $units[0];
        $defaultActivity = $defaultUnit['activities'][0];

        $requestedActivity = $requestedActivitySlug !== null && isset($activityMap[$requestedActivitySlug]) ? $activityMap[$requestedActivitySlug] : null;
        $requestedUnit = $requestedUnitSlug !== null ? $this->findUnit($units, $requestedUnitSlug) : null;

        if ($requestedActivity !== null) {
            $requestedUnit = $this->findUnit($units, $requestedActivity['unit_slug']) ?? $requestedUnit;
        }

        $unit = $requestedUnit ?? $defaultUnit;

        if ($requestedActivity !== null && $requestedActivity['unit_slug'] === $unit['slug']) {
            $activity = $requestedActivity;
        } else {
            $activity = $unit['activities'][0] ?? $defaultActivity;
        }

        return [
            'unit_slug'               => $unit['slug'],
            'unit_name'               => $unit['name'],
            'activity_slug'           => $activity['slug'],
            'activity_name'           => $activity['name'],
            'display'                 => $unit['name'] . ' / ' . $activity['name'],
            'related_balance'         => $activity['related_balance'] ?? 0,
            'related_accounts'        => $activity['related_accounts'] ?? [],
            'default_income_account'  => $activity['default_income_account'] ?? null,
            'default_expense_account' => $activity['default_expense_account'] ?? null,
            'default_transfer_from'   => $activity['default_transfer_from'] ?? null,
            'default_transfer_to'     => $activity['default_transfer_to'] ?? null,
        ];
    }

    private function buildSummary(array $transactions, array $accounts): array
    {
        $income = 0;
        $expense = 0;

        foreach ($transactions as $transaction) {
            if ($transaction['type'] === 'masuk') {
                $income += $transaction['amount'];
            }

            if ($transaction['type'] === 'biaya') {
                $expense += $transaction['amount'];
            }
        }

        return [
            'balance' => array_sum(array_column($accounts, 'balance')),
            'income'  => $income,
            'expense' => $expense,
            'surplus' => $income - $expense,
        ];
    }

    private function buildUnitSummaries(array $units, array $transactions, array $activeContext): array
    {
        foreach ($units as &$unit) {
            $unit['income'] = 0;
            $unit['expense'] = 0;
            $unit['surplus'] = 0;

            $quickActivity = $unit['slug'] === $activeContext['unit_slug']
                ? $this->findActivity($unit['activities'], $activeContext['activity_slug'])
                : null;

            if ($quickActivity === null) {
                $quickActivity = $unit['activities'][0];
            }

            $unit['quick_activity_slug'] = $quickActivity['slug'];
            $unit['quick_activity_name'] = $quickActivity['name'];
            $unit['detail_url']          = route_query('unit/' . $unit['slug'], ['kegiatan' => $quickActivity['slug']]);
            $unit['masuk_url']           = route_query('catat/masuk', ['unit' => $unit['slug'], 'kegiatan' => $quickActivity['slug']]);
            $unit['keluar_url']          = route_query('catat/keluar', ['unit' => $unit['slug'], 'kegiatan' => $quickActivity['slug']]);

            foreach ($unit['activities'] as &$activity) {
                $activity['income'] = 0;
                $activity['expense'] = 0;
                $activity['surplus'] = 0;
                $activity['detail_url'] = site_url('kegiatan/' . $activity['slug']);
                $activity['is_current'] = $activity['slug'] === $activeContext['activity_slug'];
            }
            unset($activity);
        }
        unset($unit);

        foreach ($transactions as $transaction) {
            foreach ($units as &$unit) {
                if ($unit['slug'] !== $transaction['unit_slug']) {
                    continue;
                }

                if ($transaction['type'] === 'masuk') {
                    $unit['income'] += $transaction['amount'];
                }

                if ($transaction['type'] === 'biaya') {
                    $unit['expense'] += $transaction['amount'];
                }

                foreach ($unit['activities'] as &$activity) {
                    if ($activity['slug'] !== $transaction['activity_slug']) {
                        continue;
                    }

                    if ($transaction['type'] === 'masuk') {
                        $activity['income'] += $transaction['amount'];
                    }

                    if ($transaction['type'] === 'biaya') {
                        $activity['expense'] += $transaction['amount'];
                    }
                }
                unset($activity);
            }
            unset($unit);
        }

        foreach ($units as &$unit) {
            $unit['surplus'] = $unit['income'] - $unit['expense'];

            foreach ($unit['activities'] as &$activity) {
                $activity['surplus'] = $activity['income'] - $activity['expense'];
            }
            unset($activity);
        }
        unset($unit);

        return $units;
    }

    private function buildActivitySummaries(array $units, array $transactions): array
    {
        $activities = [];

        foreach ($units as $unit) {
            foreach ($unit['activities'] as $activity) {
                $activities[] = [
                    'slug'            => $activity['slug'],
                    'name'            => $activity['name'],
                    'short_name'      => $activity['short_name'] ?? null,
                    'unit_slug'       => $unit['slug'],
                    'unit_name'       => $unit['name'],
                    'income'          => $activity['income'],
                    'expense'         => $activity['expense'],
                    'surplus'         => $activity['surplus'],
                    'related_balance' => $activity['related_balance'] ?? 0,
                    'related_accounts'=> $activity['related_accounts'] ?? [],
                    'detail_url'      => site_url('kegiatan/' . $activity['slug']),
                ];
            }
        }

        return $activities;
    }

    private function buildAccountSummaries(array $accounts, array $transactions, array $rekapQuery): array
    {
        $accountIndexes = [];

        foreach ($accounts as $index => $account) {
            $accounts[$index]['income'] = 0;
            $accounts[$index]['expense'] = 0;
            $accounts[$index]['transfer_in'] = 0;
            $accounts[$index]['transfer_out'] = 0;
            $accounts[$index]['movement_count'] = 0;
            $accounts[$index]['activity_map'] = [];
            $accounts[$index]['detail_url'] = route_query('rekening/' . $account['slug'], $rekapQuery);
            $accountIndexes[$account['name']] = $index;
        }

        foreach ($transactions as $transaction) {
            $touchedIndexes = [];

            if ($transaction['type'] === 'masuk' && isset($accountIndexes[$transaction['to_account']])) {
                $index = $accountIndexes[$transaction['to_account']];
                $accounts[$index]['income'] += $transaction['amount'];
                $touchedIndexes[] = $index;
            }

            if ($transaction['type'] === 'biaya' && isset($accountIndexes[$transaction['from_account']])) {
                $index = $accountIndexes[$transaction['from_account']];
                $accounts[$index]['expense'] += $transaction['amount'];
                $touchedIndexes[] = $index;
            }

            if ($transaction['type'] === 'pindah') {
                if (isset($accountIndexes[$transaction['from_account']])) {
                    $index = $accountIndexes[$transaction['from_account']];
                    $accounts[$index]['transfer_out'] += $transaction['amount'];
                    $touchedIndexes[] = $index;
                }

                if (isset($accountIndexes[$transaction['to_account']])) {
                    $index = $accountIndexes[$transaction['to_account']];
                    $accounts[$index]['transfer_in'] += $transaction['amount'];
                    $touchedIndexes[] = $index;
                }
            }

            foreach (array_unique($touchedIndexes) as $index) {
                $accounts[$index]['movement_count']++;
                $accounts[$index]['activity_map'][$transaction['activity_slug']] = $transaction['activity_name'];
            }
        }

        foreach ($accounts as &$account) {
            $account['incoming_total'] = $account['income'] + $account['transfer_in'];
            $account['outgoing_total'] = $account['expense'] + $account['transfer_out'];
            $account['activity_count'] = count($account['activity_map']);
            $account['preview_activity'] = $account['activity_map'] === []
                ? 'Belum ada mutasi pada filter ini'
                : reset($account['activity_map']);
            unset($account['activity_map']);
        }
        unset($account);

        return $accounts;
    }

    private function filterTransactions(array $transactions, string $periodSlug, ?string $unitSlug, ?string $activitySlug): array
    {
        return array_values(
            array_filter(
                $transactions,
                static function (array $transaction) use ($periodSlug, $unitSlug, $activitySlug): bool {
                    if ($periodSlug !== 'semua' && $transaction['period_slug'] !== $periodSlug) {
                        return false;
                    }

                    if ($unitSlug !== null && $transaction['unit_slug'] !== $unitSlug) {
                        return false;
                    }

                    if ($activitySlug !== null && $transaction['activity_slug'] !== $activitySlug) {
                        return false;
                    }

                    return true;
                }
            )
        );
    }

    private function buildCategorySummary(array $categories, array $transactions): array
    {
        $summary = [];

        foreach ($categories as $category) {
            $summary[] = [
                'name'   => $category['name'],
                'amount' => 0,
            ];
        }

        foreach ($transactions as $transaction) {
            if ($transaction['type'] !== 'biaya') {
                continue;
            }

            foreach ($summary as &$item) {
                if ($item['name'] === $transaction['category']) {
                    $item['amount'] += $transaction['amount'];
                    break;
                }
            }
            unset($item);
        }

        return $summary;
    }

    private function filterAccountTransactions(array $transactions, string $accountName): array
    {
        return array_values(
            array_filter(
                $transactions,
                static function (array $transaction) use ($accountName): bool {
                    return ($transaction['from_account'] ?? null) === $accountName
                        || ($transaction['to_account'] ?? null) === $accountName;
                }
            )
        );
    }

    private function buildAccountActivityBreakdown(array $transactions, string $accountName): array
    {
        $activities = [];

        foreach ($transactions as $transaction) {
            $slug = $transaction['activity_slug'];

            if (! isset($activities[$slug])) {
                $activities[$slug] = [
                    'slug' => $slug,
                    'name' => $transaction['activity_name'],
                    'unit_name' => $transaction['unit_name'],
                    'detail_url' => site_url('kegiatan/' . $slug),
                    'income' => 0,
                    'expense' => 0,
                    'transfer_in' => 0,
                    'transfer_out' => 0,
                    'movement_total' => 0,
                ];
            }

            if ($transaction['type'] === 'masuk' && ($transaction['to_account'] ?? null) === $accountName) {
                $activities[$slug]['income'] += $transaction['amount'];
                $activities[$slug]['movement_total'] += $transaction['amount'];
            }

            if ($transaction['type'] === 'biaya' && ($transaction['from_account'] ?? null) === $accountName) {
                $activities[$slug]['expense'] += $transaction['amount'];
                $activities[$slug]['movement_total'] += $transaction['amount'];
            }

            if ($transaction['type'] === 'pindah') {
                if (($transaction['to_account'] ?? null) === $accountName) {
                    $activities[$slug]['transfer_in'] += $transaction['amount'];
                    $activities[$slug]['movement_total'] += $transaction['amount'];
                }

                if (($transaction['from_account'] ?? null) === $accountName) {
                    $activities[$slug]['transfer_out'] += $transaction['amount'];
                    $activities[$slug]['movement_total'] += $transaction['amount'];
                }
            }
        }

        usort(
            $activities,
            static fn(array $left, array $right): int => $right['movement_total'] <=> $left['movement_total']
        );

        return array_values($activities);
    }

    private function resolveCategory(array $categories, ?string $requestedCategory): string
    {
        $names = array_column($categories, 'name');

        if ($requestedCategory !== null && in_array($requestedCategory, $names, true)) {
            return $requestedCategory;
        }

        return $names[0] ?? '';
    }

    private function resolvePeriod(array $periods, ?string $requestedPeriod): string
    {
        $available = array_column($periods, 'slug');

        if ($requestedPeriod !== null && in_array($requestedPeriod, $available, true)) {
            return $requestedPeriod;
        }

        return 'mei-2026';
    }

    private function resolveRekapFilters(array $units, array $activityMap, ?string $requestedUnitSlug, ?string $requestedActivitySlug): array
    {
        $selectedUnitSlug = $requestedUnitSlug !== null && $this->findUnit($units, $requestedUnitSlug) !== null ? $requestedUnitSlug : 'semua';
        $selectedActivitySlug = 'semua';

        if ($requestedActivitySlug !== null && isset($activityMap[$requestedActivitySlug])) {
            $activity = $activityMap[$requestedActivitySlug];

            if ($selectedUnitSlug === 'semua' || $selectedUnitSlug === $activity['unit_slug']) {
                $selectedActivitySlug = $requestedActivitySlug;
                $selectedUnitSlug = $activity['unit_slug'];
            }
        }

        return [$selectedUnitSlug, $selectedActivitySlug];
    }

    private function buildDisplayedAccounts(array $accounts, array $transactions, string $selectedUnitSlug, string $selectedActivitySlug, array $unitMap, array $activityMap): array
    {
        if ($selectedUnitSlug === 'semua' && $selectedActivitySlug === 'semua') {
            return $accounts;
        }

        $usedNames = [];

        foreach ($transactions as $transaction) {
            if (isset($transaction['from_account'])) {
                $usedNames[] = $transaction['from_account'];
            }

            if (isset($transaction['to_account'])) {
                $usedNames[] = $transaction['to_account'];
            }
        }

        if ($usedNames === [] && $selectedActivitySlug !== 'semua' && isset($activityMap[$selectedActivitySlug])) {
            $usedNames = $activityMap[$selectedActivitySlug]['related_accounts'] ?? [];
        }

        if ($usedNames === [] && $selectedUnitSlug !== 'semua' && isset($unitMap[$selectedUnitSlug])) {
            foreach ($unitMap[$selectedUnitSlug]['activities'] as $activity) {
                foreach ($activity['related_accounts'] ?? [] as $accountName) {
                    $usedNames[] = $accountName;
                }
            }
        }

        if ($usedNames === []) {
            return $accounts;
        }

        $usedNames = array_values(array_unique($usedNames));

        return array_values(
            array_filter(
                $accounts,
                static fn(array $account): bool => in_array($account['name'], $usedNames, true)
            )
        );
    }

    private function filterUnitSummaries(array $units, string $selectedUnitSlug): array
    {
        if ($selectedUnitSlug === 'semua') {
            return $units;
        }

        return array_values(
            array_filter(
                $units,
                static fn(array $unit): bool => $unit['slug'] === $selectedUnitSlug
            )
        );
    }

    private function filterActivitySummaries(array $activities, string $selectedUnitSlug, string $selectedActivitySlug): array
    {
        return array_values(
            array_filter(
                $activities,
                static function (array $activity) use ($selectedUnitSlug, $selectedActivitySlug): bool {
                    if ($selectedActivitySlug !== 'semua') {
                        return $activity['slug'] === $selectedActivitySlug;
                    }

                    if ($selectedUnitSlug !== 'semua') {
                        return $activity['unit_slug'] === $selectedUnitSlug;
                    }

                    return true;
                }
            )
        );
    }

    private function buildFilterActivities(array $units, string $selectedUnitSlug): array
    {
        $activities = [];

        foreach ($units as $unit) {
            if ($selectedUnitSlug !== 'semua' && $unit['slug'] !== $selectedUnitSlug) {
                continue;
            }

            foreach ($unit['activities'] as $activity) {
                $activities[] = [
                    'slug'      => $activity['slug'],
                    'name'      => $activity['name'],
                    'unit_name' => $unit['name'],
                ];
            }
        }

        return $activities;
    }

    private function buildRekapFilterSummary(array $periods, array $units, array $activityMap, string $selectedPeriodSlug, string $selectedUnitSlug, string $selectedActivitySlug): array
    {
        $periodLabel = 'Semua Periode';

        foreach ($periods as $period) {
            if ($period['slug'] === $selectedPeriodSlug) {
                $periodLabel = $period['label'];
                break;
            }
        }

        $unitLabel = 'Semua Unit / Program';

        if ($selectedUnitSlug !== 'semua') {
            $unit = $this->findUnit($units, $selectedUnitSlug);
            $unitLabel = $unit['name'] ?? $unitLabel;
        }

        $activityLabel = 'Semua Kegiatan';

        if ($selectedActivitySlug !== 'semua' && isset($activityMap[$selectedActivitySlug])) {
            $activityLabel = $activityMap[$selectedActivitySlug]['name'];
        }

        return [
            'period_label' => $periodLabel,
            'unit_label' => $unitLabel,
            'activity_label' => $activityLabel,
        ];
    }

    private function buildTransactionCategories(array $items): array
    {
        foreach ($items as &$item) {
            $item['status'] = $item['status'] ?? 'Aktif';
            $item['chip_label'] = $item['chip_label'] ?? $item['name'];
        }
        unset($item);

        usort(
            $items,
            static fn(array $left, array $right): int => $left['order'] <=> $right['order']
        );

        return $items;
    }

    private function filterCategoriesByType(array $categories, string $type): array
    {
        return array_values(
            array_filter(
                $categories,
                static fn(array $category): bool => $category['type'] === $type
            )
        );
    }

    private function filterReportPositionsByKind(array $positions, string $kind): array
    {
        return array_values(
            array_filter(
                $positions,
                static fn(array $position): bool => $position['kind'] === $kind
            )
        );
    }

    private function filterReportPositionsByKinds(array $positions, array $kinds): array
    {
        return array_values(
            array_filter(
                $positions,
                static fn(array $position): bool => in_array($position['kind'], $kinds, true)
            )
        );
    }

    private function filterReportPositionsByGroup(array $positions, string $group): array
    {
        return array_values(
            array_filter(
                $positions,
                static fn(array $position): bool => $position['group'] === $group
            )
        );
    }

    private function buildOpeningBalanceSources(array $accounts, array $positions): array
    {
        $sources = array_map(static fn(array $account): string => $account['name'], $accounts);

        foreach ($positions as $position) {
            if (in_array($position['kind'], ['Aset', 'Kewajiban', 'Modal'], true)) {
                $sources[] = $position['name'];
            }
        }

        return array_values(array_unique($sources));
    }

    private function buildOpeningBalancePositions(array $positions): array
    {
        return array_values(
            array_map(
                static fn(array $position): string => $position['name'],
                $this->filterReportPositionsByGroup($positions, 'Neraca')
            )
        );
    }

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

    private function findUnit(array $units, string $slug): ?array
    {
        foreach ($units as $unit) {
            if ($unit['slug'] === $slug) {
                return $unit;
            }
        }

        return null;
    }

    private function findActivity(array $activities, string $slug): ?array
    {
        foreach ($activities as $activity) {
            if ($activity['slug'] === $slug) {
                return $activity;
            }
        }

        return null;
    }

    private function findAccount(array $accounts, string $slug): ?array
    {
        foreach ($accounts as $account) {
            if (($account['slug'] ?? null) === $slug) {
                return $account;
            }
        }

        return null;
    }

    private function findCategoryItem(array $categories, string $slug): ?array
    {
        foreach ($categories as $category) {
            if (($category['slug'] ?? null) === $slug) {
                return $category;
            }
        }

        return null;
    }

    private function findTransaction(array $transactions, string $id): ?array
    {
        foreach ($transactions as $transaction) {
            if (($transaction['id'] ?? null) === $id) {
                return $transaction;
            }
        }

        return null;
    }

    private function buildTransactionFormData(array $transaction, array $data): array
    {
        return [
            'unit_options' => array_column($data['units'], 'name'),
            'activity_options' => array_map(
                static fn(array $activity): string => $activity['name'] . ' · ' . $activity['unit_name'],
                $data['activitySummaries']
            ),
            'income_category_options' => array_column($data['incomeCategories'], 'name'),
            'expense_category_options' => array_column($data['expenseCategories'], 'name'),
            'account_options' => array_column($data['accounts'], 'name'),
            'unit_value' => $transaction['unit_name'],
            'activity_value' => $transaction['activity_name'] . ' · ' . $transaction['unit_name'],
            'nominal_value' => rupiah($transaction['amount']),
            'date_value' => $transaction['date'],
            'description_value' => $transaction['note'],
        ];
    }

    private function findReportPosition(array $positions, string $slug): ?array
    {
        foreach ($positions as $position) {
            if (($position['slug'] ?? null) === $slug) {
                return $position;
            }
        }

        return null;
    }

    private function findBookPeriod(array $periods, string $slug): ?array
    {
        foreach ($periods as $period) {
            if (($period['slug'] ?? null) === $slug) {
                return $period;
            }
        }

        return null;
    }

    private function findOpeningBalance(array $balances, string $slug): ?array
    {
        foreach ($balances as $balance) {
            if (($balance['slug'] ?? null) === $slug) {
                return $balance;
            }
        }

        return null;
    }

    private function findActivityFromUnits(array $units, string $slug): ?array
    {
        foreach ($units as $unit) {
            foreach ($unit['activities'] as $activity) {
                if ($activity['slug'] === $slug) {
                    return $activity;
                }
            }
        }

        return null;
    }

    private function findIndexBySlug(array $items, string $slug): int
    {
        foreach (array_values($items) as $index => $item) {
            if (($item['slug'] ?? null) === $slug) {
                return $index;
            }
        }

        return 0;
    }

}
