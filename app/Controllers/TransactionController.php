<?php

namespace App\Controllers;

use App\Models\AccountModel;
use App\Models\ActivityModel;
use App\Models\BookPeriodModel;
use App\Models\ReceiverModel;
use App\Models\TransactionCategoryModel;
use App\Models\TransactionModel;
use App\Models\UnitModel;
use App\Models\UserModel;
use App\Services\TransactionService;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;

class TransactionController extends BaseController
{
    private TransactionService $transactionService;

    public function __construct()
    {
        $this->transactionService = new TransactionService();
    }

    public function index(): string
    {
        $units = $this->loadUnitsWithActivities();
        $activeContext = $this->buildActiveContext($units);

        $data = [
            'pageTitle' => 'Catat',
            'activeNav' => 'catat',
            'units' => $units,
            'activeContext' => $activeContext,
            'quickCategories' => $this->buildQuickCategories($activeContext),
            'recentTransactions' => $this->transactionService->loadRecentTransactions(
                $this->currentInstitutionId(),
                0, // No unit filter for global history
                0, // No activity filter for global history
                10, // Show 10 items
                $this->activeBookPeriodId()
            ),
        ];

        return view('pages/catat/index', $data);
    }

    public function hapus(int $id): RedirectResponse
    {
        try {
            $this->transactionService->delete($id, $this->currentInstitutionId());
            return redirect()->back()->with('success', 'Transaksi berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function masuk(): string
    {
        $units = $this->loadUnitsWithActivities();
        $activeContext = $this->buildActiveContext($units);
        $incomeCategories = $this->loadCategoriesByKind('Masuk');
        $accounts = $this->loadAccounts();

        $data = [
            'pageTitle' => 'Uang Masuk',
            'activeNav' => 'catat',
            'backUrl' => route_query('catat', $activeContext['query']),
            'units' => $units,
            'activitySummaries' => $this->flattenActivities($units),
            'incomeCategories' => $incomeCategories,
            'selectedIncomeCategory' => $incomeCategories[0]['name'] ?? '',
            'accounts' => $accounts,
            'activeContext' => $activeContext,
        ];

        return view('pages/catat/masuk', $data);
    }

    public function simpanMasuk(): RedirectResponse
    {
        if (! $this->validate($this->transactionRules(['category_id', 'to_account_id']))) {
            return redirect()->back()->withInput()->with('error', 'Semua kolom wajib diisi dengan benar.');
        }

        $payload = [
            'institution_id' => $this->currentInstitutionId(),
            'book_period_id' => $this->activeBookPeriodId(),
            'type' => 'masuk',
            'amount' => $this->normalizeMoney((string) $this->request->getPost('amount')),
            'admin_fee' => 0,
            'unit_id' => (int) $this->request->getPost('unit_id'),
            'activity_id' => (int) $this->request->getPost('activity_id'),
            'category_id' => (int) $this->request->getPost('category_id'),
            'from_account_id' => null,
            'to_account_id' => (int) $this->request->getPost('to_account_id'),
            'receiver_id' => null,
            'transaction_date' => (string) $this->request->getPost('transaction_date'),
            'transaction_time' => date('H:i:s'),
            'notes' => trim((string) $this->request->getPost('notes')),
            'proof_image' => null,
            'created_by' => $this->currentUserId(),
        ];

        $this->transactionService->create($payload);

        if ($this->request->getPost('action') === 'save_add') {
            return redirect()->back()->with('success', 'Uang masuk berhasil dicatat. Silakan tambah lagi.');
        }

        return redirect()->to(route_query('beranda', $this->contextQueryFromPost()))->with('success', 'Uang masuk berhasil dicatat.');
    }

    public function keluar(): string
    {
        $units = $this->loadUnitsWithActivities();
        $activeContext = $this->buildActiveContext($units);

        return view('pages/catat/keluar', [
            'pageTitle' => 'Uang Keluar',
            'activeNav' => 'catat',
            'backUrl' => route_query('catat', $activeContext['query']),
            'activeContext' => $activeContext,
        ]);
    }

    public function biaya(): string
    {
        $units = $this->loadUnitsWithActivities();
        $activeContext = $this->buildActiveContext($units);
        $expenseCategories = $this->loadCategoriesByKind('Keluar');
        $selectedCategory = (string) ($this->request->getGet('kategori') ?? ($expenseCategories[0]['name'] ?? ''));

        return view('pages/catat/biaya', [
            'pageTitle' => 'Biaya / Belanja',
            'activeNav' => 'catat',
            'backUrl' => route_query('catat/keluar', $activeContext['query']),
            'units' => $units,
            'activitySummaries' => $this->flattenActivities($units),
            'accounts' => $this->loadAccounts(),
            'expenseCategories' => $expenseCategories,
            'selectedCategory' => $selectedCategory,
            'activeContext' => $activeContext,
        ]);
    }

    public function simpanBiaya(): RedirectResponse
    {
        if (! $this->validate($this->transactionRules(['category_id', 'from_account_id']))) {
            return redirect()->back()->withInput()->with('error', 'Semua kolom wajib diisi dengan benar.');
        }

        $payload = [
            'institution_id' => $this->currentInstitutionId(),
            'book_period_id' => $this->activeBookPeriodId(),
            'type' => 'keluar',
            'amount' => $this->normalizeMoney((string) $this->request->getPost('amount')),
            'admin_fee' => $this->resolveAdminFee((string) $this->request->getPost('admin_fee_preset'), (string) $this->request->getPost('admin_fee_custom')),
            'unit_id' => (int) $this->request->getPost('unit_id'),
            'activity_id' => (int) $this->request->getPost('activity_id'),
            'category_id' => (int) $this->request->getPost('category_id'),
            'from_account_id' => (int) $this->request->getPost('from_account_id'),
            'to_account_id' => null,
            'receiver_id' => null,
            'transaction_date' => (string) $this->request->getPost('transaction_date'),
            'transaction_time' => date('H:i:s'),
            'notes' => trim((string) $this->request->getPost('notes')),
            'proof_image' => null,
            'created_by' => $this->currentUserId(),
        ];

        $this->transactionService->create($payload);

        if ($this->request->getPost('action') === 'save_add') {
            return redirect()->back()->with('success', 'Biaya berhasil dicatat. Silakan tambah lagi.');
        }

        return redirect()->to(route_query('beranda', $this->contextQueryFromPost()))->with('success', 'Biaya berhasil dicatat.');
    }

    public function honor(): string
    {
        $units = $this->loadUnitsWithActivities();
        $activeContext = $this->buildActiveContext($units);
        $honorCategory = $this->findHonorCategory();

        return view('pages/catat/honor_gaji', [
            'pageTitle' => 'Honor & Gaji',
            'activeNav' => 'catat',
            'backUrl' => route_query('catat/keluar', $activeContext['query']),
            'units' => $units,
            'accounts' => $this->loadAccounts(),
            'receivers' => $this->loadReceivers(),
            'activities' => $this->flattenActivities($units),
            'honorCat' => $honorCategory,
            'activeContext' => $activeContext,
        ]);
    }

    public function simpanHonor(): RedirectResponse
    {
        if (! $this->validate(array_merge($this->transactionRules(['from_account_id']), ['receiver_id' => 'required|is_natural_no_zero']))) {
            return redirect()->back()->withInput()->with('error', 'Semua kolom wajib diisi dengan benar.');
        }

        $honorCategory = $this->findHonorCategory();
        if ($honorCategory === null) {
            return redirect()->back()->withInput()->with('error', 'Kategori Honor belum tersedia di master data.');
        }

        $payload = [
            'institution_id' => $this->currentInstitutionId(),
            'book_period_id' => $this->activeBookPeriodId(),
            'type' => 'honor',
            'amount' => $this->normalizeMoney((string) $this->request->getPost('amount')),
            'admin_fee' => $this->resolveAdminFee((string) $this->request->getPost('admin_fee'), (string) $this->request->getPost('admin_fee_manual')),
            'unit_id' => (int) $this->request->getPost('unit_id'),
            'activity_id' => (int) $this->request->getPost('activity_id'),
            'category_id' => (int) $honorCategory['id'],
            'from_account_id' => (int) $this->request->getPost('from_account_id'),
            'to_account_id' => null,
            'receiver_id' => (int) $this->request->getPost('receiver_id'),
            'transaction_date' => (string) $this->request->getPost('transaction_date'),
            'transaction_time' => date('H:i:s'),
            'notes' => trim((string) $this->request->getPost('notes')),
            'proof_image' => null,
            'created_by' => $this->currentUserId(),
        ];

        $this->transactionService->create($payload);

        if ($this->request->getPost('action') === 'save_add') {
            return redirect()->back()->with('success', 'Honor & gaji berhasil dicatat. Silakan tambah lagi.');
        }

        return redirect()->to(route_query('beranda', $this->contextQueryFromPost()))->with('success', 'Honor & gaji berhasil dicatat.');
    }

    public function pindahDana(): string
    {
        $units = $this->loadUnitsWithActivities();
        $activeContext = $this->buildActiveContext($units);

        return view('pages/catat/pindah_dana', [
            'pageTitle' => 'Pindah Dana',
            'activeNav' => 'catat',
            'backUrl' => route_query('catat/keluar', $activeContext['query']),
            'units' => $units,
            'accounts' => $this->loadAccounts(),
            'activities' => $this->flattenActivities($units),
            'activeContext' => $activeContext,
        ]);
    }

    public function simpanPindahDana(): RedirectResponse
    {
        if (! $this->validate(array_merge($this->transactionRules(['from_account_id', 'to_account_id']), ['notes' => 'required']))) {
            return redirect()->back()->withInput()->with('error', 'Semua kolom wajib diisi dengan benar.');
        }

        $fromAccountId = (int) $this->request->getPost('from_account_id');
        $toAccountId = (int) $this->request->getPost('to_account_id');

        if ($fromAccountId === $toAccountId) {
            return redirect()->back()->withInput()->with('error', 'Rekening asal dan tujuan tidak boleh sama.');
        }

        $payload = [
            'institution_id' => $this->currentInstitutionId(),
            'book_period_id' => $this->activeBookPeriodId(),
            'type' => 'pindah',
            'amount' => $this->normalizeMoney((string) $this->request->getPost('amount')),
            'admin_fee' => $this->resolveAdminFee((string) $this->request->getPost('admin_fee'), (string) $this->request->getPost('admin_fee_manual')),
            'unit_id' => (int) $this->request->getPost('unit_id'),
            'activity_id' => (int) $this->request->getPost('activity_id'),
            'category_id' => null,
            'from_account_id' => $fromAccountId,
            'to_account_id' => $toAccountId,
            'receiver_id' => null,
            'transaction_date' => (string) $this->request->getPost('transaction_date'),
            'transaction_time' => date('H:i:s'),
            'notes' => trim((string) $this->request->getPost('notes')),
            'proof_image' => null,
            'created_by' => $this->currentUserId(),
        ];

        $this->transactionService->create($payload);

        if ($this->request->getPost('action') === 'save_add') {
            return redirect()->back()->with('success', 'Pindah dana berhasil dicatat. Silakan tambah lagi.');
        }

        return redirect()->to(route_query('beranda', $this->contextQueryFromPost()))->with('success', 'Pindah dana berhasil dicatat.');
    }

    public function detail(string $id): string
    {
        $transaction = $this->findTransactionOrFail((int) $id);
        $backUrl = $this->resolveBackUrl(site_url('catat'));

        return view('pages/transaction_detail', [
            'pageTitle' => 'Detail Transaksi',
            'activeNav' => 'catat',
            'backUrl' => $backUrl,
            'transaction' => $transaction,
            'isEditMode' => false,
            'editUrl' => site_url('transaksi/' . $transaction['id'] . '/edit') . '?from=' . rawurlencode($backUrl),
            'transactionForm' => $this->buildTransactionForm($transaction),
            'formAction' => site_url('transaksi/' . $transaction['id']) . '?from=' . rawurlencode($backUrl),
        ]);
    }

    public function edit(string $id): string
    {
        $transaction = $this->findTransactionOrFail((int) $id);
        $backUrl = $this->resolveBackUrl(site_url('transaksi/' . $transaction['id']));

        return view('pages/transaction_detail', [
            'pageTitle' => 'Edit Transaksi',
            'activeNav' => 'catat',
            'backUrl' => $backUrl,
            'transaction' => $transaction,
            'isEditMode' => true,
            'editUrl' => site_url('transaksi/' . $transaction['id'] . '/edit') . '?from=' . rawurlencode($backUrl),
            'transactionForm' => $this->buildTransactionForm($transaction, true),
            'formAction' => site_url('transaksi/' . $transaction['id']) . '?from=' . rawurlencode($backUrl),
        ]);
    }

    public function update(string $id): RedirectResponse
    {
        $existing = $this->findTransactionOrFail((int) $id);
        $type = $existing['type_key'];

        $rules = $this->transactionRules(
            match ($type) {
                'masuk' => ['category_id', 'to_account_id'],
                'pindah' => ['from_account_id', 'to_account_id'],
                'honor' => ['from_account_id', 'receiver_id'],
                default => ['category_id', 'from_account_id'],
            }
        );

        if ($type === 'pindah') {
            $rules['notes'] = 'required';
        }

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Semua kolom wajib diisi dengan benar.');
        }

        $payload = [
            'amount' => $this->normalizeMoney((string) $this->request->getPost('amount')),
            'unit_id' => (int) $this->request->getPost('unit_id'),
            'activity_id' => (int) $this->request->getPost('activity_id'),
            'transaction_date' => (string) $this->request->getPost('transaction_date'),
            'notes' => trim((string) $this->request->getPost('notes')),
        ];

        if ($type === 'masuk') {
            $payload['category_id'] = (int) $this->request->getPost('category_id');
            $payload['to_account_id'] = (int) $this->request->getPost('to_account_id');
            $payload['from_account_id'] = null;
            $payload['receiver_id'] = null;
            $payload['admin_fee'] = 0;
        } elseif ($type === 'pindah') {
            $payload['category_id'] = null;
            $payload['from_account_id'] = (int) $this->request->getPost('from_account_id');
            $payload['to_account_id'] = (int) $this->request->getPost('to_account_id');
            $payload['receiver_id'] = null;
            $payload['admin_fee'] = $this->resolveAdminFee((string) $this->request->getPost('admin_fee'), (string) $this->request->getPost('admin_fee_manual'));
        } else {
            $payload['category_id'] = (int) $this->request->getPost('category_id');
            $payload['from_account_id'] = (int) $this->request->getPost('from_account_id');
            $payload['to_account_id'] = null;
            $payload['receiver_id'] = $type === 'honor' ? (int) $this->request->getPost('receiver_id') : null;
            $payload['admin_fee'] = $this->resolveAdminFee((string) ($this->request->getPost('admin_fee_preset') ?: $this->request->getPost('admin_fee')), (string) ($this->request->getPost('admin_fee_custom') ?: $this->request->getPost('admin_fee_manual')));
        }

        (new TransactionModel())->update((int) $existing['id'], $payload);

        return redirect()->to($this->resolveBackUrl(site_url('beranda')))
            ->with('success', 'Transaksi berhasil diperbarui.');
    }

    private function currentInstitutionId(): int
    {
        return (int) ($this->session->get('auth_institution_id') ?? 1);
    }

    private function currentUserId(): int
    {
        return (int) ($this->session->get('auth_user_id') ?? 1);
    }

    private function activeBookPeriodId(): ?int
    {
        $period = (new BookPeriodModel())
            ->where('institution_id', $this->currentInstitutionId())
            ->where('is_active', 1)
            ->first();

        return is_array($period) ? (int) $period['id'] : null;
    }

    private function loadUnitsWithActivities(): array
    {
        $units = (new UnitModel())
            ->where('institution_id', $this->currentInstitutionId())
            ->where('deleted_at', null)
            ->where('is_active', 1)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('name', 'ASC')
            ->findAll();

        $activityModel = new ActivityModel();

        foreach ($units as &$unit) {
            $unit['activities'] = $activityModel
                ->where('unit_id', (int) $unit['id'])
                ->where('deleted_at', null)
                ->where('is_active', 1)
                ->orderBy('sort_order', 'ASC')
                ->orderBy('name', 'ASC')
                ->findAll();
        }
        unset($unit);

        return $units;
    }

    private function flattenActivities(array $units): array
    {
        $activities = [];
        foreach ($units as $unit) {
            foreach ($unit['activities'] as $activity) {
                $activity['unit_name'] = $unit['name'];
                $activities[] = $activity;
            }
        }

        return $activities;
    }

    private function loadAccounts(): array
    {
        return (new AccountModel())
            ->where('institution_id', $this->currentInstitutionId())
            ->where('deleted_at', null)
            ->where('is_active', 1)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('name', 'ASC')
            ->findAll();
    }

    private function loadReceivers(): array
    {
        return (new ReceiverModel())
            ->where('institution_id', $this->currentInstitutionId())
            ->where('deleted_at', null)
            ->orderBy('name', 'ASC')
            ->findAll();
    }

    private function loadCategoriesByKind(string $kind): array
    {
        return (new TransactionCategoryModel())
            ->where('institution_id', $this->currentInstitutionId())
            ->where('kind', $kind)
            ->where('deleted_at', null)
            ->where('is_active', 1)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('name', 'ASC')
            ->findAll();
    }

    private function buildActiveContext(array $units): array
    {
        $selection = $this->resolveActiveContextSelection($units);
        $query = [
            'unit' => $selection['unit_slug'] ?: null,
            'kegiatan' => $selection['activity_slug'] ?: null,
        ];

        $accounts = $this->loadAccounts();
        $defaultExpenseAccount = $accounts[0]['name'] ?? '';
        $defaultIncomeAccount = $accounts[0]['name'] ?? '';
        foreach ($accounts as $account) {
            if (str_contains(strtolower($account['name']), 'bri')) {
                $defaultIncomeAccount = $account['name'];
            }
            if (str_contains(strtolower($account['name']), 'dana operasional')) {
                $defaultExpenseAccount = $account['name'];
            }
        }

        return [
            'unit_id' => $selection['unit_id'],
            'activity_id' => $selection['activity_id'],
            'unit_slug' => $selection['unit_slug'],
            'activity_slug' => $selection['activity_slug'],
            'unit_name' => $selection['unit_name'],
            'activity_name' => $selection['activity_name'],
            'display' => $selection['unit_name'] . ' / ' . $selection['activity_name'],
            'query' => $query,
            'switch_url' => site_url('konteks-aktif'),
            'switch_redirect' => site_url('catat'),
            'switch_params' => [],
            'activity_url' => $selection['activity_slug'] !== '' ? site_url('kegiatan/' . $selection['activity_slug']) : site_url('catat'),
            'masuk_url' => site_url('catat/masuk'),
            'keluar_url' => site_url('catat/keluar'),
            'biaya_url' => route_query('catat/keluar/biaya', $query),
            'pindah_dana_url' => route_query('catat/keluar/pindah-dana', $query),
            'default_income_account' => $defaultIncomeAccount,
            'default_expense_account' => $defaultExpenseAccount,
        ];
    }

    private function buildQuickCategories(array $activeContext): array
    {
        $categories = array_filter(
            $this->loadCategoriesByKind('Keluar'),
            static fn(array $category): bool => (int) ($category['is_quick'] ?? 0) === 1
        );

        return array_map(
            static function (array $category) use ($activeContext): array {
                return [
                    'label' => $category['chip_label'] ?: $category['name'],
                    'value' => $category['name'],
                    'href' => route_query('catat/keluar/biaya', array_merge($activeContext['query'], ['kategori' => $category['name']])),
                ];
            },
            array_values($categories)
        );
    }


    private function findTransactionOrFail(int $id): array
    {
        $row = (new TransactionModel())
            ->where('institution_id', $this->currentInstitutionId())
            ->where('id', $id)
            ->where('deleted_at', null)
            ->first();

        if (! is_array($row)) {
            throw PageNotFoundException::forPageNotFound();
        }

        return $this->transactionService->formatTransactions([$row])[0];
    }

    private function buildTransactionForm(array $transaction, bool $preferOld = false): array
    {
        $units = $this->loadUnitsWithActivities();
        $activities = $this->flattenActivities($units);
        $accounts = $this->loadAccounts();
        $incomeCategories = $this->loadCategoriesByKind('Masuk');
        $expenseCategories = $this->loadCategoriesByKind('Keluar');
        $receivers = $this->loadReceivers();

        return [
            'nominal_value' => old('amount', $preferOld ? (string) $transaction['amount'] : rupiah($transaction['amount'])),
            'date_value' => old('transaction_date', $transaction['transaction_date']),
            'description_value' => old('notes', $transaction['notes']),
            'unit_options' => $this->buildOptionSet($units, old('unit_id', (string) $transaction['unit_id'])),
            'activity_options' => $this->buildOptionSet($activities, old('activity_id', (string) $transaction['activity_id'])),
            'account_options' => $this->buildOptionSet($accounts, old('from_account_id', $transaction['from_account_id'] > 0 ? (string) $transaction['from_account_id'] : (string) $transaction['to_account_id'])),
            'to_account_options' => $this->buildOptionSet($accounts, old('to_account_id', (string) $transaction['to_account_id'])),
            'income_category_options' => $this->buildOptionSet($incomeCategories, old('category_id', (string) $transaction['category_id'])),
            'expense_category_options' => $this->buildOptionSet($expenseCategories, old('category_id', (string) $transaction['category_id'])),
            'receiver_options' => $this->buildOptionSet($receivers, old('receiver_id', (string) $transaction['receiver_id'])),
            'admin_fee_value' => old('admin_fee', $transaction['admin_fee'] > 0 ? (string) $transaction['admin_fee'] : '0'),
        ];
    }

    private function buildOptionSet(array $rows, string $selectedId): array
    {
        return array_map(
            static function (array $row) use ($selectedId): array {
                $label = $row['name'] ?? '';
                if (isset($row['unit_name'])) {
                    $label .= ' · ' . $row['unit_name'];
                }

                return [
                    'value' => (string) $row['id'],
                    'label' => $label,
                    'selected' => (string) $row['id'] === $selectedId,
                ];
            },
            $rows
        );
    }

    private function indexById(array $rows): array
    {
        $mapped = [];
        foreach ($rows as $row) {
            $mapped[(int) $row['id']] = $row;
        }

        return $mapped;
    }

    private function contextQueryFromPost(): array
    {
        $unit = (new UnitModel())->find((int) $this->request->getPost('unit_id'));
        $activity = (new ActivityModel())->find((int) $this->request->getPost('activity_id'));

        return [
            'unit' => $unit['slug'] ?? null,
            'kegiatan' => $activity['slug'] ?? null,
        ];
    }

    private function resolveAdminFee(string $preset, string $custom): float
    {
        return $preset === 'manual' ? $this->normalizeMoney($custom) : $this->normalizeMoney($preset);
    }

    private function normalizeMoney(string $raw): float
    {
        $normalized = preg_replace('/[^0-9,.-]/', '', $raw) ?? '0';
        $normalized = str_replace('.', '', $normalized);
        $normalized = str_replace(',', '.', $normalized);

        return (float) $normalized;
    }

    private function transactionRules(array $requiredExtra): array
    {
        $rules = [
            'unit_id' => 'required|is_natural_no_zero',
            'activity_id' => 'required|is_natural_no_zero',
            'amount' => 'required',
            'transaction_date' => 'required|valid_date[Y-m-d]',
        ];

        foreach ($requiredExtra as $field) {
            $rules[$field] = 'required|is_natural_no_zero';
        }

        return $rules;
    }

    private function resolveBackUrl(string $fallback): string
    {
        $from = (string) $this->request->getGet('from');
        return ($from !== '' && str_starts_with($from, site_url())) ? $from : $fallback;
    }

    private function findHonorCategory(): ?array
    {
        return (new TransactionCategoryModel())
            ->where('institution_id', $this->currentInstitutionId())
            ->where('kind', 'Keluar')
            ->where('deleted_at', null)
            ->like('name', 'Honor', 'both')
            ->first();
    }
}
