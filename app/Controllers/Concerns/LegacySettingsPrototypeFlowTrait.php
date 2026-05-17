<?php

namespace App\Controllers\Concerns;

trait LegacySettingsPrototypeFlowTrait
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

        foreach ($units as &$u) {
            $uInc = (float) ($db->table('transactions')->where('unit_id', $u['id'])->where('type', 'masuk')->where('deleted_at', null)->selectSum('amount')->get()->getRow()->amount ?? 0);
            $uExp = (float) ($db->table('transactions')->where('unit_id', $u['id'])->where('type', 'keluar')->where('deleted_at', null)->select('SUM(amount + admin_fee) as total')->get()->getRow()->total ?? 0);
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

        return view('pages/home', [
            'appName' => $institution['app_name'] ?? 'Arusdana',
            'institutionName' => $institution['name'],
            'pageTitle' => 'Beranda',
            'activeNav' => 'beranda',
            'activeContext' => $activeContext,
            'summary' => [
                'balance' => $balance,
                'income' => $incSum,
                'expense' => $expSum,
                'surplus' => $surplus,
            ],
            'homeTransactions' => $homeTransactions,
            'units' => $units,
            'settingsShortcuts' => $settingsShortcuts,
        ]);
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
                'href' => route_query('catat/keluar/biaya', ['kategori' => $c['name']]),
            ];
        }

        return view('pages/catat/index', [
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
            ],
        ]);
    }

    public function catatMasuk(): string
    {
        $institutionId = $this->currentInstitutionId();
        $db = \Config\Database::connect();

        return view('pages/catat/masuk', [
            'pageTitle' => 'Uang Masuk',
            'activeNav' => 'catat',
            'backUrl' => site_url('catat'),
            'units' => $this->loadUnitProgramRows(),
            'activitySummaries' => $db->table('activities')->where('institution_id', $institutionId)->where('deleted_at', null)->get()->getResultArray(),
            'accounts' => $db->table('accounts')->where('institution_id', $institutionId)->where('deleted_at', null)->get()->getResultArray(),
            'incomeCategories' => $db->table('transaction_categories')->where('institution_id', $institutionId)->where('type', 'masuk')->where('deleted_at', null)->get()->getResultArray(),
            'selectedIncomeCategory' => '',
            'activeContext' => [
                'unit_slug' => $this->request->getGet('unit') ?? '',
                'activity_slug' => $this->request->getGet('kegiatan') ?? '',
                'default_income_account' => '',
            ],
        ]);
    }

    public function simpanMasuk()
    {
        $institutionId = $this->currentInstitutionId();
        $bookPeriodId = $this->getActiveBookPeriodId($institutionId);

        $rules = [
            'unit_id' => 'required|is_natural_no_zero',
            'activity_id' => 'required|is_natural_no_zero',
            'amount' => 'required',
            'category_id' => 'required|is_natural_no_zero',
            'to_account_id' => 'required|is_natural_no_zero',
            'transaction_date' => 'required|valid_date[Y-m-d]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Semua kolom wajib diisi dengan benar.');
        }

        $amount = (int) preg_replace('/[^0-9]/', '', (string) $this->request->getPost('amount'));

        (new \App\Models\TransactionModel())->insert([
            'institution_id' => $institutionId,
            'book_period_id' => $bookPeriodId,
            'type' => 'masuk',
            'amount' => $amount,
            'admin_fee' => 0,
            'unit_id' => $this->request->getPost('unit_id'),
            'activity_id' => $this->request->getPost('activity_id'),
            'category_id' => $this->request->getPost('category_id'),
            'from_account_id' => null,
            'to_account_id' => $this->request->getPost('to_account_id'),
            'receiver_id' => null,
            'transaction_date' => $this->request->getPost('transaction_date'),
            'transaction_time' => date('H:i:s'),
            'notes' => $this->request->getPost('notes'),
            'created_by' => $this->currentUser()['id'],
        ]);

        if ($this->request->getPost('action') === 'save_add') {
            return redirect()->back()->with('success', 'Uang masuk berhasil dicatat. Silakan tambah lagi.');
        }

        return redirect()->to('catat')->with('success', 'Uang masuk berhasil dicatat.');
    }

    public function catatKeluar(): string
    {
        $institutionId = $this->currentInstitutionId();
        $db = \Config\Database::connect();

        return view('pages/catat/keluar', [
            'pageTitle' => 'Uang Keluar',
            'activeNav' => 'catat',
            'backUrl' => site_url('catat'),
            'units' => $this->loadUnitProgramRows(),
            'activitySummaries' => $db->table('activities')->where('institution_id', $institutionId)->where('deleted_at', null)->get()->getResultArray(),
            'activeContext' => [
                'unit_slug' => $this->request->getGet('unit') ?? '',
                'activity_slug' => $this->request->getGet('kegiatan') ?? '',
                'query' => [
                    'unit' => $this->request->getGet('unit') ?? '',
                    'kegiatan' => $this->request->getGet('kegiatan') ?? '',
                ],
            ],
        ]);
    }

    public function catatBiaya(): string
    {
        $institutionId = $this->currentInstitutionId();
        $db = \Config\Database::connect();

        return view('pages/catat/biaya', [
            'pageTitle' => 'Catat Biaya',
            'activeNav' => 'catat',
            'backUrl' => site_url('catat/keluar'),
            'units' => $this->loadUnitProgramRows(),
            'activitySummaries' => $db->table('activities')->where('institution_id', $institutionId)->where('deleted_at', null)->get()->getResultArray(),
            'accounts' => $db->table('accounts')->where('institution_id', $institutionId)->where('deleted_at', null)->get()->getResultArray(),
            'expenseCategories' => $db->table('transaction_categories')->where('institution_id', $institutionId)->where('type', 'keluar')->where('deleted_at', null)->get()->getResultArray(),
            'activeContext' => [
                'unit_slug' => $this->request->getGet('unit') ?? '',
                'activity_slug' => $this->request->getGet('kegiatan') ?? '',
                'default_expense_account' => '',
            ],
        ]);
    }

    public function simpanBiaya()
    {
        $institutionId = $this->currentInstitutionId();
        $bookPeriodId = $this->getActiveBookPeriodId($institutionId);

        $rules = [
            'unit_id' => 'required|is_natural_no_zero',
            'activity_id' => 'required|is_natural_no_zero',
            'amount' => 'required',
            'category_id' => 'required|is_natural_no_zero',
            'from_account_id' => 'required|is_natural_no_zero',
            'transaction_date' => 'required|valid_date[Y-m-d]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Semua kolom wajib diisi dengan benar.');
        }

        $amount = (int) preg_replace('/[^0-9]/', '', (string) $this->request->getPost('amount'));
        $adminFeePreset = $this->request->getPost('admin_fee_preset');
        $adminFee = $adminFeePreset === 'manual'
            ? (int) preg_replace('/[^0-9]/', '', (string) $this->request->getPost('admin_fee_custom'))
            : (int) $adminFeePreset;

        (new \App\Models\TransactionModel())->insert([
            'institution_id' => $institutionId,
            'book_period_id' => $bookPeriodId,
            'type' => 'keluar',
            'amount' => $amount,
            'admin_fee' => $adminFee,
            'unit_id' => $this->request->getPost('unit_id'),
            'activity_id' => $this->request->getPost('activity_id'),
            'category_id' => $this->request->getPost('category_id'),
            'from_account_id' => $this->request->getPost('from_account_id'),
            'to_account_id' => null,
            'receiver_id' => null,
            'transaction_date' => $this->request->getPost('transaction_date'),
            'transaction_time' => date('H:i:s'),
            'notes' => $this->request->getPost('notes'),
            'created_by' => $this->currentUser()['id'],
        ]);

        if ($this->request->getPost('action') === 'save_add') {
            return redirect()->back()->with('success', 'Biaya berhasil dicatat. Silakan tambah lagi.');
        }

        return redirect()->to('catat/keluar')->with('success', 'Biaya berhasil dicatat.');
    }

    public function catatHonorGaji(): string
    {
        $institutionId = $this->currentInstitutionId();
        $db = \Config\Database::connect();

        $honorCat = $db->table('transaction_categories')
            ->where('institution_id', $institutionId)
            ->where('deleted_at', null)
            ->where('type', 'keluar')
            ->like('name', 'Honor', 'both')
            ->get()->getRowArray();

        return view('pages/catat/honor_gaji', [
            'pageTitle' => 'Honor & Gaji',
            'activeNav' => 'catat',
            'backUrl' => site_url('catat/keluar'),
            'units' => $this->loadUnitProgramRows(),
            'accounts' => $db->table('accounts')->where('institution_id', $institutionId)->where('deleted_at', null)->get()->getResultArray(),
            'receivers' => $db->table('receivers')->where('institution_id', $institutionId)->where('deleted_at', null)->get()->getResultArray(),
            'activities' => $db->table('activities')->where('institution_id', $institutionId)->where('deleted_at', null)->get()->getResultArray(),
            'honorCat' => $honorCat,
        ]);
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

        $amount = (int) preg_replace('/[^0-9]/', '', (string) $this->request->getPost('amount'));
        $adminFeeInput = (string) $this->request->getPost('admin_fee');
        $adminFee = $adminFeeInput === 'manual'
            ? (int) preg_replace('/[^0-9]/', '', (string) $this->request->getPost('admin_fee_manual'))
            : (int) preg_replace('/[^0-9]/', '', $adminFeeInput);

        (new \App\Models\TransactionModel())->insert([
            'institution_id' => $institutionId,
            'book_period_id' => $bookPeriodId,
            'type' => 'keluar',
            'amount' => $amount,
            'admin_fee' => $adminFee,
            'receiver_id' => $this->request->getPost('receiver_id'),
            'unit_id' => $this->request->getPost('unit_id'),
            'activity_id' => $this->request->getPost('activity_id'),
            'category_id' => $this->request->getPost('category_id'),
            'from_account_id' => $this->request->getPost('from_account_id'),
            'transaction_date' => date('Y-m-d', strtotime((string) $this->request->getPost('transaction_date'))),
            'transaction_time' => date('H:i:s'),
            'notes' => $this->request->getPost('notes'),
            'created_by' => 1,
        ]);

        if ($this->request->getPost('action') === 'save_add') {
            return redirect()->back()->with('success', 'Honor & Gaji berhasil dicatat. Silakan tambah lagi.');
        }

        return redirect()->to('beranda')->with('success', 'Honor & Gaji berhasil dicatat.');
    }

    public function catatPindahDana(): string
    {
        $institutionId = $this->currentInstitutionId();
        $db = \Config\Database::connect();

        return view('pages/catat/pindah_dana', [
            'pageTitle' => 'Pindah Dana',
            'activeNav' => 'catat',
            'backUrl' => site_url('catat/keluar'),
            'units' => $this->loadUnitProgramRows(),
            'accounts' => $db->table('accounts')->where('institution_id', $institutionId)->where('deleted_at', null)->get()->getResultArray(),
            'activities' => $db->table('activities')->where('institution_id', $institutionId)->where('deleted_at', null)->get()->getResultArray(),
        ]);
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

        $amount = (int) preg_replace('/[^0-9]/', '', (string) $this->request->getPost('amount'));
        $adminFeeInput = (string) $this->request->getPost('admin_fee');
        $adminFee = $adminFeeInput === 'manual'
            ? (int) preg_replace('/[^0-9]/', '', (string) $this->request->getPost('admin_fee_manual'))
            : (int) preg_replace('/[^0-9]/', '', $adminFeeInput);

        (new \App\Models\TransactionModel())->insert([
            'institution_id' => $institutionId,
            'book_period_id' => $bookPeriodId,
            'type' => 'pindah',
            'amount' => $amount,
            'admin_fee' => $adminFee,
            'unit_id' => $this->request->getPost('unit_id'),
            'activity_id' => $this->request->getPost('activity_id'),
            'from_account_id' => $this->request->getPost('from_account_id'),
            'to_account_id' => $this->request->getPost('to_account_id'),
            'transaction_date' => date('Y-m-d', strtotime((string) $this->request->getPost('transaction_date'))),
            'transaction_time' => date('H:i:s'),
            'notes' => $this->request->getPost('notes'),
            'created_by' => 1,
        ]);

        if ($this->request->getPost('action') === 'save_add') {
            return redirect()->back()->with('success', 'Pindah Dana berhasil dicatat. Silakan tambah lagi.');
        }

        return redirect()->to('beranda')->with('success', 'Pindah Dana berhasil dicatat.');
    }

    public function transaksi(string $id): string
    {
        $data = [];
        $transaction = $this->findTransaction($data['transactions'], $id);

        if ($transaction === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
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
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
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
}
