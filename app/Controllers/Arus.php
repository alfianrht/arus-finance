<?php

namespace App\Controllers;

use App\Models\AccountModel;
use App\Models\ActivityModel;
use App\Models\BookPeriodModel;
use App\Models\InstitutionModel;
use App\Models\OpeningBalanceModel;
use App\Models\ReceiverModel;
use App\Models\ReportPositionModel;
use App\Models\TransactionCategoryModel;
use App\Models\UnitModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;

class Arus extends BaseController
{
    public function home(): string
    {
        $institutionId = $this->currentInstitutionId();
        $institution = $this->currentInstitution();
        $bookPeriodId = $this->getActiveBookPeriodId($institutionId);
        $units = $this->loadUnitProgramRows();
        $contextSelection = $this->resolveActiveContextSelection($units);

        $db = \Config\Database::connect();
        
        $obSum = (float) ($db->table('opening_balances')
            ->where('institution_id', $institutionId)
            ->where('book_period_id', $bookPeriodId)
            ->where('deleted_at', null)
            ->selectSum('amount')
            ->get()->getRow()->amount ?? 0);
            
        $incSum = (float) ($db->table('transactions')
            ->where('institution_id', $institutionId)
            ->where('book_period_id', $bookPeriodId)
            ->where('type', 'masuk')
            ->where('deleted_at', null)
            ->selectSum('amount')
            ->get()->getRow()->amount ?? 0);
            
        $expSum = (float) ($db->table('transactions')
            ->where('institution_id', $institutionId)
            ->where('book_period_id', $bookPeriodId)
            ->where('type', 'keluar')
            ->where('deleted_at', null)
            ->select('SUM(amount + admin_fee) as total')
            ->get()->getRow()->total ?? 0);

        $surplus = $incSum - $expSum;
        $balance = $obSum + $surplus;

        // Recent transactions
        $recentRows = clone $db->table('transactions')->where('institution_id', $institutionId)->where('deleted_at', null)->orderBy('transaction_date', 'DESC')->orderBy('id', 'DESC')->limit(4)->get()->getResultArray();
        $homeTransactions = [];
        foreach ($recentRows as $row) {
            $cat = $db->table('transaction_categories')->where('id', $row['category_id'])->get()->getRowArray();
            $homeTransactions[] = [
                'id' => $row['id'],
                'headline' => $cat['name'] ?? 'Transaksi',
                'subline' => $row['notes'],
                'meta' => date('d M Y', strtotime($row['transaction_date'])),
                'badge_class' => $row['type'] === 'masuk' ? 'bg-emerald-100 text-emerald-900' : 'bg-rose-100 text-rose-900',
                'badge_label' => $row['type'] === 'masuk' ? 'Masuk' : 'Keluar',
                'icon' => $row['type'] === 'masuk' ? 'south_west' : 'north_east',
                'amount_class' => $row['type'] === 'masuk' ? 'text-emerald-600' : 'text-rose-600',
                'amount_prefix' => $row['type'] === 'masuk' ? '+' : '-',
                'amount' => $row['amount'] + $row['admin_fee'],
            ];
        }

        // Units mapping for cards
        foreach ($units as &$u) {
            $uInc = (float) ($db->table('transactions')->where('unit_id', $u['id'])->where('type', 'masuk')->where('deleted_at', null)->selectSum('amount')->get()->getRow()->amount ?? 0);
            $uExp = (float) ($db->table('transactions')->where('unit_id', $u['id'])->where('type', 'keluar')->where('deleted_at', null)->select('SUM(amount + admin_fee) as total')->get()->getRow()->total ?? 0);
            $uActCount = count($u['activities'] ?? []);
            $uFirstAct = $u['activities'][0] ?? null;
            
            $u['income'] = $uInc;
            $u['expense'] = $uExp;
            $u['surplus'] = $uInc - $uExp;
            $u['quick_activity_name'] = $uFirstAct['name'] ?? 'Belum ada kegiatan';
            $u['detail_url'] = '#';
            $u['masuk_url'] = route_query('catat/masuk', ['unit' => $u['slug'], 'kegiatan' => $uFirstAct['slug'] ?? null]);
            $u['keluar_url'] = route_query('catat/keluar', ['unit' => $u['slug'], 'kegiatan' => $uFirstAct['slug'] ?? null]);
        }
        unset($u);

        $activeContext = [
            'unit_id' => $contextSelection['unit_id'],
            'activity_id' => $contextSelection['activity_id'],
            'unit_slug' => $contextSelection['unit_slug'],
            'activity_slug' => $contextSelection['activity_slug'],
            'unit_name' => $contextSelection['unit_name'],
            'activity_name' => $contextSelection['activity_name'],
            'display' => $contextSelection['unit_name'] . ' / ' . $contextSelection['activity_name'],
            'query' => [
                'unit' => $contextSelection['unit_slug'] ?: null,
                'kegiatan' => $contextSelection['activity_slug'] ?: null,
            ],
            'switch_url' => site_url('konteks-aktif'),
            'switch_redirect' => site_url('catat'),
            'switch_params' => [],
            'activity_url' => $contextSelection['activity_slug'] !== '' ? site_url('kegiatan/' . $contextSelection['activity_slug']) : site_url('beranda'),
            'masuk_url' => site_url('catat/masuk'),
            'keluar_url' => site_url('catat/keluar'),
        ];

        // Ensure settingsShortcuts are passed for the dashboard block
        $settingsShortcuts = $this->buildSettingsShortcuts([
            'institutionName'       => $institution['name'],
            'units'                 => $units,
            'activitySummaries'     => [],
            'accounts'              => [],
            'transactionCategories' => [],
            'reportPositions'       => [],
            'bookPeriods'           => [],
            'openingBalances'       => [],
            'receivers'             => [],
        ]);

        $data = [
            'appName'          => $institution['app_name'] ?? 'Arusdana',
            'institutionName'  => $institution['name'],
            'pageTitle'        => 'Beranda',
            'activeNav'        => 'beranda',
            'activeContext'    => $activeContext,
            'summary'          => [
                'balance' => $balance,
                'income'  => $incSum,
                'expense' => $expSum,
                'surplus' => $surplus,
            ],
            'homeTransactions'  => $homeTransactions,
            'units'             => $units,
            'settingsShortcuts' => $settingsShortcuts,
        ];

        return view('pages/home', $data);
    }

    public function catat(): string
    {
        $institutionId = $this->currentInstitutionId();
        $db = \Config\Database::connect();
        
        $units = $this->loadUnitProgramRows();
        $activities = $db->table('activities')->where('institution_id', $institutionId)->where('deleted_at', null)->get()->getResultArray();
        
        $recentRows = $db->table('transactions')
            ->where('institution_id', $institutionId)
            ->where('deleted_at', null)
            ->orderBy('transaction_date', 'DESC')
            ->orderBy('id', 'DESC')
            ->limit(4)
            ->get()->getResultArray();
        
        $recentTransactions = [];
        foreach ($recentRows as $row) {
            $cat = $db->table('transaction_categories')->where('id', $row['category_id'])->get()->getRowArray();
            $recentTransactions[] = [
                'id' => $row['id'],
                'headline' => $cat['name'] ?? 'Transaksi',
                'subline' => $row['notes'],
                'meta' => date('d M Y', strtotime($row['transaction_date'])),
                'badge_class' => $row['type'] === 'masuk' ? 'bg-emerald-100 text-emerald-900' : 'bg-rose-100 text-rose-900',
                'badge_label' => $row['type'] === 'masuk' ? 'Masuk' : 'Keluar',
                'icon' => $row['type'] === 'masuk' ? 'south_west' : 'north_east',
                'amount_class' => $row['type'] === 'masuk' ? 'text-emerald-600' : 'text-rose-600',
                'amount_prefix' => $row['type'] === 'masuk' ? '+' : '-',
                'amount' => $row['amount'] + $row['admin_fee'],
            ];
        }

        $quickCategories = $db->table('transaction_categories')
            ->where('institution_id', $institutionId)
            ->where('type', 'keluar')
            ->where('deleted_at', null)
            ->limit(5)
            ->get()->getResultArray();
        
        $quickFormatted = [];
        foreach ($quickCategories as $c) {
            $quickFormatted[] = [
                'label' => $c['name'],
                'value' => $c['name'],
                'href' => route_query('catat/keluar/biaya', ['kategori' => $c['name']])
            ];
        }

        $data = [
            'pageTitle' => 'Catat',
            'activeNav' => 'catat',
            'units' => $units,
            'activitySummaries' => $activities,
            'recentTransactions' => $recentTransactions,
            'quickCategories' => $quickFormatted,
            'activeContext' => [
                'unit_slug' => '',
                'activity_slug' => '',
                'masuk_url' => site_url('catat/masuk'),
                'keluar_url' => site_url('catat/keluar'),
                'activity_url' => site_url('beranda'),
                'query' => [],
            ]
        ];

        return view('pages/catat/index', $data);
    }

    public function catatMasuk(): string
    {
        $institutionId = $this->currentInstitutionId();
        $db = \Config\Database::connect();
        
        $units = $this->loadUnitProgramRows();
        $activities = $db->table('activities')->where('institution_id', $institutionId)->where('deleted_at', null)->get()->getResultArray();
        $accounts = $db->table('accounts')->where('institution_id', $institutionId)->where('deleted_at', null)->get()->getResultArray();
        $categories = $db->table('transaction_categories')->where('institution_id', $institutionId)->where('type', 'masuk')->where('deleted_at', null)->get()->getResultArray();
        
        $data = [
            'pageTitle' => 'Uang Masuk',
            'activeNav' => 'catat',
            'backUrl' => site_url('catat'),
            'units' => $units,
            'activitySummaries' => $activities,
            'accounts' => $accounts,
            'incomeCategories' => $categories,
            'selectedIncomeCategory' => $categories[0]['name'] ?? '',
            'activeContext' => [
                'unit_slug' => $this->request->getGet('unit') ?? '',
                'activity_slug' => $this->request->getGet('kegiatan') ?? '',
                'default_income_account' => '',
            ]
        ];

        return view('pages/catat/masuk', $data);
    }

    public function simpanMasuk()
    {
        $institutionId = $this->currentInstitutionId();
        $bookPeriodId = $this->getActiveBookPeriodId($institutionId);

        $rules = [
            'unit_id'          => 'required|is_natural_no_zero',
            'activity_id'      => 'required|is_natural_no_zero',
            'amount'           => 'required',
            'category_id'      => 'required|is_natural_no_zero',
            'to_account_id'    => 'required|is_natural_no_zero',
            'transaction_date' => 'required|valid_date[Y-m-d]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Semua kolom wajib diisi dengan benar.');
        }

        $amountStr = preg_replace('/[^0-9]/', '', (string) $this->request->getPost('amount'));
        $amount = (int) $amountStr;

        $data = [
            'institution_id'   => $institutionId,
            'book_period_id'   => $bookPeriodId,
            'type'             => 'masuk',
            'amount'           => $amount,
            'admin_fee'        => 0,
            'unit_id'          => $this->request->getPost('unit_id'),
            'activity_id'      => $this->request->getPost('activity_id'),
            'category_id'      => $this->request->getPost('category_id'),
            'from_account_id'  => null,
            'to_account_id'    => $this->request->getPost('to_account_id'),
            'receiver_id'      => null,
            'transaction_date' => $this->request->getPost('transaction_date'),
            'transaction_time' => date('H:i:s'),
            'notes'            => $this->request->getPost('notes'),
            'created_by'       => $this->currentUser()['id'],
        ];

        (new \App\Models\TransactionModel())->insert($data);

        $action = $this->request->getPost('action');
        if ($action === 'save_add') {
            return redirect()->back()->with('success', 'Uang masuk berhasil dicatat. Silakan tambah lagi.');
        }

        return redirect()->to('catat')->with('success', 'Uang masuk berhasil dicatat.');
    }

    public function catatKeluar(): string
    {
        $institutionId = $this->currentInstitutionId();
        $db = \Config\Database::connect();
        
        $units = $this->loadUnitProgramRows();
        $activities = $db->table('activities')->where('institution_id', $institutionId)->where('deleted_at', null)->get()->getResultArray();
        
        $data = [
            'pageTitle' => 'Uang Keluar',
            'activeNav' => 'catat',
            'backUrl' => site_url('catat'),
            'units' => $units,
            'activitySummaries' => $activities,
            'activeContext' => [
                'unit_slug' => $this->request->getGet('unit') ?? '',
                'activity_slug' => $this->request->getGet('kegiatan') ?? '',
                'query' => [
                    'unit' => $this->request->getGet('unit') ?? '',
                    'kegiatan' => $this->request->getGet('kegiatan') ?? '',
                ]
            ]
        ];

        return view('pages/catat/keluar', $data);
    }

    public function catatBiaya(): string
    {
        $institutionId = $this->currentInstitutionId();
        $db = \Config\Database::connect();
        
        $units = $this->loadUnitProgramRows();
        $activities = $db->table('activities')->where('institution_id', $institutionId)->where('deleted_at', null)->get()->getResultArray();
        $accounts = $db->table('accounts')->where('institution_id', $institutionId)->where('deleted_at', null)->get()->getResultArray();
        $categories = $db->table('transaction_categories')->where('institution_id', $institutionId)->where('type', 'keluar')->where('deleted_at', null)->get()->getResultArray();
        
        $data = [
            'pageTitle' => 'Catat Biaya',
            'activeNav' => 'catat',
            'backUrl' => site_url('catat/keluar'),
            'units' => $units,
            'activitySummaries' => $activities,
            'accounts' => $accounts,
            'expenseCategories' => $categories,
            'activeContext' => [
                'unit_slug' => $this->request->getGet('unit') ?? '',
                'activity_slug' => $this->request->getGet('kegiatan') ?? '',
                'default_expense_account' => '',
            ]
        ];

        return view('pages/catat/biaya', $data);
    }

    public function simpanBiaya()
    {
        $institutionId = $this->currentInstitutionId();
        $bookPeriodId = $this->getActiveBookPeriodId($institutionId);

        $rules = [
            'unit_id'          => 'required|is_natural_no_zero',
            'activity_id'      => 'required|is_natural_no_zero',
            'amount'           => 'required',
            'category_id'      => 'required|is_natural_no_zero',
            'from_account_id'  => 'required|is_natural_no_zero',
            'transaction_date' => 'required|valid_date[Y-m-d]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Semua kolom wajib diisi dengan benar.');
        }

        $amountStr = preg_replace('/[^0-9]/', '', (string) $this->request->getPost('amount'));
        $amount = (int) $amountStr;

        $adminFeePreset = $this->request->getPost('admin_fee_preset');
        if ($adminFeePreset === 'manual') {
            $adminFeeStr = preg_replace('/[^0-9]/', '', (string) $this->request->getPost('admin_fee_custom'));
            $adminFee = (int) $adminFeeStr;
        } else {
            $adminFee = (int) $adminFeePreset;
        }

        $data = [
            'institution_id'   => $institutionId,
            'book_period_id'   => $bookPeriodId,
            'type'             => 'keluar', // Atau bisa spesifik 'keluar_biaya' kalau mau, tapi enum-nya 'keluar'
            'amount'           => $amount,
            'admin_fee'        => $adminFee,
            'unit_id'          => $this->request->getPost('unit_id'),
            'activity_id'      => $this->request->getPost('activity_id'),
            'category_id'      => $this->request->getPost('category_id'),
            'from_account_id'  => $this->request->getPost('from_account_id'),
            'to_account_id'    => null,
            'receiver_id'      => null,
            'transaction_date' => $this->request->getPost('transaction_date'),
            'transaction_time' => date('H:i:s'),
            'notes'            => $this->request->getPost('notes'),
            'created_by'       => $this->currentUser()['id'],
        ];

        (new \App\Models\TransactionModel())->insert($data);

        $action = $this->request->getPost('action');
        if ($action === 'save_add') {
            return redirect()->back()->with('success', 'Biaya berhasil dicatat. Silakan tambah lagi.');
        }

        return redirect()->to('catat/keluar')->with('success', 'Biaya berhasil dicatat.');
    }

    public function catatHonorGaji(): string
    {
        $institutionId = $this->currentInstitutionId();
        $db = \Config\Database::connect();
        
        $units = $this->loadUnitProgramRows();
        $accounts = $db->table('accounts')->where('institution_id', $institutionId)->where('deleted_at', null)->get()->getResultArray();
        $receivers = $db->table('receivers')->where('institution_id', $institutionId)->where('deleted_at', null)->get()->getResultArray();
        
        // Find a specific category for honor
        $honorCat = $db->table('transaction_categories')
            ->where('institution_id', $institutionId)
            ->where('deleted_at', null)
            ->where('type', 'keluar')
            ->like('name', 'Honor', 'both')
            ->get()->getRowArray();
        
        $activities = $db->table('activities')->where('institution_id', $institutionId)->where('deleted_at', null)->get()->getResultArray();

        $data = [
            'pageTitle'  => 'Honor & Gaji',
            'activeNav'  => 'catat',
            'backUrl'    => site_url('catat/keluar'),
            'units'      => $units,
            'accounts'   => $accounts,
            'receivers'  => $receivers,
            'activities' => $activities,
            'honorCat'   => $honorCat,
        ];

        return view('pages/catat/honor_gaji', $data);
    }

    public function simpanHonorGaji()
    {
        $institutionId = $this->currentInstitutionId();
        $bookPeriodId = $this->getActiveBookPeriodId($institutionId);

        $rules = [
            'amount' => 'required',
            'receiver_id' => 'required|numeric',
            'unit_id' => 'required|numeric',
            'activity_id' => 'required|numeric',
            'from_account_id' => 'required|numeric',
            'transaction_date' => 'required',
            'notes' => 'required',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Gagal menyimpan. Pastikan semua field terisi dengan benar.');
        }

        $amountStr = $this->request->getPost('amount');
        $amount = (int) preg_replace('/[^0-9]/', '', $amountStr);

        $adminFeeInput = $this->request->getPost('admin_fee');
        if ($adminFeeInput === 'manual') {
            $adminFeeStr = $this->request->getPost('admin_fee_manual');
            $adminFee = (int) preg_replace('/[^0-9]/', '', $adminFeeStr);
        } else {
            $adminFee = (int) preg_replace('/[^0-9]/', '', $adminFeeInput);
        }

        $categoryId = $this->request->getPost('category_id');

        $data = [
            'institution_id' => $institutionId,
            'book_period_id' => $bookPeriodId,
            'type' => 'keluar',
            'amount' => $amount,
            'admin_fee' => $adminFee,
            'receiver_id' => $this->request->getPost('receiver_id'),
            'unit_id' => $this->request->getPost('unit_id'),
            'activity_id' => $this->request->getPost('activity_id'),
            'category_id' => $categoryId,
            'from_account_id' => $this->request->getPost('from_account_id'),
            'transaction_date' => date('Y-m-d', strtotime($this->request->getPost('transaction_date'))),
            'transaction_time' => date('H:i:s'),
            'notes' => $this->request->getPost('notes'),
            'created_by' => 1,
        ];

        (new \App\Models\TransactionModel())->insert($data);

        $action = $this->request->getPost('action');
        if ($action === 'save_add') {
            return redirect()->back()->with('success', 'Honor & Gaji berhasil dicatat. Silakan tambah lagi.');
        }

        return redirect()->to('beranda')->with('success', 'Honor & Gaji berhasil dicatat.');
    }

    public function catatPindahDana(): string
    {
        $institutionId = $this->currentInstitutionId();
        $db = \Config\Database::connect();
        
        $units = $this->loadUnitProgramRows();
        $accounts = $db->table('accounts')->where('institution_id', $institutionId)->where('deleted_at', null)->get()->getResultArray();
        $activities = $db->table('activities')->where('institution_id', $institutionId)->where('deleted_at', null)->get()->getResultArray();

        $data = [
            'pageTitle'  => 'Pindah Dana',
            'activeNav'  => 'catat',
            'backUrl'    => site_url('catat/keluar'),
            'units'      => $units,
            'accounts'   => $accounts,
            'activities' => $activities,
        ];

        return view('pages/catat/pindah_dana', $data);
    }

    public function simpanPindahDana()
    {
        $institutionId = $this->currentInstitutionId();
        $bookPeriodId = $this->getActiveBookPeriodId($institutionId);

        $rules = [
            'amount' => 'required',
            'from_account_id' => 'required|numeric',
            'to_account_id' => 'required|numeric',
            'unit_id' => 'required|numeric',
            'activity_id' => 'required|numeric',
            'transaction_date' => 'required',
            'notes' => 'required',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Gagal menyimpan. Pastikan semua field terisi dengan benar.');
        }

        $amountStr = $this->request->getPost('amount');
        $amount = (int) preg_replace('/[^0-9]/', '', $amountStr);

        $adminFeeInput = $this->request->getPost('admin_fee');
        if ($adminFeeInput === 'manual') {
            $adminFeeStr = $this->request->getPost('admin_fee_manual');
            $adminFee = (int) preg_replace('/[^0-9]/', '', $adminFeeStr);
        } else {
            $adminFee = (int) preg_replace('/[^0-9]/', '', $adminFeeInput);
        }

        $data = [
            'institution_id' => $institutionId,
            'book_period_id' => $bookPeriodId,
            'type' => 'pindah',
            'amount' => $amount,
            'admin_fee' => $adminFee,
            'unit_id' => $this->request->getPost('unit_id'),
            'activity_id' => $this->request->getPost('activity_id'),
            'from_account_id' => $this->request->getPost('from_account_id'),
            'to_account_id' => $this->request->getPost('to_account_id'),
            'transaction_date' => date('Y-m-d', strtotime($this->request->getPost('transaction_date'))),
            'transaction_time' => date('H:i:s'),
            'notes' => $this->request->getPost('notes'),
            'created_by' => 1,
        ];

        (new \App\Models\TransactionModel())->insert($data);

        $action = $this->request->getPost('action');
        if ($action === 'save_add') {
            return redirect()->back()->with('success', 'Pindah Dana berhasil dicatat. Silakan tambah lagi.');
        }

        return redirect()->to('beranda')->with('success', 'Pindah Dana berhasil dicatat.');
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

        (new InstitutionModel())->update((int) $institution['id'], [
            'name' => $name,
            'app_name' => $appName,
            'type' => trim((string) $this->request->getPost('type')) ?: 'Lembaga',
            'address' => trim((string) $this->request->getPost('address')),
            'email' => trim((string) $this->request->getPost('email')),
            'whatsapp' => trim((string) $this->request->getPost('whatsapp')),
            'logo' => trim((string) $this->request->getPost('logo')),
        ]);

        return redirect()->to(site_url('pengaturan/profil-lembaga/edit'))
            ->with('success', 'Profil lembaga berhasil diperbarui.');
    }

    public function masterUnitProgram(): string
    {
        $data               = [];
        $data['pageTitle']  = 'Master Unit / Program';
        $data['activeNav']  = 'beranda';
        $data['backUrl']    = site_url('pengaturan');
        $data['units']      = $this->loadUnitProgramRows();

        return view('pages/master/units', $data);
    }

    public function tambahUnitProgram(): string
    {
        return view('pages/master/form', $this->buildUnitFormData());
    }

    public function editUnitProgram(string $slug): string
    {
        $unit = (new UnitModel())->where('slug', $slug)->where('deleted_at', null)->first();

        if (! is_array($unit)) {
            throw PageNotFoundException::forPageNotFound();
        }

        return view('pages/master/form', $this->buildUnitFormData($unit, true));
    }

    public function simpanUnitProgram(): RedirectResponse
    {
        return $this->persistUnitProgram();
    }

    public function updateUnitProgram(string $slug): RedirectResponse
    {
        return $this->persistUnitProgram($slug);
    }

    public function hapusUnitProgram(string $slug): RedirectResponse
    {
        $unitModel = new UnitModel();
        $unit = $unitModel->where('slug', $slug)->first();

        if (! is_array($unit)) {
            throw PageNotFoundException::forPageNotFound();
        }

        // Cek apakah unit masih memiliki kegiatan aktif
        $activityCount = (new ActivityModel())
            ->where('unit_id', (int) $unit['id'])
            ->where('deleted_at', null)
            ->countAllResults();

        if ($activityCount > 0) {
            return redirect()->to(site_url('pengaturan/unit-program'))
                ->with('error', 'Unit <strong>' . esc($unit['name']) . '</strong> tidak bisa dihapus karena masih memiliki ' . $activityCount . ' kegiatan. Hapus kegiatan terlebih dahulu.');
        }

        // Soft delete via CI4 built-in
        $unitModel->delete((int) $unit['id']);

        return redirect()->to(site_url('pengaturan/unit-program'))
            ->with('success', 'Unit <strong>' . esc($unit['name']) . '</strong> berhasil dihapus.');
    }

    public function masterKegiatan(): string
    {
        $data = [];
        $data['pageTitle'] = 'Master Kegiatan';
        $data['activeNav'] = 'beranda';
        $data['backUrl'] = site_url('pengaturan');
        $data['activitySummaries'] = $this->loadActivityRows();

        return view('pages/master/activities', $data);
    }

    public function tambahKegiatan(): string
    {
        return view('pages/master/form', $this->buildActivityFormData());
    }

    public function editKegiatan(string $slug): string
    {
        $activity = (new ActivityModel())->where('slug', $slug)->where('deleted_at', null)->first();

        if (! is_array($activity)) {
            throw PageNotFoundException::forPageNotFound();
        }

        return view('pages/master/form', $this->buildActivityFormData($activity, true));
    }

    public function simpanKegiatan(): RedirectResponse
    {
        return $this->persistActivity();
    }

    public function updateKegiatan(string $slug): RedirectResponse
    {
        return $this->persistActivity($slug);
    }

    public function hapusKegiatan(string $slug): RedirectResponse
    {
        $model = new ActivityModel();
        $activity = $model->where('slug', $slug)->first();

        if (! is_array($activity)) {
            throw PageNotFoundException::forPageNotFound();
        }

        // TODO: Saat tabel transactions sudah aktif, tambahkan pengecekan:
        // $txCount = (new TransactionModel())->where('activity_id', $activity['id'])->where('deleted_at', null)->countAllResults();
        // if ($txCount > 0) { return redirect()->with('error', '...'); }

        $model->delete((int) $activity['id']);

        return redirect()->to(site_url('pengaturan/kegiatan'))
            ->with('success', 'Kegiatan <strong>' . esc($activity['name']) . '</strong> berhasil dihapus.');
    }

    public function masterRekeningDompet(): string
    {
        $data = [];
        $data['pageTitle'] = 'Master Rekening / Dompet';
        $data['activeNav'] = 'beranda';
        $data['backUrl'] = site_url('pengaturan');
        $data['accountSummaries'] = $this->loadAccountRows();

        return view('pages/master/accounts', $data);
    }

    public function tambahRekeningDompet(): string
    {
        return view('pages/master/form', $this->buildAccountFormData());
    }

    public function editRekeningDompet(string $slug): string
    {
        $account = (new AccountModel())->where('slug', $slug)->where('deleted_at', null)->first();

        if (! is_array($account)) {
            throw PageNotFoundException::forPageNotFound();
        }

        return view('pages/master/form', $this->buildAccountFormData($account, true));
    }

    public function simpanRekeningDompet(): RedirectResponse
    {
        return $this->persistAccount();
    }

    public function updateRekeningDompet(string $slug): RedirectResponse
    {
        return $this->persistAccount($slug);
    }

    public function hapusRekeningDompet(string $slug): RedirectResponse
    {
        $model = new AccountModel();
        $account = $model->where('slug', $slug)->first();

        if (! is_array($account)) {
            throw PageNotFoundException::forPageNotFound();
        }

        // TODO: Cek transaksi terkait saat tabel transactions sudah aktif
        // $txCount = (new TransactionModel())->groupStart()
        //     ->where('from_account_id', $account['id'])
        //     ->orWhere('to_account_id', $account['id'])
        // ->groupEnd()->where('deleted_at', null)->countAllResults();

        $model->delete((int) $account['id']);

        return redirect()->to(site_url('pengaturan/rekening-dompet'))
            ->with('success', 'Rekening <strong>' . esc($account['name']) . '</strong> berhasil dihapus.');
    }

    public function masterKategoriBiaya(): string
    {
        $data = [];
        $data['pageTitle'] = 'Master Kategori Transaksi';
        $data['activeNav'] = 'beranda';
        $data['backUrl'] = site_url('pengaturan');
        $data['transactionCategories'] = $this->loadTransactionCategoryRows();

        return view('pages/master/categories', $data);
    }

    public function tambahKategoriBiaya(): string
    {
        return view('pages/master/form', $this->buildTransactionCategoryFormData());
    }

    public function editKategoriBiaya(string $id): string
    {
        $category = (new TransactionCategoryModel())->find((int) $id);

        if (! is_array($category)) {
            throw PageNotFoundException::forPageNotFound();
        }

        return view('pages/master/form', $this->buildTransactionCategoryFormData($category, true));
    }

    public function simpanKategoriBiaya(): RedirectResponse
    {
        return $this->persistTransactionCategory();
    }

    public function updateKategoriBiaya(string $id): RedirectResponse
    {
        return $this->persistTransactionCategory($id);
    }

    public function hapusKategoriBiaya(string $id): RedirectResponse
    {
        $model = new TransactionCategoryModel();
        $item = $model->find((int) $id);
        if (! is_array($item)) { throw PageNotFoundException::forPageNotFound(); }
        $model->delete((int) $item['id']);
        return redirect()->to(site_url('pengaturan/kategori-biaya'))
            ->with('success', 'Kategori <strong>' . esc($item['name']) . '</strong> berhasil dihapus.');
    }

    public function masterPenerima(): string
    {
        $data = [];
        $data['pageTitle'] = 'Penerima';
        $data['activeNav'] = 'beranda';
        $data['backUrl'] = site_url('pengaturan');
        $data['receivers'] = $this->loadReceiverRows();

        return view('pages/master/receivers', $data);
    }

    public function tambahPenerima(): string
    {
        return view('pages/master/form', $this->buildReceiverFormData());
    }

    public function editPenerima(string $id): string
    {
        $receiver = (new ReceiverModel())->find((int) $id);

        if (! is_array($receiver)) {
            throw PageNotFoundException::forPageNotFound();
        }

        return view('pages/master/form', $this->buildReceiverFormData($receiver, true));
    }

    public function simpanPenerima(): RedirectResponse
    {
        return $this->persistReceiver();
    }

    public function updatePenerima(string $id): RedirectResponse
    {
        return $this->persistReceiver($id);
    }

    public function hapusPenerima(string $id): RedirectResponse
    {
        $model = new ReceiverModel();
        $item = $model->find((int) $id);
        if (! is_array($item)) { throw PageNotFoundException::forPageNotFound(); }
        $model->delete((int) $item['id']);
        return redirect()->to(site_url('pengaturan/penerima'))
            ->with('success', 'Penerima <strong>' . esc($item['name']) . '</strong> berhasil dihapus.');
    }

    public function masterPosLaporan(): string
    {
        $data = [];
        $data['pageTitle'] = 'Pos Laporan';
        $data['activeNav'] = 'beranda';
        $data['backUrl'] = site_url('pengaturan');
        $data['reportPositions'] = $this->loadReportPositionRows();
        $data['reportGroups'] = [
            ['name' => 'Laba Rugi'],
            ['name' => 'Neraca'],
        ];

        return view('pages/master/report_positions', $data);
    }

    public function tambahPosLaporan(): string
    {
        return view('pages/master/form', $this->buildReportPositionFormData());
    }

    public function editPosLaporan(string $id): string
    {
        $reportPosition = (new ReportPositionModel())->find((int) $id);

        if (! is_array($reportPosition)) {
            throw PageNotFoundException::forPageNotFound();
        }

        return view('pages/master/form', $this->buildReportPositionFormData($reportPosition, true));
    }

    public function simpanPosLaporan(): RedirectResponse
    {
        return $this->persistReportPosition();
    }

    public function updatePosLaporan(string $id): RedirectResponse
    {
        return $this->persistReportPosition($id);
    }

    public function hapusPosLaporan(string $id): RedirectResponse
    {
        $model = new ReportPositionModel();
        $item = $model->find((int) $id);
        if (! is_array($item)) { throw PageNotFoundException::forPageNotFound(); }
        $model->delete((int) $item['id']);
        return redirect()->to(site_url('pengaturan/pos-laporan'))
            ->with('success', 'Pos Laporan <strong>' . esc($item['name']) . '</strong> berhasil dihapus.');
    }

    public function masterTahunBuku(): string
    {
        $data = [];
        $data['pageTitle'] = 'Tahun Buku';
        $data['activeNav'] = 'beranda';
        $data['backUrl'] = site_url('pengaturan');
        $data['bookPeriods'] = $this->loadBookPeriodRows();

        return view('pages/master/book_periods', $data);
    }

    public function tambahTahunBuku(): string
    {
        return view('pages/master/form', $this->buildBookPeriodFormData());
    }

    public function editTahunBuku(string $slug): string
    {
        $bookPeriod = (new BookPeriodModel())->where('slug', $slug)->first();

        if (! is_array($bookPeriod)) {
            throw PageNotFoundException::forPageNotFound();
        }

        return view('pages/master/form', $this->buildBookPeriodFormData($bookPeriod, true));
    }

    public function simpanTahunBuku(): RedirectResponse
    {
        return $this->persistBookPeriod();
    }

    public function updateTahunBuku(string $slug): RedirectResponse
    {
        return $this->persistBookPeriod($slug);
    }

    public function hapusTahunBuku(string $slug): RedirectResponse
    {
        $model = new BookPeriodModel();
        $item = $model->where('slug', $slug)->first();
        if (! is_array($item)) { throw PageNotFoundException::forPageNotFound(); }
        $model->delete((int) $item['id']);
        return redirect()->to(site_url('pengaturan/tahun-buku'))
            ->with('success', 'Tahun Buku <strong>' . esc($item['name']) . '</strong> berhasil dihapus.');
    }

    public function masterSaldoAwal(): string
    {
        $data = [];
        $data['pageTitle'] = 'Saldo Awal';
        $data['activeNav'] = 'beranda';
        $data['backUrl'] = site_url('pengaturan');
        $data['openingBalances'] = $this->loadOpeningBalanceRows();
        $data['bookPeriods'] = $this->loadBookPeriodRows();

        return view('pages/master/opening_balances', $data);
    }

    public function tambahSaldoAwal(): string
    {
        return view('pages/master/form', $this->buildOpeningBalanceFormData());
    }

    public function editSaldoAwal(string $id): string
    {
        $openingBalance = (new OpeningBalanceModel())->find((int) $id);

        if (! is_array($openingBalance)) {
            throw PageNotFoundException::forPageNotFound();
        }

        return view('pages/master/form', $this->buildOpeningBalanceFormData($openingBalance, true));
    }

    public function simpanSaldoAwal(): RedirectResponse
    {
        return $this->persistOpeningBalance();
    }

    public function updateSaldoAwal(string $id): RedirectResponse
    {
        return $this->persistOpeningBalance($id);
    }

    public function hapusSaldoAwal(string $id): RedirectResponse
    {
        $model = new OpeningBalanceModel();
        $item = $model->find((int) $id);
        if (! is_array($item)) { throw PageNotFoundException::forPageNotFound(); }
        $model->delete((int) $item['id']);
        return redirect()->to(site_url('pengaturan/saldo-awal'))
            ->with('success', 'Saldo Awal <strong>' . esc($item['label'] ?? 'item') . '</strong> berhasil dihapus.');
    }

    public function transaksi(string $id): string
    {
        $data = [];
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
        $data = [];
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

private function prototypeData(array $options = []): array
    {
        return [];
    }

    private function rawPrototypeData(): array
    {
        return [];
    }

    private function loadUnitProgramRows(): array
    {
        $unitModel = new UnitModel();
        $activityModel = new ActivityModel();
        $units = $unitModel
            ->where('institution_id', $this->currentInstitutionId())
            ->where('deleted_at', null)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('name', 'ASC')
            ->findAll();

        foreach ($units as &$unit) {
            $unitActivities = $activityModel
                ->where('unit_id', $unit['id'])
                ->where('deleted_at', null)
                ->orderBy('sort_order', 'ASC')
                ->orderBy('name', 'ASC')
                ->findAll();

            $unit['activities'] = $unitActivities;
            $unit['income'] = 0;
            $unit['expense'] = 0;
            $unit['surplus'] = 0;
            $unit['status_label'] = (int) ($unit['is_active'] ?? 0) === 1 ? 'Aktif' : 'Nonaktif';
            $unit['note'] = 'Unit tersimpan di database. Ringkasan transaksi nyata akan mengikuti setelah modul transaksi disambungkan.';
        }
        unset($unit);

        return $units;
    }

    private function buildUnitFormData(?array $unit = null, bool $isEdit = false): array
    {
        $existingUnits = $this->loadUnitProgramRows();
        $sortOrder = $isEdit
            ? (string) ($unit['sort_order'] ?? 0)
            : (string) (count($existingUnits) + 1);

        return [
            'pageTitle' => $isEdit ? 'Edit ' . ($unit['name'] ?? 'Unit') : 'Tambah Unit / Program',
            'activeNav' => 'beranda',
            'backUrl' => site_url('pengaturan/unit-program'),
            'formMode' => $isEdit ? 'Edit Data' : 'Tambah Data',
            'formTitle' => 'Form Unit / Program',
            'formDescription' => 'Data ini sekarang tersimpan ke database dan akan dipakai sebagai fondasi konteks aktif serta relasi kegiatan.',
            'saveLabel' => $isEdit ? 'Simpan Perubahan' : 'Simpan Unit',
            'formAction' => $isEdit && isset($unit['slug'])
                ? site_url('pengaturan/unit-program/' . $unit['slug'])
                : site_url('pengaturan/unit-program'),
            'formMethod' => 'post',
            'formFields' => [
                [
                    'type' => 'text',
                    'name' => 'name',
                    'label' => 'Nama Unit / Program',
                    'value' => old('name', $unit['name'] ?? ''),
                ],
                [
                    'type' => 'text',
                    'name' => 'short_name',
                    'label' => 'Singkatan Unit',
                    'value' => old('short_name', $unit['short_name'] ?? ''),
                ],
                [
                    'type' => 'select',
                    'name' => 'status',
                    'label' => 'Status',
                    'value' => old('status', ((int) ($unit['is_active'] ?? 1) === 1 ? 'Aktif' : 'Nonaktif')),
                    'options' => ['Aktif', 'Nonaktif'],
                ],
                [
                    'type' => 'number',
                    'name' => 'sort_order',
                    'label' => 'Urutan Tampil',
                    'value' => old('sort_order', $sortOrder),
                ],
                [
                    'type' => 'textarea',
                    'name' => 'note',
                    'label' => 'Catatan Singkat',
                    'value' => old('note', $unit['note'] ?? ''),
                ],
            ],
        ];
    }

    private function persistUnitProgram(?string $slug = null): RedirectResponse
    {
        $unitModel = new UnitModel();
        $isEdit = $slug !== null;
        $current = null;

        if ($isEdit) {
            $current = $unitModel->where('slug', $slug)->where('deleted_at', null)->first();

            if (! is_array($current)) {
                throw PageNotFoundException::forPageNotFound();
            }
        }

        $name = trim((string) $this->request->getPost('name'));
        $shortName = strtoupper(trim((string) $this->request->getPost('short_name')));
        $sortOrder = (int) $this->request->getPost('sort_order');
        $status = (string) $this->request->getPost('status');
        $note = trim((string) $this->request->getPost('note'));

        if ($name === '') {
            return redirect()->back()->withInput()->with('error', 'Nama unit / program wajib diisi.');
        }

        if ($shortName === '') {
            return redirect()->back()->withInput()->with('error', 'Singkatan unit wajib diisi.');
        }

        $baseSlug = url_title($name, '-', true);
        $finalSlug = $baseSlug;
        $suffix = 2;

        while (true) {
            $conflict = $unitModel->where('slug', $finalSlug)->where('deleted_at', null)->first();

            if (! is_array($conflict) || ($isEdit && (int) $conflict['id'] === (int) $current['id'])) {
                break;
            }

            $finalSlug = $baseSlug . '-' . $suffix;
            $suffix++;
        }

        $institutionId = (int) ($this->session->get('auth_institution_id') ?? 1);

        $payload = [
            'institution_id' => $institutionId,
            'name' => $name,
            'slug' => $finalSlug,
            'short_name' => $shortName,
            'is_active' => $status === 'Aktif' ? 1 : 0,
            'sort_order' => $sortOrder > 0 ? $sortOrder : 1,
        ];

        if ($isEdit) {
            $unitModel->update((int) $current['id'], $payload);

            return redirect()->to(site_url('pengaturan/unit-program/' . $finalSlug . '/edit'))
                ->with('success', 'Unit / Program berhasil diperbarui.');
        }

        $unitModel->insert($payload);

        return redirect()->to(site_url('pengaturan/unit-program/' . $payload['slug'] . '/edit'))
            ->with('success', 'Unit / Program berhasil ditambahkan.');
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
                ['type' => 'text', 'name' => 'logo', 'label' => 'Path Logo Lembaga', 'value' => old('logo', $institution['logo'] ?? '')],
            ],
        ];
    }

    private function loadActivityRows(): array
    {
        $activityModel = new ActivityModel();
        $unitMap = [];
        foreach ($this->loadUnitProgramRows() as $unit) {
            $unitMap[(int) $unit['id']] = $unit;
        }

        $unitIds = array_keys($unitMap) ?: [0];
        $activities = $activityModel
            ->whereIn('unit_id', $unitIds)
            ->where('deleted_at', null)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('name', 'ASC')
            ->findAll();

        foreach ($activities as &$activity) {
            $unit = $unitMap[(int) $activity['unit_id']] ?? null;
            $activity['slug'] = $activity['slug'] ?? (string) $activity['id'];
            $activity['unit_name'] = $unit['name'] ?? 'Tanpa Unit';
            $activity['income'] = 0;
            $activity['expense'] = 0;
            $activity['related_balance'] = 0;
            $activity['related_accounts'] = [];
        }
        unset($activity);

        return $activities;
    }

    private function buildActivityFormData(?array $activity = null, bool $isEdit = false): array
    {
        $units = $this->loadUnitProgramRows();
        $selectedUnitId = (string) old('unit_id', $activity['unit_id'] ?? ($units[0]['id'] ?? ''));
        $sortOrder = $isEdit ? (string) ($activity['sort_order'] ?? 0) : (string) (count($this->loadActivityRows()) + 1);

        return [
            'pageTitle' => $isEdit ? 'Edit ' . ($activity['name'] ?? 'Kegiatan') : 'Tambah Kegiatan',
            'activeNav' => 'beranda',
            'backUrl' => site_url('pengaturan/kegiatan'),
            'formMode' => $isEdit ? 'Edit Data' : 'Tambah Data',
            'formTitle' => 'Form Kegiatan',
            'formDescription' => 'Kegiatan tersimpan ke database dan menjadi konteks aktif level 2 saat pencatatan.',
            'saveLabel' => $isEdit ? 'Simpan Perubahan' : 'Simpan Kegiatan',
            'formAction' => $isEdit ? site_url('pengaturan/kegiatan/' . $activity['slug']) : site_url('pengaturan/kegiatan'),
            'formMethod' => 'post',
            'formFields' => [
                ['type' => 'select', 'name' => 'unit_id', 'label' => 'Unit Induk', 'value' => $selectedUnitId, 'options' => $this->buildSelectOptions($units)],
                ['type' => 'text', 'name' => 'name', 'label' => 'Nama Kegiatan', 'value' => old('name', $activity['name'] ?? '')],
                ['type' => 'text', 'name' => 'short_name', 'label' => 'Singkatan Kegiatan', 'value' => old('short_name', $activity['short_name'] ?? '')],
                ['type' => 'select', 'name' => 'status', 'label' => 'Status', 'value' => old('status', ((int) ($activity['is_active'] ?? 1) === 1 ? 'Aktif' : 'Nonaktif')), 'options' => ['Aktif', 'Nonaktif']],
                ['type' => 'number', 'name' => 'sort_order', 'label' => 'Urutan Tampil', 'value' => old('sort_order', $sortOrder)],
            ],
        ];
    }

    private function persistActivity(?string $slug = null): RedirectResponse
    {
        $model = new ActivityModel();
        $current = null;

        if ($slug !== null) {
            $current = $model->where('slug', $slug)->where('deleted_at', null)->first();
            if (! is_array($current)) {
                throw PageNotFoundException::forPageNotFound();
            }
        }

        $unitId = (int) $this->request->getPost('unit_id');
        $name = trim((string) $this->request->getPost('name'));
        $shortName = strtoupper(trim((string) $this->request->getPost('short_name')));

        if ($unitId <= 0 || $name === '' || $shortName === '') {
            return redirect()->back()->withInput()->with('error', 'Unit induk, nama kegiatan, dan singkatan wajib diisi.');
        }

        $payload = [
            'unit_id' => $unitId,
            'name' => $name,
            'slug' => $this->uniqueSlug(new ActivityModel(), $name, $current['id'] ?? null),
            'short_name' => $shortName,
            'is_active' => $this->request->getPost('status') === 'Aktif' ? 1 : 0,
            'sort_order' => max(1, (int) $this->request->getPost('sort_order')),
        ];

        if (is_array($current)) {
            $model->update((int) $current['id'], $payload);
            return redirect()->to(site_url('pengaturan/kegiatan/' . $payload['slug'] . '/edit'))->with('success', 'Kegiatan berhasil diperbarui.');
        }

        $model->insert($payload);
        return redirect()->to(site_url('pengaturan/kegiatan/' . $payload['slug'] . '/edit'))->with('success', 'Kegiatan berhasil ditambahkan.');
    }

    private function loadAccountRows(): array
    {
        $accountModel = new AccountModel();
        $positionMap = $this->reportPositionNameMap();
        $openingByPosition = $this->openingBalanceTotalByPosition();

        $accounts = $accountModel
            ->where('institution_id', $this->currentInstitutionId())
            ->where('deleted_at', null)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('name', 'ASC')
            ->findAll();

        foreach ($accounts as &$account) {
            $positionId = (int) ($account['report_position_id'] ?? 0);
            $account['balance'] = (float) ($openingByPosition[$positionId] ?? 0);
            $account['report_position_name'] = $positionMap[$positionId] ?? 'Belum dipilih';
            $account['preview_activity'] = $account['report_position_name'];
            $account['movement_count'] = 0;
            $account['detail_url'] = site_url('pengaturan/rekening-dompet/' . $account['slug'] . '/edit');
        }
        unset($account);

        return $accounts;
    }

    private function buildAccountFormData(?array $account = null, bool $isEdit = false): array
    {
        $positions = $this->loadReportPositionRows('Neraca');
        $sortOrder = $isEdit ? (string) ($account['sort_order'] ?? 0) : (string) (count($this->loadAccountRows()) + 1);

        return [
            'pageTitle' => $isEdit ? 'Edit ' . ($account['name'] ?? 'Rekening') : 'Tambah Rekening / Dompet',
            'activeNav' => 'beranda',
            'backUrl' => site_url('pengaturan/rekening-dompet'),
            'formMode' => $isEdit ? 'Edit Data' : 'Tambah Data',
            'formTitle' => 'Form Rekening / Dompet',
            'formDescription' => 'Master rekening sekarang langsung tersimpan ke database dan menyimpan pos laporan terkait.',
            'saveLabel' => $isEdit ? 'Simpan Perubahan' : 'Simpan Rekening',
            'formAction' => $isEdit ? site_url('pengaturan/rekening-dompet/' . $account['slug']) : site_url('pengaturan/rekening-dompet'),
            'formMethod' => 'post',
            'formFields' => [
                ['type' => 'text', 'name' => 'name', 'label' => 'Nama Rekening / Dompet', 'value' => old('name', $account['name'] ?? '')],
                ['type' => 'select', 'name' => 'kind', 'label' => 'Jenis Penyimpanan Dana', 'value' => old('kind', $account['kind'] ?? 'Rekening'), 'options' => ['Rekening', 'Dompet Digital', 'Kas Tunai']],
                ['type' => 'text', 'name' => 'mark', 'label' => 'Label / Singkatan', 'value' => old('mark', $account['mark'] ?? '')],
                ['type' => 'text', 'name' => 'account_number', 'label' => 'Nomor Rekening', 'value' => old('account_number', $account['account_number'] ?? '')],
                ['type' => 'select', 'name' => 'report_position_id', 'label' => 'Pos Laporan Terkait', 'value' => (string) old('report_position_id', $account['report_position_id'] ?? ($positions[0]['id'] ?? '')), 'options' => $this->buildSelectOptions($positions)],
                ['type' => 'file', 'name' => 'logo_file', 'label' => 'Logo Rekening (Opsional)', 'value' => $account['logo_asset'] ?? ''],
                ['type' => 'textarea', 'name' => 'note', 'label' => 'Catatan Penggunaan', 'value' => old('note', $account['note'] ?? '')],
                ['type' => 'select', 'name' => 'status', 'label' => 'Status', 'value' => old('status', ((int) ($account['is_active'] ?? 1) === 1 ? 'Aktif' : 'Nonaktif')), 'options' => ['Aktif', 'Nonaktif']],
                ['type' => 'number', 'name' => 'sort_order', 'label' => 'Urutan Tampil', 'value' => old('sort_order', $sortOrder)],
            ],
        ];
    }

    private function persistAccount(?string $slug = null): RedirectResponse
    {
        $model = new AccountModel();
        $current = null;

        if ($slug !== null) {
            $current = $model->where('slug', $slug)->where('deleted_at', null)->first();
            if (! is_array($current)) {
                throw PageNotFoundException::forPageNotFound();
            }
        }

        $name = trim((string) $this->request->getPost('name'));
        $mark = strtoupper(trim((string) $this->request->getPost('mark')));
        if ($name === '' || $mark === '') {
            return redirect()->back()->withInput()->with('error', 'Nama rekening dan label singkatan wajib diisi.');
        }

        // Handle logo upload
        $logoAsset = $current['logo_asset'] ?? ''; // keep existing
        $logoFile = $this->request->getFile('logo_file');
        if ($logoFile !== null && $logoFile->isValid() && ! $logoFile->hasMoved()) {
            $uploadDir = FCPATH . 'uploads/accounts';
            if (! is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $newName = $logoFile->getRandomName();
            $logoFile->move($uploadDir, $newName);
            $logoAsset = 'uploads/accounts/' . $newName;

            // Hapus logo lama jika ada
            $oldLogo = $current['logo_asset'] ?? '';
            if ($oldLogo !== '' && is_file(FCPATH . $oldLogo)) {
                @unlink(FCPATH . $oldLogo);
            }
        }

        $payload = [
            'institution_id' => $this->currentInstitutionId(),
            'name' => $name,
            'slug' => $this->uniqueSlug(new AccountModel(), $name, $current['id'] ?? null),
            'kind' => (string) $this->request->getPost('kind'),
            'mark' => $mark,
            'account_number' => trim((string) $this->request->getPost('account_number')),
            'logo_asset' => $logoAsset,
            'note' => trim((string) $this->request->getPost('note')),
            'report_position_id' => (int) $this->request->getPost('report_position_id') ?: null,
            'is_active' => $this->request->getPost('status') === 'Aktif' ? 1 : 0,
            'sort_order' => max(1, (int) $this->request->getPost('sort_order')),
        ];

        if (is_array($current)) {
            $model->update((int) $current['id'], $payload);
            return redirect()->to(site_url('pengaturan/rekening-dompet/' . $payload['slug'] . '/edit'))->with('success', 'Rekening / dompet berhasil diperbarui.');
        }

        $model->insert($payload);
        return redirect()->to(site_url('pengaturan/rekening-dompet/' . $payload['slug'] . '/edit'))->with('success', 'Rekening / dompet berhasil ditambahkan.');
    }

    private function loadTransactionCategoryRows(): array
    {
        $positionMap = $this->reportPositionNameMap();
        $rows = (new TransactionCategoryModel())
            ->where('institution_id', $this->currentInstitutionId())
            ->where('deleted_at', null)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('name', 'ASC')
            ->findAll();

        foreach ($rows as &$row) {
            $row['slug'] = (string) $row['id'];
            $row['type'] = $row['kind'];
            $row['order'] = (int) $row['sort_order'];
            $row['report_position_name'] = $positionMap[(int) ($row['report_position_id'] ?? 0)] ?? 'Belum dipilih';
            $row['note'] = $row['chip_label'] ?: 'Kategori transaksi untuk ' . strtolower($row['kind']) . '.';
        }
        unset($row);

        return $rows;
    }

    private function buildTransactionCategoryFormData(?array $category = null, bool $isEdit = false): array
    {
        $positions = $this->loadReportPositionRows('Laba Rugi');
        $sortOrder = $isEdit ? (string) ($category['sort_order'] ?? 0) : (string) (count($this->loadTransactionCategoryRows()) + 1);

        return [
            'pageTitle' => $isEdit ? 'Edit ' . ($category['name'] ?? 'Kategori') : 'Tambah Kategori Transaksi',
            'activeNav' => 'beranda',
            'backUrl' => site_url('pengaturan/kategori-biaya'),
            'formMode' => $isEdit ? 'Edit Data' : 'Tambah Data',
            'formTitle' => 'Form Kategori Transaksi',
            'formDescription' => 'Kategori transaksi langsung menentukan pilihan di form uang masuk atau uang keluar.',
            'saveLabel' => $isEdit ? 'Simpan Perubahan' : 'Simpan Kategori',
            'formAction' => $isEdit ? site_url('pengaturan/kategori-biaya/' . $category['id']) : site_url('pengaturan/kategori-biaya'),
            'formMethod' => 'post',
            'formFields' => [
                ['type' => 'text', 'name' => 'name', 'label' => 'Nama Kategori', 'value' => old('name', $category['name'] ?? '')],
                ['type' => 'select', 'name' => 'kind', 'label' => 'Jenis Transaksi', 'value' => old('kind', $category['kind'] ?? 'Keluar'), 'options' => ['Masuk', 'Keluar']],
                ['type' => 'select', 'name' => 'report_position_id', 'label' => 'Pos Laporan Terkait', 'value' => (string) old('report_position_id', $category['report_position_id'] ?? ($positions[0]['id'] ?? '')), 'options' => $this->buildSelectOptions($positions)],
                ['type' => 'text', 'name' => 'chip_label', 'label' => 'Label Chip Cepat', 'value' => old('chip_label', $category['chip_label'] ?? '')],
                ['type' => 'select', 'name' => 'is_quick', 'label' => 'Muncul sebagai kategori cepat', 'value' => old('is_quick', ((int) ($category['is_quick'] ?? 0) === 1 ? 'Ya' : 'Tidak')), 'options' => ['Ya', 'Tidak']],
                ['type' => 'select', 'name' => 'status', 'label' => 'Status', 'value' => old('status', ((int) ($category['is_active'] ?? 1) === 1 ? 'Aktif' : 'Nonaktif')), 'options' => ['Aktif', 'Nonaktif']],
                ['type' => 'number', 'name' => 'sort_order', 'label' => 'Urutan Tampil', 'value' => old('sort_order', $sortOrder)],
            ],
        ];
    }

    private function persistTransactionCategory(?string $id = null): RedirectResponse
    {
        $model = new TransactionCategoryModel();
        $current = $id !== null ? $model->find((int) $id) : null;

        if ($id !== null && ! is_array($current)) {
            throw PageNotFoundException::forPageNotFound();
        }

        $name = trim((string) $this->request->getPost('name'));
        if ($name === '') {
            return redirect()->back()->withInput()->with('error', 'Nama kategori wajib diisi.');
        }

        $payload = [
            'institution_id' => $this->currentInstitutionId(),
            'name' => $name,
            'kind' => (string) $this->request->getPost('kind'),
            'report_position_id' => (int) $this->request->getPost('report_position_id') ?: null,
            'is_quick' => $this->request->getPost('is_quick') === 'Ya' ? 1 : 0,
            'chip_label' => trim((string) $this->request->getPost('chip_label')),
            'is_active' => $this->request->getPost('status') === 'Aktif' ? 1 : 0,
            'sort_order' => max(1, (int) $this->request->getPost('sort_order')),
        ];

        if (is_array($current)) {
            $model->update((int) $current['id'], $payload);
            return redirect()->to(site_url('pengaturan/kategori-biaya/' . $current['id'] . '/edit'))->with('success', 'Kategori transaksi berhasil diperbarui.');
        }

        $model->insert($payload);
        $newId = (string) $model->getInsertID();
        return redirect()->to(site_url('pengaturan/kategori-biaya/' . $newId . '/edit'))->with('success', 'Kategori transaksi berhasil ditambahkan.');
    }

    private function loadReceiverRows(): array
    {
        $rows = (new ReceiverModel())
            ->where('institution_id', $this->currentInstitutionId())
            ->where('deleted_at', null)
            ->orderBy('name', 'ASC')
            ->findAll();

        foreach ($rows as &$row) {
            $row['slug'] = (string) $row['id'];
            $row['note'] = $row['notes'] ?? '';
        }
        unset($row);

        return $rows;
    }

    private function buildReceiverFormData(?array $receiver = null, bool $isEdit = false): array
    {
        return [
            'pageTitle' => $isEdit ? 'Edit ' . ($receiver['name'] ?? 'Penerima') : 'Tambah Penerima',
            'activeNav' => 'beranda',
            'backUrl' => site_url('pengaturan/penerima'),
            'formMode' => $isEdit ? 'Edit Data' : 'Tambah Data',
            'formTitle' => 'Form Penerima',
            'formDescription' => 'Penerima dipakai sebagai daftar kontak, vendor, dan pihak terkait saat transaksi keluar dicatat.',
            'saveLabel' => $isEdit ? 'Simpan Perubahan' : 'Simpan Penerima',
            'formAction' => $isEdit ? site_url('pengaturan/penerima/' . $receiver['id']) : site_url('pengaturan/penerima'),
            'formMethod' => 'post',
            'formFields' => [
                ['type' => 'text', 'name' => 'name', 'label' => 'Nama Penerima / Kontak', 'value' => old('name', $receiver['name'] ?? '')],
                ['type' => 'select', 'name' => 'type', 'label' => 'Jenis Kontak', 'value' => old('type', $receiver['type'] ?? 'Vendor'), 'options' => ['Tim Internal', 'Vendor', 'Klien', 'Lainnya']],
                ['type' => 'text', 'name' => 'nik', 'label' => 'NIK (Opsional)', 'value' => old('nik', $receiver['nik'] ?? '')],
                ['type' => 'text', 'name' => 'npwp', 'label' => 'NPWP (Opsional)', 'value' => old('npwp', $receiver['npwp'] ?? '')],
                ['type' => 'text', 'name' => 'bank_account', 'label' => 'Informasi Rekening (Opsional)', 'value' => old('bank_account', $receiver['bank_account'] ?? '')],
                ['type' => 'textarea', 'name' => 'notes', 'label' => 'Catatan', 'value' => old('notes', $receiver['notes'] ?? '')],
            ],
        ];
    }

    private function persistReceiver(?string $id = null): RedirectResponse
    {
        $model = new ReceiverModel();
        $current = $id !== null ? $model->find((int) $id) : null;

        if ($id !== null && ! is_array($current)) {
            throw PageNotFoundException::forPageNotFound();
        }

        $name = trim((string) $this->request->getPost('name'));
        if ($name === '') {
            return redirect()->back()->withInput()->with('error', 'Nama penerima wajib diisi.');
        }

        $payload = [
            'institution_id' => $this->currentInstitutionId(),
            'name' => $name,
            'type' => (string) $this->request->getPost('type'),
            'nik' => trim((string) $this->request->getPost('nik')),
            'npwp' => trim((string) $this->request->getPost('npwp')),
            'bank_account' => trim((string) $this->request->getPost('bank_account')),
            'notes' => trim((string) $this->request->getPost('notes')),
        ];

        if (is_array($current)) {
            $model->update((int) $current['id'], $payload);
            return redirect()->to(site_url('pengaturan/penerima/' . $current['id'] . '/edit'))->with('success', 'Penerima berhasil diperbarui.');
        }

        $model->insert($payload);
        $newId = (string) $model->getInsertID();
        return redirect()->to(site_url('pengaturan/penerima/' . $newId . '/edit'))->with('success', 'Penerima berhasil ditambahkan.');
    }

    private function loadReportPositionRows(?string $group = null): array
    {
        $model = new ReportPositionModel();
        $builder = $model
            ->where('institution_id', $this->currentInstitutionId())
            ->orderBy('sort_order', 'ASC')
            ->orderBy('name', 'ASC');
        if ($group !== null) {
            $builder = $builder->where('group', $group);
        }
        $rows = $builder->findAll();

        foreach ($rows as &$row) {
            $row['slug'] = (string) $row['id'];
            $row['code'] = 'POS-' . str_pad((string) $row['id'], 3, '0', STR_PAD_LEFT);
            $row['note'] = 'Pos laporan untuk kelompok ' . strtolower($row['group']) . '.';
            $row['normal_balance'] = $this->normalBalanceForKind($row['kind']);
        }
        unset($row);

        return $rows;
    }

    private function buildReportPositionFormData(?array $position = null, bool $isEdit = false): array
    {
        return [
            'pageTitle' => $isEdit ? 'Edit ' . ($position['name'] ?? 'Pos') : 'Tambah Pos Laporan',
            'activeNav' => 'beranda',
            'backUrl' => site_url('pengaturan/pos-laporan'),
            'formMode' => $isEdit ? 'Edit Data' : 'Tambah Data',
            'formTitle' => 'Form Pos Laporan',
            'formDescription' => 'Pos laporan menjadi fondasi akhir untuk laporan tahunan, tetapi tetap dikelola sederhana dari sekarang.',
            'saveLabel' => $isEdit ? 'Simpan Perubahan' : 'Simpan Pos',
            'formAction' => $isEdit ? site_url('pengaturan/pos-laporan/' . $position['id']) : site_url('pengaturan/pos-laporan'),
            'formMethod' => 'post',
            'formFields' => [
                ['type' => 'text', 'name' => 'name', 'label' => 'Nama Pos Laporan', 'value' => old('name', $position['name'] ?? '')],
                ['type' => 'select', 'name' => 'group', 'label' => 'Kelompok Laporan', 'value' => old('group', $position['group'] ?? 'Laba Rugi'), 'options' => ['Laba Rugi', 'Neraca']],
                ['type' => 'select', 'name' => 'kind', 'label' => 'Jenis Pos', 'value' => old('kind', $position['kind'] ?? 'Beban'), 'options' => ['Pendapatan', 'Beban', 'Aset', 'Kewajiban', 'Modal']],
                ['type' => 'number', 'name' => 'sort_order', 'label' => 'Urutan Tampil', 'value' => old('sort_order', $position['sort_order'] ?? (count($this->loadReportPositionRows()) + 1))],
            ],
        ];
    }

    private function persistReportPosition(?string $id = null): RedirectResponse
    {
        $model = new ReportPositionModel();
        $current = $id !== null ? $model->find((int) $id) : null;

        if ($id !== null && ! is_array($current)) {
            throw PageNotFoundException::forPageNotFound();
        }

        $name = trim((string) $this->request->getPost('name'));
        if ($name === '') {
            return redirect()->back()->withInput()->with('error', 'Nama pos laporan wajib diisi.');
        }

        $payload = [
            'institution_id' => $this->currentInstitutionId(),
            'name' => $name,
            'group' => (string) $this->request->getPost('group'),
            'kind' => (string) $this->request->getPost('kind'),
            'sort_order' => max(1, (int) $this->request->getPost('sort_order')),
        ];

        if (is_array($current)) {
            $model->update((int) $current['id'], $payload);
            return redirect()->to(site_url('pengaturan/pos-laporan/' . $current['id'] . '/edit'))->with('success', 'Pos laporan berhasil diperbarui.');
        }

        $model->insert($payload);
        $newId = (string) $model->getInsertID();
        return redirect()->to(site_url('pengaturan/pos-laporan/' . $newId . '/edit'))->with('success', 'Pos laporan berhasil ditambahkan.');
    }

    private function getActiveBookPeriodId(int $institutionId): ?int
    {
        $activePeriod = (new \App\Models\BookPeriodModel())
            ->where('institution_id', $institutionId)
            ->where('is_active', true)
            ->first();
        return $activePeriod['id'] ?? null;
    }

    private function loadBookPeriodRows(): array
    {
        $rows = (new BookPeriodModel())
            ->where('institution_id', $this->currentInstitutionId())
            ->orderBy('start_date', 'DESC')
            ->findAll();

        foreach ($rows as &$row) {
            $row['start'] = $row['start_date'];
            $row['end'] = $row['end_date'];
            $row['status'] = (int) $row['is_locked'] === 1 ? 'Ditutup' : ((int) $row['is_active'] === 1 ? 'Aktif' : 'Draft');
            $row['note'] = (int) $row['is_locked'] === 1 ? 'Periode sudah ditutup.' : 'Periode buku aktif untuk fondasi laporan.';
        }
        unset($row);

        return $rows;
    }

    private function buildBookPeriodFormData(?array $period = null, bool $isEdit = false): array
    {
        return [
            'pageTitle' => $isEdit ? 'Edit ' . ($period['name'] ?? 'Tahun Buku') : 'Tambah Tahun Buku',
            'activeNav' => 'beranda',
            'backUrl' => site_url('pengaturan/tahun-buku'),
            'formMode' => $isEdit ? 'Edit Data' : 'Tambah Data',
            'formTitle' => 'Form Tahun Buku',
            'formDescription' => 'Tahun buku mengikat saldo awal dan nanti akan menjadi pintu masuk filter laporan.',
            'saveLabel' => $isEdit ? 'Simpan Perubahan' : 'Simpan Tahun Buku',
            'formAction' => $isEdit ? site_url('pengaturan/tahun-buku/' . $period['slug']) : site_url('pengaturan/tahun-buku'),
            'formMethod' => 'post',
            'formFields' => [
                ['type' => 'text', 'name' => 'name', 'label' => 'Nama Tahun Buku', 'value' => old('name', $period['name'] ?? '')],
                ['type' => 'date', 'name' => 'start_date', 'label' => 'Tanggal Mulai', 'value' => old('start_date', $period['start_date'] ?? '')],
                ['type' => 'date', 'name' => 'end_date', 'label' => 'Tanggal Selesai', 'value' => old('end_date', $period['end_date'] ?? '')],
                ['type' => 'select', 'name' => 'status', 'label' => 'Status', 'value' => old('status', ((int) ($period['is_locked'] ?? 0) === 1 ? 'Ditutup' : ((int) ($period['is_active'] ?? 0) === 1 ? 'Aktif' : 'Draft'))), 'options' => ['Draft', 'Aktif', 'Ditutup']],
            ],
        ];
    }

    private function persistBookPeriod(?string $slug = null): RedirectResponse
    {
        $model = new BookPeriodModel();
        $current = $slug !== null ? $model->where('slug', $slug)->first() : null;

        if ($slug !== null && ! is_array($current)) {
            throw PageNotFoundException::forPageNotFound();
        }

        $name = trim((string) $this->request->getPost('name'));
        $startDate = (string) $this->request->getPost('start_date');
        $endDate = (string) $this->request->getPost('end_date');
        $status = (string) $this->request->getPost('status');

        if ($name === '' || $startDate === '' || $endDate === '') {
            return redirect()->back()->withInput()->with('error', 'Nama tahun buku dan rentang tanggal wajib diisi.');
        }

        $payload = [
            'institution_id' => $this->currentInstitutionId(),
            'name' => $name,
            'slug' => $this->uniqueSlug(new BookPeriodModel(), $name, $current['id'] ?? null),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'is_active' => $status === 'Aktif' ? 1 : 0,
            'is_locked' => $status === 'Ditutup' ? 1 : 0,
        ];

        if ($status === 'Aktif') {
            $model->where('institution_id', $this->currentInstitutionId())->set(['is_active' => 0])->update();
        }

        if (is_array($current)) {
            $model->update((int) $current['id'], $payload);
            return redirect()->to(site_url('pengaturan/tahun-buku/' . $payload['slug'] . '/edit'))->with('success', 'Tahun buku berhasil diperbarui.');
        }

        $model->insert($payload);
        return redirect()->to(site_url('pengaturan/tahun-buku/' . $payload['slug'] . '/edit'))->with('success', 'Tahun buku berhasil ditambahkan.');
    }

    private function loadOpeningBalanceRows(): array
    {
        $periodMap = [];
        foreach ($this->loadBookPeriodRows() as $period) {
            $periodMap[(int) $period['id']] = $period;
        }
        $positionMap = $this->reportPositionNameMap();
        $accountMap = [];
        foreach ($this->loadAccountRows() as $acc) {
            $accountMap[(int) $acc['id']] = $acc['name'] . ' (' . $acc['mark'] . ')';
        }

        $rows = (new OpeningBalanceModel())
            ->where('institution_id', $this->currentInstitutionId())
            ->where('deleted_at', null)
            ->orderBy('book_period_id', 'DESC')
            ->findAll();

        foreach ($rows as &$row) {
            $row['slug'] = (string) $row['id'];
            $row['label'] = $row['source_label'];
            $row['book_period_name'] = $periodMap[(int) $row['book_period_id']]['name'] ?? 'Tanpa Periode';
            $row['report_position_name'] = $positionMap[(int) $row['report_position_id']] ?? 'Belum dipilih';
            $row['account_name'] = !empty($row['account_id']) ? ($accountMap[(int) $row['account_id']] ?? '') : '';
            $row['type'] = !empty($row['account_name']) ? 'Rekening' : 'Pos Neraca';
            $row['note'] = 'Saldo awal untuk ' . strtolower($row['report_position_name']) . '.';
        }
        unset($row);

        return $rows;
    }

    private function buildOpeningBalanceFormData(?array $balance = null, bool $isEdit = false): array
    {
        $periods = $this->loadBookPeriodRows();
        $positions = $this->loadReportPositionRows();
        $accounts = $this->loadAccountRows();

        // Build account options: first = "none", then real accounts
        $accountOptions = [['value' => '', 'label' => '— Tidak terkait rekening —']];
        foreach ($accounts as $acc) {
            $accountOptions[] = ['value' => (string) $acc['id'], 'label' => $acc['name'] . ' (' . $acc['mark'] . ')'];
        }

        return [
            'pageTitle' => $isEdit ? 'Edit ' . ($balance['source_label'] ?? 'Saldo Awal') : 'Tambah Saldo Awal',
            'activeNav' => 'beranda',
            'backUrl' => site_url('pengaturan/saldo-awal'),
            'formMode' => $isEdit ? 'Edit Data' : 'Tambah Data',
            'formTitle' => 'Form Saldo Awal',
            'formDescription' => 'Saldo awal menyimpan posisi awal tiap rekening atau pos neraca sebelum transaksi berjalan mulai dicatat.',
            'saveLabel' => $isEdit ? 'Simpan Perubahan' : 'Simpan Saldo Awal',
            'formAction' => $isEdit ? site_url('pengaturan/saldo-awal/' . $balance['id']) : site_url('pengaturan/saldo-awal'),
            'formMethod' => 'post',
            'formFields' => [
                ['type' => 'select', 'name' => 'book_period_id', 'label' => 'Tahun Buku', 'value' => (string) old('book_period_id', $balance['book_period_id'] ?? ($periods[0]['id'] ?? '')), 'options' => $this->buildSelectOptions($periods)],
                ['type' => 'select', 'name' => 'account_id', 'label' => 'Rekening / Dompet (Opsional)', 'value' => (string) old('account_id', $balance['account_id'] ?? ''), 'options' => $accountOptions],
                ['type' => 'text', 'name' => 'source_label', 'label' => 'Pos / Sumber Saldo', 'value' => old('source_label', $balance['source_label'] ?? '')],
                ['type' => 'select', 'name' => 'report_position_id', 'label' => 'Pos Laporan Terkait', 'value' => (string) old('report_position_id', $balance['report_position_id'] ?? ($positions[0]['id'] ?? '')), 'options' => $this->buildSelectOptions($positions)],
                ['type' => 'text', 'name' => 'amount', 'label' => 'Nilai Saldo Awal', 'value' => old('amount', isset($balance['amount']) ? rupiah((float) $balance['amount']) : 'Rp 0')],
            ],
        ];
    }

    private function persistOpeningBalance(?string $id = null): RedirectResponse
    {
        $model = new OpeningBalanceModel();
        $current = $id !== null ? $model->find((int) $id) : null;

        if ($id !== null && ! is_array($current)) {
            throw PageNotFoundException::forPageNotFound();
        }

        $sourceLabel = trim((string) $this->request->getPost('source_label'));
        if ($sourceLabel === '') {
            return redirect()->back()->withInput()->with('error', 'Pos / sumber saldo wajib diisi.');
        }

        $accountId = $this->request->getPost('account_id');

        $payload = [
            'institution_id' => $this->currentInstitutionId(),
            'account_id' => ($accountId !== '' && $accountId !== null) ? (int) $accountId : null,
            'book_period_id' => (int) $this->request->getPost('book_period_id'),
            'report_position_id' => (int) $this->request->getPost('report_position_id'),
            'source_label' => $sourceLabel,
            'amount' => $this->normalizeMoney((string) $this->request->getPost('amount')),
        ];

        if (is_array($current)) {
            $model->update((int) $current['id'], $payload);
            return redirect()->to(site_url('pengaturan/saldo-awal/' . $current['id'] . '/edit'))->with('success', 'Saldo awal berhasil diperbarui.');
        }

        $model->insert($payload);
        $newId = (string) $model->getInsertID();
        return redirect()->to(site_url('pengaturan/saldo-awal/' . $newId . '/edit'))->with('success', 'Saldo awal berhasil ditambahkan.');
    }

    private function currentInstitutionId(): int
    {
        return (int) ($this->session->get('auth_institution_id') ?? 1);
    }

    private function currentInstitution(): array
    {
        $institution = (new InstitutionModel())->find($this->currentInstitutionId());

        if (is_array($institution)) {
            return $institution;
        }

        return [
            'id' => 1,
            'name' => 'PT Maju Pendidikan Bangsa',
            'app_name' => 'Arus',
            'type' => 'Lembaga',
            'email' => '',
            'whatsapp' => '',
            'address' => '',
            'logo' => '',
        ];
    }

    private function buildSelectOptions(array $rows, string $valueKey = 'id', string $labelKey = 'name'): array
    {
        return array_map(
            static fn(array $row): array => [
                'value' => (string) ($row[$valueKey] ?? ''),
                'label' => (string) ($row[$labelKey] ?? ''),
            ],
            $rows
        );
    }

    private function uniqueSlug(object $model, string $name, ?int $ignoreId = null): string
    {
        $baseSlug = url_title($name, '-', true);
        $finalSlug = $baseSlug;
        $suffix = 2;

        while (true) {
            $conflict = $model->where('slug', $finalSlug)->first();
            if (! is_array($conflict) || ($ignoreId !== null && (int) $conflict['id'] === $ignoreId)) {
                return $finalSlug;
            }

            $finalSlug = $baseSlug . '-' . $suffix;
            $suffix++;
        }
    }

    private function reportPositionNameMap(): array
    {
        $map = [];
        foreach ((new ReportPositionModel())->where('institution_id', $this->currentInstitutionId())->findAll() as $position) {
            $map[(int) $position['id']] = $position['name'];
        }

        return $map;
    }

    private function openingBalanceTotalByPosition(): array
    {
        $totals = [];
        foreach ((new OpeningBalanceModel())->findAll() as $row) {
            $positionId = (int) $row['report_position_id'];
            $totals[$positionId] = ($totals[$positionId] ?? 0) + (float) $row['amount'];
        }

        return $totals;
    }

    private function normalBalanceForKind(string $kind): string
    {
        return in_array($kind, ['Pendapatan', 'Kewajiban', 'Modal'], true) ? 'Kredit' : 'Debit';
    }

    private function normalizeMoney(string $raw): float
    {
        $normalized = preg_replace('/[^0-9,.-]/', '', $raw) ?? '0';
        $normalized = str_replace('.', '', $normalized);
        $normalized = str_replace(',', '.', $normalized);

        return (float) $normalized;
    }

}
