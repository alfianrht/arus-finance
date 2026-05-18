<?php

namespace App\Controllers;

use App\Models\ActivityModel;
use App\Models\AccountModel;
use App\Models\BookPeriodModel;
use App\Models\ReceiverModel;
use App\Models\ReportPositionModel;
use App\Models\TransactionCategoryModel;
use App\Models\UnitModel;
use App\Services\TransactionService;

class HomeController extends BaseController
{
    private TransactionService $transactionService;

    public function __construct()
    {
        $this->transactionService = new TransactionService();
    }

    public function index(): string
    {
        $institution = $this->currentInstitution();
        $institutionId = (int) ($institution['id'] ?? 1);
        $transactionPage = max(1, (int) ($this->request->getGet('transaksi_page') ?? 1));
        
        $period = $this->activeBookPeriod();
        $bookPeriodId = is_array($period) ? (int) $period['id'] : 0;

        $units = $this->loadUnitsWithActivities($institutionId);
        $accounts = $this->loadAccounts($institutionId);
        $contextSelection = $this->resolveActiveContextSelection($units, $accounts);

        $db = \Config\Database::connect();

        // 1. Calculate Summary
        // Opening Balances
        $obSum = (float) ($db->table('opening_balances')
            ->where('institution_id', $institutionId)
            ->where('book_period_id', $bookPeriodId)
            ->where('deleted_at', null)
            ->selectSum('amount')
            ->get()->getRow()->amount ?? 0);

        // Incomes
        $incSum = (float) ($db->table('transactions')
            ->where('institution_id', $institutionId)
            ->where('book_period_id', $bookPeriodId)
            ->where('type', 'masuk')
            ->where('deleted_at', null)
            ->selectSum('amount')
            ->get()->getRow()->amount ?? 0);

        // Expenses (keluar, honor) + pindah fees
        $expSumMain = (float) ($db->table('transactions')
            ->where('institution_id', $institutionId)
            ->where('book_period_id', $bookPeriodId)
            ->whereIn('type', ['keluar', 'honor'])
            ->where('deleted_at', null)
            ->select('SUM(amount + admin_fee) as total')
            ->get()->getRow()->total ?? 0);

        $pindahFeeSum = (float) ($db->table('transactions')
            ->where('institution_id', $institutionId)
            ->where('book_period_id', $bookPeriodId)
            ->where('type', 'pindah')
            ->where('deleted_at', null)
            ->selectSum('admin_fee')
            ->get()->getRow()->admin_fee ?? 0);

        $expSum = $expSumMain + $pindahFeeSum;

        $surplus = $incSum - $expSum;
        $balance = $obSum + $surplus;

        // 2. Format Recent Transactions using the robust TransactionController formatter pattern.
        $homeTransactionsPage = $this->transactionService->loadTransactionHistoryPage(
            $institutionId,
            0,
            0,
            $bookPeriodId,
            'semua',
            $transactionPage,
            10
        );

        // 3. Units mapping for cards
        foreach ($units as &$u) {
            $unitTransactionBuilder = $db->table('transactions')
                ->where('institution_id', $institutionId)
                ->where('unit_id', $u['id'])
                ->where('deleted_at', null);

            if ($bookPeriodId > 0) {
                $unitTransactionBuilder->where('book_period_id', $bookPeriodId);
            }

            $uInc = (float) ((clone $unitTransactionBuilder)->where('type', 'masuk')->selectSum('amount')->get()->getRow()->amount ?? 0);

            $uExpMain = (float) ((clone $unitTransactionBuilder)->whereIn('type', ['keluar', 'honor'])->select('SUM(amount + admin_fee) as total')->get()->getRow()->total ?? 0);
            $uExpFee = (float) ((clone $unitTransactionBuilder)->where('type', 'pindah')->selectSum('admin_fee')->get()->getRow()->admin_fee ?? 0);
            $uExp = $uExpMain + $uExpFee;
            
            $u['income'] = $uInc;
            $u['expense'] = $uExp;
            $u['surplus'] = $uInc - $uExp;
            $u['related_balance'] = $uInc - $uExp;
            
            $uFirstAct = $u['activities'][0] ?? null;
            $u['quick_activity_name'] = $uFirstAct['name'] ?? 'Belum ada kegiatan';
            $u['detail_url'] = site_url('unit/' . $u['slug']);
            $u['masuk_url'] = route_query('catat/masuk', ['unit' => $u['slug'], 'kegiatan' => $uFirstAct['slug'] ?? null]);
            $u['keluar_url'] = route_query('catat/keluar', ['unit' => $u['slug'], 'kegiatan' => $uFirstAct['slug'] ?? null]);
        }
        unset($u);

        // 4. Active Context
        $activeContext = [
            'unit_id' => $contextSelection['unit_id'],
            'activity_id' => $contextSelection['activity_id'],
            'account_id' => $contextSelection['account_id'],
            'unit_slug' => $contextSelection['unit_slug'],
            'activity_slug' => $contextSelection['activity_slug'],
            'account_slug' => $contextSelection['account_slug'],
            'unit_name' => $contextSelection['unit_name'],
            'activity_name' => $contextSelection['activity_name'],
            'account_name' => $contextSelection['account_name'],
            'display' => $contextSelection['unit_name'] . ' / ' . $contextSelection['activity_name'] . ' / ' . $contextSelection['account_name'],
            'query' => [
                'unit' => $contextSelection['unit_slug'] ?: null,
                'kegiatan' => $contextSelection['activity_slug'] ?: null,
                'rekening' => $contextSelection['account_slug'] ?: null,
            ],
            'switch_url' => site_url('konteks-aktif'),
            'switch_redirect' => site_url('catat'),
            'switch_params' => [],
            'activity_url' => $contextSelection['activity_slug'] !== '' ? site_url('kegiatan/' . $contextSelection['activity_slug']) : site_url('catat'),
            'masuk_url' => site_url('catat/masuk'),
            'keluar_url' => site_url('catat/keluar'),
            'account_options' => array_map(static fn(array $account): array => [
                'slug' => (string) ($account['slug'] ?? ''),
                'name' => (string) ($account['name'] ?? ''),
            ], $accounts),
        ];

        // 5. Build settings shortcuts for the dashboard
        $settingsShortcuts = $this->buildSettingsShortcuts([
            'institutionName'       => $institution['name'],
            'units'                 => $units,
            'activityCount'         => array_sum(array_map(static fn(array $unit): int => count($unit['activities'] ?? []), $units)),
            'accountCount'          => (new AccountModel())->where('institution_id', $institutionId)->where('deleted_at', null)->countAllResults(),
            'transactionCategoryCount' => (new TransactionCategoryModel())->where('institution_id', $institutionId)->where('deleted_at', null)->countAllResults(),
            'receiverCount'         => (new ReceiverModel())->where('institution_id', $institutionId)->where('deleted_at', null)->countAllResults(),
            'reportPositionCount'   => (new ReportPositionModel())->where('institution_id', $institutionId)->countAllResults(),
            'bookPeriodCount'       => (new BookPeriodModel())->where('institution_id', $institutionId)->countAllResults(),
        ]);

        $data = [
            'appName'          => $institution['app_name'] ?? 'Arusdana',
            'institutionName'  => $institution['name'],
            'pageTitle'        => 'Beranda',
            'activeNav'        => 'beranda',
            'bookPeriodLabel'  => $this->activeBookPeriodLabel(),
            'activeContext'    => $activeContext,
            'summary'          => [
                'balance' => $balance,
                'income'  => $incSum,
                'expense' => $expSum,
                'surplus' => $surplus,
            ],
            'homeTransactions'  => $homeTransactionsPage['items'],
            'homeTransactionPagination' => $homeTransactionsPage,
            'units'             => $units,
            'settingsShortcuts' => $settingsShortcuts,
        ];

        return view('pages/home', $data);
    }

    private function loadUnitsWithActivities(int $institutionId): array
    {
        $units = (new UnitModel())
            ->where('institution_id', $institutionId)
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

    private function loadAccounts(int $institutionId): array
    {
        return (new AccountModel())
            ->where('institution_id', $institutionId)
            ->where('deleted_at', null)
            ->where('is_active', 1)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('name', 'ASC')
            ->findAll();
    }

    private function currentInstitution(): array
    {
        $id = (int) (service('session')->get('auth_institution_id') ?? 1);
        $row = (new \App\Models\InstitutionModel())->find($id);

        return is_array($row) ? $row : ['id' => 1, 'name' => 'Arusdana', 'app_name' => 'Arusdana'];
    }

    private function buildSettingsShortcuts(array $data): array
    {
        return [
            [
                'group' => 'Operasional Harian',
                'title' => 'Profil Lembaga',
                'description' => 'Identitas utama aplikasi dan lembaga yang memakai Arus.',
                'meta' => $data['institutionName'] ?? 'Lembaga',
                'href' => site_url('pengaturan/profil-lembaga'),
                'icon' => 'badge',
            ],
            [
                'group' => 'Operasional Harian',
                'title' => 'Unit / Program',
                'description' => 'Struktur usaha atau layanan yang menaungi kegiatan.',
                'meta' => count($data['units'] ?? []) . ' unit',
                'href' => site_url('pengaturan/unit-program'),
                'icon' => 'domain',
            ],
            [
                'group' => 'Operasional Harian',
                'title' => 'Kegiatan',
                'description' => 'Turunan unit yang dipakai sebagai konteks aktif pencatatan.',
                'meta' => ($data['activityCount'] ?? 0) . ' kegiatan',
                'href' => site_url('pengaturan/kegiatan'),
                'icon' => 'workspaces',
            ],
            [
                'group' => 'Operasional Harian',
                'title' => 'Rekening / Dompet',
                'description' => 'Sumber dan tujuan uang bergerak saat transaksi dicatat.',
                'meta' => ($data['accountCount'] ?? 0) . ' rekening',
                'href' => site_url('pengaturan/rekening-dompet'),
                'icon' => 'account_balance_wallet',
            ],
            [
                'group' => 'Operasional Harian',
                'title' => 'Kategori Transaksi',
                'description' => 'Kategori untuk pemasukan, biaya, honor, dan pengeluaran lain.',
                'meta' => ($data['transactionCategoryCount'] ?? 0) . ' kategori',
                'href' => site_url('pengaturan/kategori-biaya'),
                'icon' => 'inventory_2',
            ],
            [
                'group' => 'Operasional Harian',
                'title' => 'Penerima',
                'description' => 'Kontak internal, vendor, atau pihak yang menerima pembayaran.',
                'meta' => ($data['receiverCount'] ?? 0) . ' penerima',
                'href' => site_url('pengaturan/penerima'),
                'icon' => 'person',
            ],
            [
                'group' => 'Fondasi Laporan',
                'title' => 'Pos Laporan',
                'description' => 'Fondasi struktur laporan tahunan dan pemetaan transaksi.',
                'meta' => ($data['reportPositionCount'] ?? 0) . ' pos',
                'href' => site_url('pengaturan/pos-laporan'),
                'icon' => 'account_tree',
            ],
            [
                'group' => 'Fondasi Laporan',
                'title' => 'Tahun Buku',
                'description' => 'Periode aktif yang dipakai transaksi dan laporan.',
                'meta' => ($data['bookPeriodCount'] ?? 0) . ' periode',
                'href' => site_url('pengaturan/tahun-buku'),
                'icon' => 'calendar_month',
            ],
        ];
    }
}
