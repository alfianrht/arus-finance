<?php

namespace App\Controllers;

use App\Models\ActivityModel;
use App\Models\BookPeriodModel;
use App\Services\ProjectPocketService;
use App\Models\UnitModel;
use App\Services\TransactionService;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\Database\BaseBuilder;

class ReportController extends BaseController
{
    private TransactionService $transactionService;
    private ProjectPocketService $projectPocketService;

    public function __construct()
    {
        $this->transactionService = new TransactionService();
        $this->projectPocketService = new ProjectPocketService();
    }

    public function index(): string
    {
        $institutionId = $this->currentInstitutionId();
        $db = \Config\Database::connect();
        $request = service('request');
        $transactionPage = max(1, (int) ($request->getGet('transaksi_page') ?? 1));
        $transferPage = max(1, (int) ($request->getGet('mutasi_page') ?? 1));

        // Filters
        $selectedPeriodSlug = $request->getGet('periode') ?: 'semua';
        $selectedUnitSlug = $request->getGet('unit') ?: 'semua';
        $selectedActivitySlug = $request->getGet('kegiatan') ?: 'semua';

        // 1. Load Filter Options
        $periods = [['slug' => 'semua', 'label' => 'Semua Periode']];
        $dbPeriods = $db->table('book_periods')
            ->where('institution_id', $institutionId)
            ->get()->getResultArray();
        
        foreach ($dbPeriods as $p) {
            $startYear = date('Y', strtotime($p['start_date']));
            $endYear = date('Y', strtotime($p['end_date']));
            $periods[] = [
                'slug' => 'period-' . $p['id'],
                'label' => 'TB ' . $startYear . '/' . $endYear . ($p['is_active'] ? ' (Aktif)' : '')
            ];
        }

        $units = $this->loadUnitProgramRows($institutionId);
        $unitMap = $this->indexById($units);
        $allUnitIds = array_column($units, 'id');
        $allActivities = $allUnitIds === []
            ? []
            : $db->table('activities')
                ->whereIn('unit_id', $allUnitIds)
                ->where('deleted_at', null)
                ->where('is_active', 1)
                ->get()->getResultArray();
        $activityMap = $this->indexById($allActivities);
        $filterUnitId = $this->resolveUnitId($selectedUnitSlug, $units);
        $filterActivityId = $this->resolveActivityId($selectedActivitySlug, $allActivities);
        
        $filterActivities = [];
        if ($filterUnitId !== null) {
            $unitId = $filterUnitId;
            $filterActivities = $db->table('activities')
                ->where('unit_id', $unitId)
                ->where('deleted_at', null)
                ->where('is_active', 1)
                ->get()->getResultArray();
        } else {
            if (empty($allUnitIds)) {
                $filterActivities = [];
            } else {
                $filterActivities = $db->table('activities')
                    ->whereIn('unit_id', $allUnitIds)
                    ->where('deleted_at', null)
                    ->where('is_active', 1)
                    ->get()->getResultArray();
            }
        }
        
        foreach ($filterActivities as &$fa) {
            $faUnit = $unitMap[(int) $fa['unit_id']] ?? null;
            $fa['unit_name'] = $faUnit['name'] ?? '';
        }
        unset($fa);

        $dropdownActivities = $allActivities;
        foreach ($dropdownActivities as &$da) {
            $daUnit = $unitMap[(int) $da['unit_id']] ?? null;
            $da['slug'] = (string) ($da['slug'] ?? '');
            $da['unit_slug'] = (string) ($daUnit['slug'] ?? '');
            $da['unit_name'] = $daUnit['name'] ?? '';
        }
        unset($da);

        // Map selected slugs to IDs
        $filterPeriodId = $selectedPeriodSlug !== 'semua' ? (int) str_replace('period-', '', $selectedPeriodSlug) : null;

        // Base Query builder for transactions
        $tBuilder = $db->table('transactions')
            ->where('institution_id', $institutionId)
            ->where('deleted_at', null);
            
        if ($filterPeriodId) $tBuilder->where('book_period_id', $filterPeriodId);
        if ($filterUnitId) $tBuilder->where('unit_id', $filterUnitId);
        if ($filterActivityId) $tBuilder->where('activity_id', $filterActivityId);

        // 2. Summary
        $obBuilder = $db->table('opening_balances')
            ->where('institution_id', $institutionId)
            ->where('deleted_at', null);
            
        if ($filterPeriodId) $obBuilder->where('book_period_id', $filterPeriodId);
        $obSum = (float) ($obBuilder->selectSum('amount')->get()->getRow()->amount ?? 0);

        $incSum = (float) (clone $tBuilder)->where('type', 'masuk')->selectSum('amount')->get()->getRow()->amount ?? 0;
        
        $expMain = (float) (clone $tBuilder)->whereIn('type', ['keluar', 'honor'])->select('SUM(amount + admin_fee) as total')->get()->getRow()->total ?? 0;
        $expPindah = (float) (clone $tBuilder)->where('type', 'pindah')->selectSum('admin_fee')->get()->getRow()->admin_fee ?? 0;
        $expSum = $expMain + $expPindah;
        
        $surplus = $incSum - $expSum;

        $rekapSummary = [
            'balance' => $obSum + $surplus,
            'income' => $incSum,
            'expense' => $expSum,
            'surplus' => $surplus,
        ];

        // 3. Transactions List
        $recentRows = (clone $tBuilder)
            ->orderBy('transaction_date', 'DESC')
            ->orderBy('transaction_time', 'DESC')
            ->orderBy('id', 'DESC')
            ->get()->getResultArray();
            
        $formattedTransactions = $this->transactionService->formatTransactions($recentRows);
        
        $rekapTransactions = [];
        $rekapTransferItems = [];
        
        foreach ($formattedTransactions as $item) {
            if ($item['type_key'] === 'pindah') {
                $rekapTransferItems[] = $item;
            } else {
                $rekapTransactions[] = $item;
            }
        }
        $rekapTransactionsPagination = paginate_items($rekapTransactions, $transactionPage, 10);
        $rekapTransfersPagination = paginate_items($rekapTransferItems, $transferPage, 10);

        // 4. Accounts
        $accounts = $db->table('accounts')
            ->where('institution_id', $institutionId)
            ->where('deleted_at', null)
            ->where('is_active', 1)
            ->get()->getResultArray();
            
        $rekapAccounts = [];
        foreach ($accounts as $acc) {
            $accOb = (float) ($db->table('opening_balances')
                ->where('account_id', $acc['id'])
                ->where('deleted_at', null)
                ->selectSum('amount')
                ->get()->getRow()->amount ?? 0);
                
            $accIncMasuk = (float) (clone $tBuilder)->where('type', 'masuk')->where('to_account_id', $acc['id'])->selectSum('amount')->get()->getRow()->amount ?? 0;
            $accIncPindah = (float) (clone $tBuilder)->where('type', 'pindah')->where('to_account_id', $acc['id'])->selectSum('amount')->get()->getRow()->amount ?? 0;
            $accInc = $accIncMasuk + $accIncPindah;
            
            $accExpMain = (float) (clone $tBuilder)->whereIn('type', ['keluar', 'honor'])->where('from_account_id', $acc['id'])->select('SUM(amount + admin_fee) as total')->get()->getRow()->total ?? 0;
            $accExpPindah = (float) (clone $tBuilder)->where('type', 'pindah')->where('from_account_id', $acc['id'])->select('SUM(amount + admin_fee) as total')->get()->getRow()->total ?? 0;
            $accExp = $accExpMain + $accExpPindah;
            
            $rekapAccounts[] = [
                'name' => $acc['name'],
                'kind' => $acc['kind'] ?? 'Tunai',
                'mark' => $acc['mark'] ?? '',
                'logo_asset' => $acc['logo_asset'] ?? null,
                'account_number' => $acc['account_number'] ?? '',
                'slug' => $acc['slug'] ?? 'acc-' . $acc['id'],
                'balance' => $accOb + $accInc - $accExp,
                'income' => $accInc,
                'expense' => $accExp,
                'transfer_in' => $accIncPindah,
                'transfer_out' => $accExpPindah,
                'preview_activity' => 'Rekapitulasi Saldo',
                'movement_count' => (clone $tBuilder)
                    ->groupStart()
                        ->where('from_account_id', $acc['id'])
                        ->orWhere('to_account_id', $acc['id'])
                    ->groupEnd()
                    ->countAllResults(),
                'icon' => 'account_balance_wallet',
                'color' => 'emerald',
                'detail_url' => route_query('rekening/' . ($acc['slug'] ?? ('acc-' . $acc['id']))),
            ];
        }

        // 5. Units
        $rekapUnits = [];
        if ($filterUnitId) {
            $unitRows = $db->table('units')->where('id', $filterUnitId)->where('deleted_at', null)->get()->getResultArray();
        } else {
            $unitRows = $units;
        }
        
        foreach ($unitRows as $u) {
            $uInc = (float) (clone $tBuilder)->where('unit_id', $u['id'])->where('type', 'masuk')->selectSum('amount')->get()->getRow()->amount ?? 0;
            
            $uExpMain = (float) (clone $tBuilder)->where('unit_id', $u['id'])->whereIn('type', ['keluar', 'honor'])->select('SUM(amount + admin_fee) as total')->get()->getRow()->total ?? 0;
            $uExpPindah = (float) (clone $tBuilder)->where('unit_id', $u['id'])->where('type', 'pindah')->selectSum('admin_fee')->get()->getRow()->admin_fee ?? 0;
            $uExp = $uExpMain + $uExpPindah;
            
            if ($uInc == 0 && $uExp == 0) continue; // Only units with transactions in this filter
            
            $uActRows = array_values(array_filter($allActivities, static fn(array $activity): bool => (int) $activity['unit_id'] === (int) $u['id']));
            $uActCount = count($uActRows);
            $uFirstAct = $uActRows[0] ?? null;
            $u['income'] = $uInc;
            $u['expense'] = $uExp;
            $u['surplus'] = $uInc - $uExp;
            $u['activities'] = $uActRows;
            $u['quick_activity_name'] = $uFirstAct['name'] ?? '-';
            $u['detail_url'] = route_query('unit/' . $u['slug'], ['periode' => $selectedPeriodSlug, 'kegiatan' => $selectedActivitySlug]);
            $rekapUnits[] = $u;
        }

        // 6. Activities
        $rekapActivities = [];
        $actRows = $filterActivities;
        foreach ($actRows as $a) {
            $aInc = (float) (clone $tBuilder)->where('activity_id', $a['id'])->where('type', 'masuk')->selectSum('amount')->get()->getRow()->amount ?? 0;
            
            $aExpMain = (float) (clone $tBuilder)->where('activity_id', $a['id'])->whereIn('type', ['keluar', 'honor'])->select('SUM(amount + admin_fee) as total')->get()->getRow()->total ?? 0;
            $aExpPindah = (float) (clone $tBuilder)->where('activity_id', $a['id'])->where('type', 'pindah')->selectSum('admin_fee')->get()->getRow()->admin_fee ?? 0;
            $aExp = $aExpMain + $aExpPindah;
            
            if ($aInc == 0 && $aExp == 0) continue;
            
            $a['income'] = $aInc;
            $a['expense'] = $aExp;
            $a['surplus'] = $aInc - $aExp;
            $a['related_balance'] = $aInc - $aExp;
            $a['detail_url'] = route_query('kegiatan/' . $a['slug'], ['periode' => $selectedPeriodSlug, 'unit' => $selectedUnitSlug]);
            $activityUnitSlug = null;
            foreach ($units as $candidateUnit) {
                if ((int) $candidateUnit['id'] === (int) ($a['unit_id'] ?? 0)) {
                    $activityUnitSlug = $candidateUnit['slug'] ?? null;
                    break;
                }
            }
            $a['masuk_url'] = route_query('catat/masuk', ['unit' => $activityUnitSlug, 'kegiatan' => $a['slug']]);
            $a['keluar_url'] = route_query('catat/keluar', ['unit' => $activityUnitSlug, 'kegiatan' => $a['slug']]);
            $rekapActivities[] = $a;
        }

        $receiverSums = (clone $tBuilder)
            ->where('receiver_id IS NOT NULL')
            ->select('receiver_id, SUM(amount + admin_fee) as total')
            ->groupBy('receiver_id')
            ->get()->getResultArray();
            
        $rekapReceivers = [];
        foreach ($receiverSums as $rs) {
            $rec = $db->table('receivers')->where('id', $rs['receiver_id'])->get()->getRowArray();
            if ($rec) {
                $rekapReceivers[] = [
                    'id' => (int) $rec['id'],
                    'name' => $rec['name'],
                    'type' => $rec['type'],
                    'total_received' => (float) $rs['total'],
                    'detail_url' => site_url('penerima/' . $rec['id']),
                ];
            }
        }
        
        usort($rekapReceivers, fn($a, $b) => $b['total_received'] <=> $a['total_received']);

        $data = [
            'pageTitle' => 'Rekap',
            'activeNav' => 'rekap',
            'periods' => $periods,
            'selectedPeriodSlug' => $selectedPeriodSlug,
            'units' => $units,
            'selectedUnitSlug' => $selectedUnitSlug,
            'filterActivities' => $filterActivities,
            'dropdownActivities' => $dropdownActivities,
            'selectedActivitySlug' => $selectedActivitySlug,
            'rekapSummary' => $rekapSummary,
            'rekapTransactions' => $rekapTransactionsPagination['items'],
            'rekapTransactionPagination' => $rekapTransactionsPagination,
            'rekapTransferItems' => $rekapTransfersPagination['items'],
            'rekapTransferPagination' => $rekapTransfersPagination,
            'rekapAccounts' => $rekapAccounts,
            'rekapUnits' => $rekapUnits,
            'rekapActivities' => $rekapActivities,
            'rekapReceivers' => $rekapReceivers,
        ];

        return view('pages/rekap', $data);
    }

    private function loadUnitProgramRows(int $institutionId): array
    {
        return (new UnitModel())
            ->where('institution_id', $institutionId)
            ->where('deleted_at', null)
            ->where('is_active', 1)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('name', 'ASC')
            ->findAll();
    }

    private function loadAllUnitProgramRows(int $institutionId): array
    {
        return (new UnitModel())
            ->where('institution_id', $institutionId)
            ->where('deleted_at', null)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('name', 'ASC')
            ->findAll();
    }


    public function rekening(string $slug): string
    {
        $institutionId = $this->currentInstitutionId();
        $db = \Config\Database::connect();
        $request = service('request');

        $acc = $db->table('accounts')
            ->groupStart()
                ->where('slug', $slug)
                ->orWhere('id', (int) str_replace('acc-', '', $slug))
            ->groupEnd()
            ->where('institution_id', $institutionId)
            ->where('deleted_at', null)
            ->get()->getRowArray();
        
        if (! $acc) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }
        $accountId = (int) $acc['id'];

        $selectedPeriodSlug = $request->getGet('periode') ?: 'semua';
        $selectedUnitSlug = $request->getGet('unit') ?: 'semua';
        $selectedActivitySlug = $request->getGet('kegiatan') ?: 'semua';
        $transactionPage = max(1, (int) ($request->getGet('transaksi_page') ?? 1));

        $filterPeriodId = $selectedPeriodSlug !== 'semua' ? (int) str_replace('period-', '', $selectedPeriodSlug) : null;
        $units = $this->loadUnitProgramRows($institutionId);
        $allUnitIds = array_column($units, 'id');
        $allActivities = $allUnitIds === []
            ? []
            : $db->table('activities')->whereIn('unit_id', $allUnitIds)->where('deleted_at', null)->where('is_active', 1)->get()->getResultArray();
        $unitMap = $this->indexById($units);
        $activityMap = $this->indexById($allActivities);
        $filterUnitId = $this->resolveUnitId($selectedUnitSlug, $units);
        $filterActivityId = $this->resolveActivityId($selectedActivitySlug, $allActivities);

        $tBuilder = $db->table('transactions')
            ->where('institution_id', $institutionId)
            ->where('deleted_at', null)
            ->groupStart()
                ->where('to_account_id', $accountId)
                ->orWhere('from_account_id', $accountId)
            ->groupEnd();
            
        if ($filterPeriodId) $tBuilder->where('book_period_id', $filterPeriodId);
        if ($filterUnitId) $tBuilder->where('unit_id', $filterUnitId);
        if ($filterActivityId) $tBuilder->where('activity_id', $filterActivityId);

        $accOb = (float) ($db->table('opening_balances')->where('account_id', $accountId)->where('deleted_at', null)->selectSum('amount')->get()->getRow()->amount ?? 0);
        $accIncMasuk = (float) (clone $tBuilder)->where('type', 'masuk')->where('to_account_id', $accountId)->selectSum('amount')->get()->getRow()->amount ?? 0;
        $accIncPindah = (float) (clone $tBuilder)->where('type', 'pindah')->where('to_account_id', $accountId)->selectSum('amount')->get()->getRow()->amount ?? 0;
        $accInc = $accIncMasuk + $accIncPindah;

        $accExpMain = (float) (clone $tBuilder)->whereIn('type', ['keluar', 'honor'])->where('from_account_id', $accountId)->select('SUM(amount + admin_fee) as total')->get()->getRow()->total ?? 0;
        $accExpPindah = (float) (clone $tBuilder)->where('type', 'pindah')->where('from_account_id', $accountId)->select('SUM(amount + admin_fee) as total')->get()->getRow()->total ?? 0;
        $accExp = $accExpMain + $accExpPindah;

        $recentRows = (clone $tBuilder)->orderBy('transaction_date', 'DESC')->orderBy('transaction_time', 'DESC')->orderBy('id', 'DESC')->get()->getResultArray();
        $formattedTransactions = $this->transactionService->formatTransactions($recentRows);
        $accountTransactionPagination = paginate_items($formattedTransactions, $transactionPage, 10);
        
        $account = [
            'name' => $acc['name'],
            'balance' => $accOb + $accInc - $accExp,
            'income' => $accInc,
            'expense' => $accExp,
            'icon' => 'account_balance_wallet',
            'color' => 'emerald',
            'surplus' => $accInc - $accExp,
            'slug' => $acc['slug'] ?? ('acc-' . $accountId),
            'kind' => $acc['kind'] ?? 'Tunai',
            'mark' => $acc['mark'] ?? '',
            'account_number' => $acc['account_number'] ?? '',
            'logo_asset' => $acc['logo_asset'] ?? '',
            'note' => trim((string) ($acc['note'] ?? '')) !== '' ? (string) $acc['note'] : 'Rekening aktif',
            'movement_count' => count($recentRows),
            'transaction_count' => count($recentRows),
        ];

        $accountActivities = [];
        
        foreach ($recentRows as $row) {
            $isIncome = ($row['to_account_id'] == $accountId);
            $amount = $row['amount'] + ($isIncome ? 0 : $row['admin_fee']);
            $actId = $row['activity_id'];
            if ($actId) {
                if (!isset($accountActivities[$actId])) {
                    $a = $activityMap[(int) $actId] ?? null;
                    $u = is_array($a) ? ($unitMap[(int) $a['unit_id']] ?? null) : null;
                    $accountActivities[$actId] = [
                        'id' => (int) $actId,
                        'name' => $a['name'] ?? 'Tanpa Kegiatan',
                        'slug' => $a['slug'] ?? ('act-' . $actId),
                        'short_name' => $a['short_name'] ?? ($a['name'] ?? 'Kegiatan'),
                        'unit_name' => $u['name'] ?? 'Tanpa Unit',
                        'amount' => 0,
                        'income' => 0,
                        'expense' => 0,
                        'transfer_in' => 0,
                        'transfer_out' => 0,
                        'surplus' => 0,
                        'related_balance' => 0,
                        'related_accounts' => [$acc['name']],
                        'is_current' => false,
                        'detail_url' => is_array($a) ? route_query('kegiatan/' . ($a['slug'] ?? ('act-' . $actId))) : site_url('rekap'),
                    ];
                }
                $accountActivities[$actId]['amount'] += ($isIncome ? $amount : -$amount);
                if ((string) $row['type'] === 'pindah') {
                    if ($isIncome) {
                        $accountActivities[$actId]['transfer_in'] += (float) $row['amount'];
                    } else {
                        $accountActivities[$actId]['transfer_out'] += (float) $row['amount'] + (float) $row['admin_fee'];
                    }
                } elseif ($isIncome) {
                    $accountActivities[$actId]['income'] += $amount;
                } else {
                    $accountActivities[$actId]['expense'] += $amount;
                }
                $accountActivities[$actId]['surplus'] = $accountActivities[$actId]['income'] - $accountActivities[$actId]['expense'];
                $accountActivities[$actId]['related_balance'] = $accountActivities[$actId]['amount'];
            }
        }

        usort($accountActivities, fn($a, $b) => abs($b['amount']) <=> abs($a['amount']));
        $involvedReceivers = $this->getInvolvedReceivers($db, clone $tBuilder);

        $data = [
            'pageTitle'           => $account['name'],
            'activeNav'           => 'rekap',
            'backUrl'             => route_query('rekap', ['periode' => $selectedPeriodSlug, 'unit' => $selectedUnitSlug, 'kegiatan' => $selectedActivitySlug]),
            'account'             => $account,
            'accountTransactions' => $accountTransactionPagination['items'],
            'accountTransactionPagination' => $accountTransactionPagination,
            'involvedReceivers'   => $involvedReceivers,
            'accountActivities'   => $accountActivities,
        ];

        return view('pages/account_detail', $data);
    }

    public function unit(string $slug): string
    {
        $institutionId = $this->currentInstitutionId();
        $db = \Config\Database::connect();
        $units = $this->loadUnitProgramRows($institutionId);
        $allUnits = $this->loadAllUnitProgramRows($institutionId);
        $unitId = $this->resolveUnitId($slug, $allUnits);
        $u = $unitId === null
            ? null
            : $db->table('units')->where('id', $unitId)->where('institution_id', $institutionId)->where('deleted_at', null)->get()->getRowArray();
        
        if (! $u) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $request = service('request');
        $selectedPeriodSlug = $request->getGet('periode') ?: 'semua';
        $selectedActivitySlug = $request->getGet('kegiatan') ?: 'semua';
        $transactionPage = max(1, (int) ($request->getGet('transaksi_page') ?? 1));

        $filterPeriodId = $selectedPeriodSlug !== 'semua' ? (int) str_replace('period-', '', $selectedPeriodSlug) : null;
        $allActivities = $db->table('activities')->where('unit_id', $unitId)->where('deleted_at', null)->orderBy('id', 'ASC')->get()->getResultArray();
        $filterActivityId = $this->resolveActivityId($selectedActivitySlug, $allActivities);

        $tBuilder = $db->table('transactions')
            ->where('institution_id', $institutionId)
            ->where('unit_id', $unitId)
            ->where('deleted_at', null);

        if ($filterPeriodId) $tBuilder->where('book_period_id', $filterPeriodId);
        if ($filterActivityId) $tBuilder->where('activity_id', $filterActivityId);

        $uInc = (float) (clone $tBuilder)->where('type', 'masuk')->selectSum('amount')->get()->getRow()->amount ?? 0;
        $uExpMain = (float) (clone $tBuilder)->whereIn('type', ['keluar', 'honor'])->select('SUM(amount + admin_fee) as total')->get()->getRow()->total ?? 0;
        $uExpPindah = (float) (clone $tBuilder)->where('type', 'pindah')->selectSum('admin_fee')->get()->getRow()->admin_fee ?? 0;
        $uExp = $uExpMain + $uExpPindah;
        
        $uActivities = $allActivities;
        $formattedActivities = [];

        foreach ($uActivities as $act) {
            $actBuilder = (clone $tBuilder)->where('activity_id', $act['id']);
            
            $actInc = (float) (clone $actBuilder)->where('type', 'masuk')->selectSum('amount')->get()->getRow()->amount ?? 0;
            $actExpMain = (float) (clone $actBuilder)->whereIn('type', ['keluar', 'honor'])->select('SUM(amount + admin_fee) as total')->get()->getRow()->total ?? 0;
            $actExpPindah = (float) (clone $actBuilder)->where('type', 'pindah')->selectSum('admin_fee')->get()->getRow()->admin_fee ?? 0;
            $actExp = $actExpMain + $actExpPindah;

            $formattedActivities[] = [
                'id' => $act['id'],
                'slug' => $act['slug'] ?? ('act-' . $act['id']),
                'name' => $act['name'],
                'short_name' => substr($act['name'], 0, 4),
                'unit_name' => $u['name'],
                'income' => $actInc,
                'expense' => $actExp,
                'surplus' => $actInc - $actExp,
                'related_balance' => $actInc - $actExp,
                'detail_url' => route_query('kegiatan/' . ($act['slug'] ?? ('act-' . $act['id'])), ['periode' => $selectedPeriodSlug, 'unit' => $u['slug']]),
                'masuk_url' => route_query('catat/masuk', ['unit' => $u['slug'], 'kegiatan' => $act['slug'] ?? null]),
                'keluar_url' => route_query('catat/keluar', ['unit' => $u['slug'], 'kegiatan' => $act['slug'] ?? null]),
            ];
        }

        $unit = [
            'slug' => $u['slug'],
            'name' => $u['name'],
            'short_name' => substr($u['name'], 0, 4),
            'income' => $uInc,
            'expense' => $uExp,
            'surplus' => $uInc - $uExp,
            'related_balance' => $uInc - $uExp,
            'activities' => $formattedActivities,
            'quick_activity_name' => $uActivities[0]['name'] ?? '-',
            'detail_url' => site_url('unit/' . $u['slug']),
        ];

        $recentRows = (clone $tBuilder)->orderBy('transaction_date', 'DESC')->orderBy('transaction_time', 'DESC')->orderBy('id', 'DESC')->get()->getResultArray();
        $involvedReceivers = $this->getInvolvedReceivers($db, clone $tBuilder);
        $involvedAccounts = $this->getInvolvedAccounts($db, $recentRows);
        $unitTransactionPagination = paginate_items($this->transactionService->formatTransactions($recentRows), $transactionPage, 10);
        
        $data = [
            'pageTitle' => $unit['name'],
            'activeNav' => 'rekap',
            'backUrl' => site_url('rekap'),
            'unit' => $unit,
            'unitTransactions' => $unitTransactionPagination['items'],
            'unitTransactionPagination' => $unitTransactionPagination,
            'involvedReceivers' => $involvedReceivers,
            'involvedAccounts' => $involvedAccounts,
        ];

        return view('pages/unit_detail', $data);
    }

    public function bagikanUnit(string $slug): string
    {
        $institutionId = $this->currentInstitutionId();
        $db = \Config\Database::connect();
        $allUnits = $this->loadAllUnitProgramRows($institutionId);
        $unitId = $this->resolveUnitId($slug, $allUnits);
        $unit = $unitId === null
            ? null
            : $db->table('units')->where('id', $unitId)->where('institution_id', $institutionId)->where('deleted_at', null)->get()->getRowArray();

        if (! $unit) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        return view('pages/share/unit_share_setup', [
            'pageTitle' => 'Bagikan Laporan Unit',
            'activeNav' => 'rekap',
            'backUrl' => site_url('unit/' . $unit['slug']),
            'unit' => [
                'slug' => $unit['slug'],
                'name' => $unit['name'],
                'short_name' => substr($unit['name'], 0, 4),
            ],
            'shareUrl' => site_url('laporan/unit/' . $unit['slug']),
            'demoPin' => '240519',
        ]);
    }

    public function laporanUnitPublik(string $slug): string
    {
        $institutionId = $this->currentInstitutionId();
        $db = \Config\Database::connect();

        if ($institutionId) {
            $allUnits = $this->loadAllUnitProgramRows($institutionId);
            $unitId = $this->resolveUnitId($slug, $allUnits);
            $unit = $unitId === null
                ? null
                : $db->table('units')->where('id', $unitId)->where('institution_id', $institutionId)->where('deleted_at', null)->get()->getRowArray();
        } else {
            $unit = $db->table('units')->where('slug', $slug)->where('deleted_at', null)->get()->getRowArray();
        }

        if (! $unit) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $unlocked = $this->request->getGet('preview') === '1';
        $selectedTransactionFilter = strtolower((string) ($this->request->getGet('jenis') ?? 'semua'));
        if (! in_array($selectedTransactionFilter, ['semua', 'masuk', 'keluar', 'honor', 'pindah'], true)) {
            $selectedTransactionFilter = 'semua';
        }
        $transactionPage = max(1, (int) ($this->request->getGet('transaksi_page') ?? 1));
        $selectedActivitySlug = (string) ($this->request->getGet('kegiatan') ?? '');
        $billPreviewUrl = base_url('images/logo-primary-2.webp');

        $reportActivities = [
            [
                'slug' => 'operasional-simpaud-2026',
                'name' => 'Operasional SIMPAUD 2026',
                'short_name' => 'OPER',
                'unit_name' => $unit['name'],
                'income' => 52000000,
                'expense' => 34150000,
                'surplus' => 17850000,
                'related_balance' => 17850000,
                'related_accounts' => ['BCA Operasional', 'Kas Tunai'],
                'detail_url' => current_url(),
                'transaction_count' => 28,
                'receiver_count' => 5,
                'account_count' => 2,
            ],
            [
                'slug' => 'simpaud-web-app-mobile-first-transformation',
                'name' => 'SIMPAUD Web App Mobile First Transformation',
                'short_name' => 'SIMP',
                'unit_name' => $unit['name'],
                'income' => 38500000,
                'expense' => 27400000,
                'surplus' => 11100000,
                'related_balance' => 11100000,
                'related_accounts' => ['BCA Operasional'],
                'detail_url' => current_url(),
                'transaction_count' => 21,
                'receiver_count' => 4,
                'account_count' => 1,
            ],
            [
                'slug' => 'kemitraan-publikasi',
                'name' => 'Kemitraan & Publikasi',
                'short_name' => 'KEMP',
                'unit_name' => $unit['name'],
                'income' => 37000000,
                'expense' => 27700000,
                'surplus' => 9300000,
                'related_balance' => 9300000,
                'related_accounts' => ['Kas Tunai'],
                'detail_url' => current_url(),
                'transaction_count' => 35,
                'receiver_count' => 5,
                'account_count' => 1,
            ],
        ];

        $allowedActivitySlugs = array_column($reportActivities, 'slug');
        if ($selectedActivitySlug !== '' && ! in_array($selectedActivitySlug, $allowedActivitySlugs, true)) {
            $selectedActivitySlug = '';
        }

        $reportTransactions = [
            [
                'id' => '9001',
                'badge_label' => 'Biaya',
                'badge_class' => 'bg-rose-50 text-rose-600',
                'icon' => 'north_east',
                'headline' => 'Operasional dari BCA Operasional',
                'subline' => $unit['name'] . ' / Operasional SIMPAUD 2026',
                'meta' => '17 Mei 2026 · Pembayaran vendor operasional',
                'amount' => 500000,
                'amount_prefix' => '-',
                'amount_class' => 'text-rose-600',
                'admin_fee' => 2500,
                'type_key' => 'keluar',
                'activity_slug' => 'operasional-simpaud-2026',
                'bill_preview_url' => $billPreviewUrl,
            ],
            [
                'id' => '9002',
                'badge_label' => 'Masuk',
                'badge_class' => 'bg-emerald-50 text-emerald-700',
                'icon' => 'south',
                'headline' => 'Pelatihan ke BCA Operasional',
                'subline' => $unit['name'] . ' / SIMPAUD Web App Mobile First Transformation',
                'meta' => '16 Mei 2026 · Pemasukan program pelatihan',
                'amount' => 8000000,
                'amount_prefix' => '+',
                'amount_class' => 'text-emerald-600',
                'admin_fee' => 6500,
                'type_key' => 'masuk',
                'activity_slug' => 'simpaud-web-app-mobile-first-transformation',
                'bill_preview_url' => null,
            ],
            [
                'id' => '9003',
                'badge_label' => 'Honor',
                'badge_class' => 'bg-orange-50 text-orange-700',
                'icon' => 'payments',
                'headline' => 'Honor untuk Tim Internal',
                'subline' => $unit['name'] . ' / Kemitraan & Publikasi',
                'meta' => '15 Mei 2026 · Honor tim internal',
                'amount' => 1500000,
                'amount_prefix' => '-',
                'amount_class' => 'text-rose-600',
                'admin_fee' => 2500,
                'type_key' => 'honor',
                'receiver_name' => 'Tim Internal',
                'activity_slug' => 'kemitraan-publikasi',
                'bill_preview_url' => $billPreviewUrl,
            ],
            [
                'id' => '9004',
                'badge_label' => 'Pindah Dana',
                'badge_class' => 'bg-sky-50 text-sky-700',
                'icon' => 'sync_alt',
                'headline' => 'BCA Operasional ke Kas Tunai',
                'subline' => $unit['name'] . ' / Operasional SIMPAUD 2026',
                'meta' => '14 Mei 2026 · Pindah dana operasional',
                'amount' => 2500000,
                'amount_prefix' => '-',
                'amount_class' => 'text-rose-600',
                'admin_fee' => 1000,
                'type_key' => 'pindah',
                'activity_slug' => 'operasional-simpaud-2026',
                'bill_preview_url' => null,
            ],
            [
                'id' => '9005',
                'badge_label' => 'Biaya',
                'badge_class' => 'bg-rose-50 text-rose-600',
                'icon' => 'north_east',
                'headline' => 'Iklan dari BCA Operasional',
                'subline' => $unit['name'] . ' / Kemitraan & Publikasi',
                'meta' => '13 Mei 2026 · Iklan promosi',
                'amount' => 750000,
                'amount_prefix' => '-',
                'amount_class' => 'text-rose-600',
                'admin_fee' => 2500,
                'type_key' => 'keluar',
                'activity_slug' => 'kemitraan-publikasi',
                'bill_preview_url' => $billPreviewUrl,
            ],
            [
                'id' => '9006',
                'badge_label' => 'Masuk',
                'badge_class' => 'bg-emerald-50 text-emerald-700',
                'icon' => 'south',
                'headline' => 'Kemitraan ke Kas Tunai',
                'subline' => $unit['name'] . ' / Kemitraan & Publikasi',
                'meta' => '12 Mei 2026 · Dana kemitraan',
                'amount' => 4200000,
                'amount_prefix' => '+',
                'amount_class' => 'text-emerald-600',
                'admin_fee' => 0,
                'type_key' => 'masuk',
                'activity_slug' => 'kemitraan-publikasi',
                'bill_preview_url' => null,
            ],
            [
                'id' => '9007',
                'badge_label' => 'Honor',
                'badge_class' => 'bg-orange-50 text-orange-700',
                'icon' => 'payments',
                'headline' => 'Honor untuk Narasumber',
                'subline' => $unit['name'] . ' / Operasional SIMPAUD 2026',
                'meta' => '11 Mei 2026 · Honor narasumber',
                'amount' => 900000,
                'amount_prefix' => '-',
                'amount_class' => 'text-rose-600',
                'admin_fee' => 0,
                'type_key' => 'honor',
                'receiver_name' => 'Narasumber',
                'activity_slug' => 'operasional-simpaud-2026',
                'bill_preview_url' => $billPreviewUrl,
            ],
            [
                'id' => '9008',
                'badge_label' => 'Biaya',
                'badge_class' => 'bg-rose-50 text-rose-600',
                'icon' => 'north_east',
                'headline' => 'Internet dari BCA Operasional',
                'subline' => $unit['name'] . ' / SIMPAUD Web App Mobile First Transformation',
                'meta' => '10 Mei 2026 · Langganan internet',
                'amount' => 350000,
                'amount_prefix' => '-',
                'amount_class' => 'text-rose-600',
                'admin_fee' => 0,
                'type_key' => 'keluar',
                'activity_slug' => 'simpaud-web-app-mobile-first-transformation',
                'bill_preview_url' => $billPreviewUrl,
            ],
            [
                'id' => '9009',
                'badge_label' => 'Masuk',
                'badge_class' => 'bg-emerald-50 text-emerald-700',
                'icon' => 'south',
                'headline' => 'Program ke BCA Operasional',
                'subline' => $unit['name'] . ' / Operasional SIMPAUD 2026',
                'meta' => '09 Mei 2026 · Dana program',
                'amount' => 6700000,
                'amount_prefix' => '+',
                'amount_class' => 'text-emerald-600',
                'admin_fee' => 0,
                'type_key' => 'masuk',
                'activity_slug' => 'operasional-simpaud-2026',
                'bill_preview_url' => null,
            ],
            [
                'id' => '9010',
                'badge_label' => 'Pindah Dana',
                'badge_class' => 'bg-sky-50 text-sky-700',
                'icon' => 'sync_alt',
                'headline' => 'Kas Tunai ke BCA Operasional',
                'subline' => $unit['name'] . ' / Kemitraan & Publikasi',
                'meta' => '08 Mei 2026 · Penyesuaian kas',
                'amount' => 1200000,
                'amount_prefix' => '-',
                'amount_class' => 'text-rose-600',
                'admin_fee' => 0,
                'type_key' => 'pindah',
                'activity_slug' => 'kemitraan-publikasi',
                'bill_preview_url' => null,
            ],
            [
                'id' => '9011',
                'badge_label' => 'Biaya',
                'badge_class' => 'bg-rose-50 text-rose-600',
                'icon' => 'north_east',
                'headline' => 'ATK dari Kas Tunai',
                'subline' => $unit['name'] . ' / Operasional SIMPAUD 2026',
                'meta' => '07 Mei 2026 · ATK operasional',
                'amount' => 210000,
                'amount_prefix' => '-',
                'amount_class' => 'text-rose-600',
                'admin_fee' => 0,
                'type_key' => 'keluar',
                'activity_slug' => 'operasional-simpaud-2026',
                'bill_preview_url' => $billPreviewUrl,
            ],
            [
                'id' => '9012',
                'badge_label' => 'Masuk',
                'badge_class' => 'bg-emerald-50 text-emerald-700',
                'icon' => 'south',
                'headline' => 'Pelatihan ke Kas Tunai',
                'subline' => $unit['name'] . ' / SIMPAUD Web App Mobile First Transformation',
                'meta' => '06 Mei 2026 · Pemasukan pelatihan',
                'amount' => 5300000,
                'amount_prefix' => '+',
                'amount_class' => 'text-emerald-600',
                'admin_fee' => 0,
                'type_key' => 'masuk',
                'activity_slug' => 'simpaud-web-app-mobile-first-transformation',
                'bill_preview_url' => null,
            ],
        ];

        $countHonorReceivers = static function (array $transactions, ?string $activitySlug = null): int {
            $receivers = [];

            foreach ($transactions as $transaction) {
                if (($transaction['type_key'] ?? '') !== 'honor') {
                    continue;
                }

                if ($activitySlug !== null && ($transaction['activity_slug'] ?? '') !== $activitySlug) {
                    continue;
                }

                $receiverName = trim((string) ($transaction['receiver_name'] ?? ''));
                if ($receiverName === '') {
                    continue;
                }

                $receivers[strtolower($receiverName)] = true;
            }

            return count($receivers);
        };

        foreach ($reportActivities as &$activity) {
            $activity['receiver_count'] = $countHonorReceivers($reportTransactions, $activity['slug']);
        }
        unset($activity);

        $scopeActivity = null;
        foreach ($reportActivities as &$activity) {
            $activity['is_selected'] = $selectedActivitySlug !== '' && $activity['slug'] === $selectedActivitySlug;
            $activity['detail_url'] = route_query('laporan/unit/' . $unit['slug'], [
                'preview' => 1,
                'kegiatan' => $activity['slug'],
                'jenis' => $selectedTransactionFilter === 'semua' ? null : $selectedTransactionFilter,
                'transaksi_page' => null,
            ]);

            if ($activity['is_selected']) {
                $scopeActivity = $activity;
            }
        }
        unset($activity);

        $scopeSummary = [
            'period' => 'Januari - Desember 2026',
            'income' => 127500000,
            'expense' => 89250000,
            'surplus' => 38250000,
            'balance' => 45600000,
        ];
        $scopeTitle = $unit['name'];
        $scopeMode = 'unit';
        $scopeHighlights = [
            ['label' => 'Penerima Honor', 'value' => (string) $countHonorReceivers($reportTransactions) . ' penerima'],
            ['label' => 'Rekening Terlibat', 'value' => '3 rekening'],
            ['label' => 'Transaksi Tercatat', 'value' => '84 transaksi'],
        ];

        if (is_array($scopeActivity)) {
            $scopeSummary = [
                'period' => 'Januari - Desember 2026',
                'income' => (float) $scopeActivity['income'],
                'expense' => (float) $scopeActivity['expense'],
                'surplus' => (float) $scopeActivity['surplus'],
                'balance' => (float) $scopeActivity['related_balance'],
            ];
            $scopeTitle = $scopeActivity['name'];
            $scopeMode = 'kegiatan';
            $scopeHighlights = [
                ['label' => 'Transaksi Tercatat', 'value' => (string) ($scopeActivity['transaction_count'] ?? 0) . ' transaksi'],
                ['label' => 'Penerima Honor', 'value' => (string) ($scopeActivity['receiver_count'] ?? 0) . ' penerima'],
                ['label' => 'Rekening Terlibat', 'value' => (string) ($scopeActivity['account_count'] ?? 0) . ' rekening'],
            ];
            $reportTransactions = array_values(array_filter(
                $reportTransactions,
                static fn(array $transaction): bool => ($transaction['activity_slug'] ?? '') === ($scopeActivity['slug'] ?? '')
            ));
        }

        $filteredPublicTransactions = $selectedTransactionFilter === 'semua'
            ? $reportTransactions
            : array_values(array_filter(
                $reportTransactions,
                static fn(array $transaction): bool => ($transaction['type_key'] ?? '') === $selectedTransactionFilter
            ));

        $publicTransactionPagination = paginate_items($filteredPublicTransactions, $transactionPage, 10);

        return view('pages/share/unit_public_report', [
            'pageTitle' => 'Laporan Unit',
            'unit' => [
                'slug' => $unit['slug'],
                'name' => $unit['name'],
                'short_name' => substr($unit['name'], 0, 4),
            ],
            'shareUrl' => current_url(),
            'demoPin' => '240519',
            'unlocked' => $unlocked,
            'reportSummary' => $scopeSummary,
            'reportHighlights' => $scopeHighlights,
            'reportUnitCard' => [
                'slug' => $unit['slug'],
                'name' => $unit['name'],
                'short_name' => substr($unit['name'], 0, 4),
                'income' => 127500000,
                'expense' => 89250000,
                'surplus' => 38250000,
                'related_balance' => 45600000,
                'detail_url' => route_query('laporan/unit/' . $unit['slug'], [
                    'preview' => 1,
                    'kegiatan' => null,
                    'jenis' => $selectedTransactionFilter === 'semua' ? null : $selectedTransactionFilter,
                    'transaksi_page' => null,
                ]),
                'activities' => [1, 2, 3, 4, 5, 6],
            ],
            'reportActivities' => $reportActivities,
            'transactionFilters' => [
                ['key' => 'semua', 'label' => 'Semua'],
                ['key' => 'masuk', 'label' => 'Masuk'],
                ['key' => 'keluar', 'label' => 'Biaya'],
                ['key' => 'honor', 'label' => 'Honor'],
                ['key' => 'pindah', 'label' => 'Pindah Dana'],
            ],
            'selectedTransactionFilter' => $selectedTransactionFilter,
            'selectedActivitySlug' => $selectedActivitySlug,
            'scopeMode' => $scopeMode,
            'scopeTitle' => $scopeTitle,
            'scopeResetUrl' => route_query('laporan/unit/' . $unit['slug'], [
                'preview' => 1,
                'kegiatan' => null,
                'jenis' => $selectedTransactionFilter === 'semua' ? null : $selectedTransactionFilter,
                'transaksi_page' => null,
            ]),
            'reportTransactions' => $publicTransactionPagination['items'],
            'reportTransactionPagination' => $publicTransactionPagination,
        ]);
    }

    public function kegiatan(string $slug): string
    {
        $context = $this->findActivityContextOrFail($slug);
        $institutionId = $context['institution_id'];
        $db = $context['db'];
        $activityId = (int) $context['activity']['id'];
        $act = $context['activity'];
        $unit = $context['unit'];
        $units = $context['units'];
        $request = service('request');
        $selectedPeriodSlug = $request->getGet('periode') ?: 'semua';
        $selectedUnitSlug = $request->getGet('unit') ?: 'semua';
        $transactionPage = max(1, (int) ($request->getGet('transaksi_page') ?? 1));
        $transferPage = max(1, (int) ($request->getGet('mutasi_page') ?? 1));

        $filterPeriodId = $selectedPeriodSlug !== 'semua' ? (int) str_replace('period-', '', $selectedPeriodSlug) : null;
        $filterUnitId = $this->resolveUnitId($selectedUnitSlug, $units);

        $tBuilder = $db->table('transactions')
            ->where('institution_id', $institutionId)
            ->where('activity_id', $activityId)
            ->where('deleted_at', null);

        if ($filterPeriodId) $tBuilder->where('book_period_id', $filterPeriodId);
        if ($filterUnitId) $tBuilder->where('unit_id', $filterUnitId);

        $actInc = (float) (clone $tBuilder)->where('type', 'masuk')->selectSum('amount')->get()->getRow()->amount ?? 0;
        $actExpMain = (float) (clone $tBuilder)->whereIn('type', ['keluar', 'honor'])->select('SUM(amount + admin_fee) as total')->get()->getRow()->total ?? 0;
        $actExpPindah = (float) (clone $tBuilder)->where('type', 'pindah')->selectSum('admin_fee')->get()->getRow()->admin_fee ?? 0;
        $actExp = $actExpMain + $actExpPindah;

        $activity = [
            'id' => $activityId,
            'slug' => $act['slug'] ?? ('act-' . $activityId),
            'name' => $act['name'],
            'short_name' => $act['short_name'] ?? substr($act['name'], 0, 4),
            'unit_name' => $unit['name'] ?? '',
            'income' => $actInc,
            'expense' => $actExp,
            'surplus' => $actInc - $actExp,
            'related_accounts' => [],
            'related_balance' => $actInc - $actExp,
            'masuk_url' => route_query('catat/masuk', ['unit' => $unit['slug'] ?? null, 'kegiatan' => $act['slug'] ?? null]),
            'keluar_url' => route_query('catat/keluar', ['unit' => $unit['slug'] ?? null, 'kegiatan' => $act['slug'] ?? null]),
        ];

        if ($this->projectPocketService->hasProjectMode($institutionId, $activityId)) {
            return view('pages/project_activity_detail', $this->buildProjectActivityDetailData(
                $activity,
                $unit,
                clone $tBuilder,
                $selectedPeriodSlug,
                $selectedUnitSlug
            ));
        }

        $recentRows = (clone $tBuilder)->orderBy('transaction_date', 'DESC')->orderBy('transaction_time', 'DESC')->orderBy('id', 'DESC')->get()->getResultArray();
        $formattedTransactions = $this->transactionService->formatTransactions($recentRows);
        $involvedAccounts = $this->getInvolvedAccounts($db, $recentRows);
        $activity['related_accounts'] = array_column($involvedAccounts, 'name');
        
        $activityTransactions = [];
        $transferItems = [];
        $categoryBreakdownMap = [];
        
        foreach ($formattedTransactions as $item) {
            if ($item['type_key'] === 'pindah') {
                $transferItems[] = $item;
            } else {
                $activityTransactions[] = $item;
            }
            if ($item['type_key'] === 'keluar') {
                $catName = $item['category'] ?: 'Lainnya';
                if (!isset($categoryBreakdownMap[$catName])) {
                    $categoryBreakdownMap[$catName] = 0;
                }
                $categoryBreakdownMap[$catName] += $item['amount'];
            }
        }

        $categoryBreakdown = [];
        foreach ($categoryBreakdownMap as $name => $amount) {
            $categoryBreakdown[] = [
                'category_name' => $name,
                'total_amount' => $amount,
                'percentage' => $actExp > 0 ? ($amount / $actExp) * 100 : 0,
            ];
        }

        usort($categoryBreakdown, fn($a, $b) => $b['total_amount'] <=> $a['total_amount']);
        $activityTransactionPagination = paginate_items($activityTransactions, $transactionPage, 10);
        $activityTransferPagination = paginate_items($transferItems, $transferPage, 10);

        $data = [
            'pageTitle' => $activity['name'],
            'activeNav' => 'rekap',
            'backUrl' => site_url('rekap'),
            'activity' => $activity,
            'projectActivationUrl' => site_url('kegiatan/' . $activity['slug'] . '/proyek'),
            'activityTransactions' => $activityTransactionPagination['items'],
            'activityTransactionPagination' => $activityTransactionPagination,
            'transferItems' => $activityTransferPagination['items'],
            'activityTransferPagination' => $activityTransferPagination,
            'categoryBreakdown' => $categoryBreakdown,
            'involvedReceivers' => $this->getInvolvedReceivers($db, clone $tBuilder),
            'involvedAccounts' => $involvedAccounts,
        ];

        return view('pages/activity_detail', $data);
    }

    public function updateProyekKegiatan(string $slug): RedirectResponse
    {
        $context = $this->findActivityContextOrFail($slug);
        $mainPocket = $this->projectPocketService->ensureMainPocket($context['institution_id'], $context['activity']);

        $this->projectPocketService->updatePocket($mainPocket, [
            'notes' => (string) $this->request->getPost('notes'),
            'contract_value' => (string) $this->request->getPost('contract_value'),
            'contract_terms_count' => (int) $this->request->getPost('contract_terms_count'),
        ]);

        return redirect()->to(site_url('kegiatan/' . $context['activity']['slug']))
            ->with('success', 'Pengaturan proyek berhasil diperbarui.');
    }

    public function simpanKantongKegiatan(string $slug): RedirectResponse
    {
        $context = $this->findActivityContextOrFail($slug);

        try {
            $this->projectPocketService->createExecutionPocket($context['institution_id'], $context['activity'], [
                'name' => (string) $this->request->getPost('name'),
                'notes' => (string) $this->request->getPost('notes'),
                'is_active' => 1,
            ]);
        } catch (\RuntimeException $e) {
            return redirect()->back()->withInput()->with('warning', $e->getMessage());
        }

        return redirect()->to(site_url('kegiatan/' . $context['activity']['slug']))
            ->with('success', 'Kantong pelaksanaan berhasil ditambahkan.');
    }

    public function kantongKegiatan(string $slug, string $pocketSlug): string
    {
        $context = $this->findActivityContextOrFail($slug);
        $pocket = $this->projectPocketService->findPocket($context['institution_id'], (int) $context['activity']['id'], $pocketSlug);

        if (! is_array($pocket)) {
            throw PageNotFoundException::forPageNotFound();
        }

        return view('pages/project_pocket_detail', $this->buildProjectPocketDetailData(
            $context['activity'],
            $context['unit'],
            $pocket
        ));
    }

    public function updateKantongKegiatan(string $slug, string $pocketSlug): RedirectResponse
    {
        $context = $this->findActivityContextOrFail($slug);
        $pocket = $this->projectPocketService->findPocket($context['institution_id'], (int) $context['activity']['id'], $pocketSlug);

        if (! is_array($pocket)) {
            throw PageNotFoundException::forPageNotFound();
        }

        try {
            $updatedPocket = $this->projectPocketService->updatePocket($pocket, [
                'name' => (string) $this->request->getPost('name'),
                'notes' => (string) $this->request->getPost('notes'),
                'contract_value' => (string) $this->request->getPost('contract_value'),
                'contract_terms_count' => (int) $this->request->getPost('contract_terms_count'),
            ]);
        } catch (\RuntimeException $e) {
            return redirect()->back()->withInput()->with('warning', $e->getMessage());
        }

        return redirect()->to(site_url('kegiatan/' . $context['activity']['slug'] . '/kantong/' . $updatedPocket['slug']))
            ->with('success', 'Kantong berhasil diperbarui.');
    }

    public function nonaktifkanKantongKegiatan(string $slug, string $pocketSlug): RedirectResponse
    {
        $context = $this->findActivityContextOrFail($slug);
        $pocket = $this->projectPocketService->findPocket($context['institution_id'], (int) $context['activity']['id'], $pocketSlug);

        if (! is_array($pocket)) {
            throw PageNotFoundException::forPageNotFound();
        }

        try {
            $this->projectPocketService->deactivatePocket($pocket);
        } catch (\RuntimeException $e) {
            return redirect()->back()->with('warning', $e->getMessage());
        }

        return redirect()->to(site_url('kegiatan/' . $context['activity']['slug']))
            ->with('success', 'Kantong pelaksanaan dinonaktifkan.');
    }

    public function penerima(string $slug): string
    {
        $institutionId = $this->currentInstitutionId();
        $db = \Config\Database::connect();

        $receiver = $db->table('receivers')
            ->where('institution_id', $institutionId)
            ->where('deleted_at', null)
            ->where('id', (int) $slug)
            ->get()
            ->getRowArray();

        if (! is_array($receiver)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $receiverId = (int) $receiver['id'];
        $transactionPage = max(1, (int) ($this->request->getGet('transaksi_page') ?? 1));
        $tBuilder = $db->table('transactions')
            ->where('institution_id', $institutionId)
            ->where('receiver_id', $receiverId)
            ->where('deleted_at', null);

        $recentRows = (clone $tBuilder)
            ->orderBy('transaction_date', 'DESC')
            ->orderBy('transaction_time', 'DESC')
            ->orderBy('id', 'DESC')
            ->get()
            ->getResultArray();

        $formattedTransactions = $this->transactionService->formatTransactions($recentRows);
        $receiverTransactionPagination = paginate_items($formattedTransactions, $transactionPage, 10);
        $involvedAccounts = $this->getInvolvedAccounts($db, $recentRows);
        $involvedActivities = $this->getInvolvedActivities($db, $recentRows);

        $data = [
            'pageTitle' => $receiver['name'],
            'activeNav' => 'rekap',
            'backUrl' => site_url('rekap'),
            'receiver' => [
                'id' => $receiverId,
                'name' => $receiver['name'],
                'type' => $receiver['type'] ?? 'Lainnya',
                'notes' => trim((string) ($receiver['notes'] ?? '')),
                'total_amount' => (float) ((clone $tBuilder)->select('SUM(amount + admin_fee) as total')->get()->getRow()->total ?? 0),
                'transaction_count' => count($recentRows),
            ],
            'receiverActivities' => $involvedActivities,
            'receiverAccounts' => $involvedAccounts,
            'receiverTransactions' => $receiverTransactionPagination['items'],
            'receiverTransactionPagination' => $receiverTransactionPagination,
        ];

        return view('pages/receiver_detail', $data);
    }

    private function findActivityContextOrFail(string $slug): array
    {
        $institutionId = $this->currentInstitutionId();
        $db = \Config\Database::connect();
        $units = $this->loadUnitProgramRows($institutionId);
        $allUnits = $this->loadAllUnitProgramRows($institutionId);
        $allUnitIds = array_column($allUnits, 'id');
        $allActivities = $allUnitIds === []
            ? []
            : $db->table('activities')
                ->whereIn('unit_id', $allUnitIds)
                ->where('deleted_at', null)
                ->get()
                ->getResultArray();
        $activityId = $this->resolveActivityId($slug, $allActivities);
        $activity = $activityId === null
            ? null
            : $db->table('activities')->where('id', $activityId)->where('deleted_at', null)->get()->getRowArray();

        if (! is_array($activity)) {
            throw PageNotFoundException::forPageNotFound();
        }

        $unit = $db->table('units')
            ->where('id', $activity['unit_id'])
            ->where('institution_id', $institutionId)
            ->where('deleted_at', null)
            ->get()
            ->getRowArray();

        if (! is_array($unit)) {
            throw PageNotFoundException::forPageNotFound();
        }

        return [
            'institution_id' => $institutionId,
            'db' => $db,
            'units' => $units,
            'all_units' => $allUnits,
            'activity' => $activity,
            'unit' => $unit,
        ];
    }

    private function buildProjectActivityDetailData(array $activity, array $unit, BaseBuilder $tBuilder, string $selectedPeriodSlug, string $selectedUnitSlug): array
    {
        $institutionId = $this->currentInstitutionId();
        $mainPocket = $this->projectPocketService->ensureMainPocket($institutionId, [
            'id' => $activity['id'] ?? 0,
            'unit_id' => $unit['id'] ?? 0,
        ]);
        $pockets = $this->projectPocketService->getByActivity($institutionId, (int) $activity['id'], false);
        $recentRows = (clone $tBuilder)
            ->orderBy('transaction_date', 'DESC')
            ->orderBy('transaction_time', 'DESC')
            ->orderBy('id', 'DESC')
            ->get()
            ->getResultArray();
        $formattedTransactions = $this->transactionService->formatTransactions($recentRows);
        $transactionPage = max(1, (int) ($this->request->getGet('transaksi_page') ?? 1));
        $projectTransactionPagination = paginate_items($formattedTransactions, $transactionPage, 10);

        $pocketCards = [];
        $totalPocketBalance = 0.0;
        $mainPocketSummary = null;
        foreach ($pockets as $pocket) {
            $summary = $this->summarizePocketRows($recentRows, (int) $pocket['id']);
            $totalPocketBalance += (float) $summary['balance'];

            if ((string) ($pocket['pocket_type'] ?? '') === 'main') {
                $mainPocketSummary = $summary;
            }

            $pocketCards[] = [
                'id' => (int) $pocket['id'],
                'name' => (string) $pocket['name'],
                'slug' => (string) $pocket['slug'],
                'pocket_type' => (string) $pocket['pocket_type'],
                'type_label' => (string) (($pocket['pocket_type'] ?? '') === 'main' ? 'Kantong Utama' : 'Pelaksanaan'),
                'notes' => trim((string) ($pocket['notes'] ?? '')),
                'is_active' => (int) ($pocket['is_active'] ?? 0) === 1,
                'balance' => (float) $summary['balance'],
                'income' => (float) $summary['income'],
                'expense' => (float) $summary['expense'],
                'transfer_in' => (float) $summary['transfer_in'],
                'transfer_out' => (float) $summary['transfer_out'],
                'transaction_count' => (int) $summary['transaction_count'],
                'detail_url' => site_url('kegiatan/' . $activity['slug'] . '/kantong/' . $pocket['slug']),
                'edit_url' => site_url('kegiatan/' . $activity['slug'] . '/kantong/' . $pocket['slug']),
                'deactivate_url' => site_url('kegiatan/' . $activity['slug'] . '/kantong/' . $pocket['slug'] . '/nonaktif'),
                'short_name' => (string) (($pocket['pocket_type'] ?? '') === 'main' ? 'UTM' : substr((string) $pocket['name'], 0, 4)),
                'unit_name' => $activity['name'],
                'related_balance' => (float) $summary['balance'],
                'surplus' => (float) $summary['balance'],
                'detail_badge' => (string) (($pocket['pocket_type'] ?? '') === 'main' ? 'Kontrak' : 'Operasional'),
            ];
        }

        $mainPocketSummary = $mainPocketSummary ?? $this->summarizePocketRows($recentRows, (int) ($mainPocket['id'] ?? 0));
        $contractValue = (float) ($mainPocket['contract_value'] ?? 0);
        $terminCair = (float) ($mainPocketSummary['contract_income'] ?? 0);

        return [
            'pageTitle' => $activity['name'],
            'activeNav' => 'rekap',
            'backUrl' => site_url('rekap'),
            'activity' => $activity,
            'unit' => $unit,
            'projectSummary' => [
                'contract_value' => $contractValue,
                'contract_terms_count' => (int) ($mainPocket['contract_terms_count'] ?? 0),
                'termin_cair' => $terminCair,
                'outstanding' => $contractValue - $terminCair,
                'total_pocket_balance' => $totalPocketBalance,
                'transaction_count' => count($recentRows),
                'execution_pocket_count' => count(array_filter($pockets, static fn(array $pocket): bool => ($pocket['pocket_type'] ?? '') === 'execution' && (int) ($pocket['is_active'] ?? 0) === 1)),
            ],
            'mainPocket' => $mainPocket,
            'pocketCards' => $pocketCards,
            'projectTransactions' => $projectTransactionPagination['items'],
            'projectTransactionPagination' => $projectTransactionPagination,
            'projectFilters' => [
                'periode' => $selectedPeriodSlug,
                'unit' => $selectedUnitSlug,
            ],
        ];
    }

    private function buildProjectPocketDetailData(array $activity, array $unit, array $pocket): array
    {
        $db = \Config\Database::connect();
        $transactionPage = max(1, (int) ($this->request->getGet('transaksi_page') ?? 1));
        $transferPage = max(1, (int) ($this->request->getGet('mutasi_page') ?? 1));

        $tBuilder = $db->table('transactions')
            ->where('institution_id', $this->currentInstitutionId())
            ->where('activity_id', (int) $activity['id'])
            ->where('deleted_at', null)
            ->groupStart()
                ->where('project_pocket_id', (int) $pocket['id'])
                ->orWhere('counter_project_pocket_id', (int) $pocket['id'])
            ->groupEnd();

        $recentRows = (clone $tBuilder)
            ->orderBy('transaction_date', 'DESC')
            ->orderBy('transaction_time', 'DESC')
            ->orderBy('id', 'DESC')
            ->get()
            ->getResultArray();
        $formattedTransactions = $this->transactionService->formatTransactions($recentRows);
        $summary = $this->summarizePocketRows($recentRows, (int) $pocket['id']);

        $activityTransactions = [];
        $transferItems = [];
        foreach ($formattedTransactions as $item) {
            if (($item['type_key'] ?? '') === 'pindah') {
                $transferItems[] = $item;
            } else {
                $activityTransactions[] = $item;
            }
        }

        $categoryMap = [];
        foreach ($formattedTransactions as $item) {
            if (($item['type_key'] ?? '') !== 'keluar') {
                continue;
            }
            if ((int) ($item['project_pocket_id'] ?? 0) !== (int) $pocket['id']) {
                continue;
            }

            $categoryName = trim((string) ($item['category'] ?? '')) !== '' ? (string) $item['category'] : 'Lainnya';
            $categoryMap[$categoryName] = ($categoryMap[$categoryName] ?? 0) + (float) ($item['amount'] ?? 0) + (float) ($item['admin_fee'] ?? 0);
        }

        $categoryBreakdown = [];
        foreach ($categoryMap as $name => $amount) {
            $categoryBreakdown[] = [
                'category_name' => $name,
                'total_amount' => $amount,
                'percentage' => (float) $summary['expense'] > 0 ? ($amount / (float) $summary['expense']) * 100 : 0,
            ];
        }
        usort($categoryBreakdown, static fn(array $a, array $b): int => $b['total_amount'] <=> $a['total_amount']);

        $activityTransactionPagination = paginate_items($activityTransactions, $transactionPage, 10);
        $activityTransferPagination = paginate_items($transferItems, $transferPage, 10);

        return [
            'pageTitle' => $pocket['name'],
            'activeNav' => 'rekap',
            'backUrl' => site_url('kegiatan/' . $activity['slug']),
            'activity' => [
                'slug' => $activity['slug'],
                'project_slug' => $activity['slug'],
                'name' => $pocket['name'],
                'short_name' => ($pocket['pocket_type'] ?? '') === 'main' ? 'UTM' : substr((string) $pocket['name'], 0, 4),
                'unit_name' => $activity['name'],
                'income' => (float) $summary['income'],
                'expense' => (float) $summary['expense'],
                'surplus' => (float) $summary['balance'],
                'related_accounts' => [],
                'related_balance' => (float) $summary['balance'],
                'masuk_url' => route_query('catat/masuk', ['unit' => $unit['slug'] ?? null, 'kegiatan' => $activity['slug'] ?? null]),
                'keluar_url' => route_query('catat/keluar', ['unit' => $unit['slug'] ?? null, 'kegiatan' => $activity['slug'] ?? null]),
            ],
            'pocket' => $pocket,
            'activityTransactions' => $activityTransactionPagination['items'],
            'activityTransactionPagination' => $activityTransactionPagination,
            'transferItems' => $activityTransferPagination['items'],
            'activityTransferPagination' => $activityTransferPagination,
            'categoryBreakdown' => $categoryBreakdown,
            'involvedReceivers' => $this->getInvolvedReceivers($db, clone $tBuilder),
            'involvedAccounts' => $this->getInvolvedAccounts($db, $recentRows),
            'pocketSummary' => $summary,
        ];
    }

    private function summarizePocketRows(array $rows, int $pocketId): array
    {
        $income = 0.0;
        $expense = 0.0;
        $transferIn = 0.0;
        $transferOut = 0.0;
        $contractIncome = 0.0;
        $transactionCount = 0;
        $categoryBreakdown = [];

        foreach ($rows as $row) {
            $type = (string) ($row['type'] ?? '');
            $sourcePocketId = (int) ($row['project_pocket_id'] ?? 0);
            $targetPocketId = (int) ($row['counter_project_pocket_id'] ?? 0);
            $amount = (float) ($row['amount'] ?? 0);
            $adminFee = (float) ($row['admin_fee'] ?? 0);
            $isSource = $sourcePocketId === $pocketId;
            $isTarget = $targetPocketId === $pocketId;

            if (! $isSource && ! $isTarget) {
                continue;
            }

            $transactionCount++;

            if ($type === 'masuk' && $isSource) {
                $income += $amount;
                $contractIncome += $amount;
                continue;
            }

            if (in_array($type, ['keluar', 'honor'], true) && $isSource) {
                $expense += $amount + $adminFee;
                $category = trim((string) ($row['category'] ?? '')) !== '' ? (string) $row['category'] : 'Lainnya';
                $categoryBreakdown[$category] = ($categoryBreakdown[$category] ?? 0) + $amount + $adminFee;
                continue;
            }

            if ($type !== 'pindah') {
                continue;
            }

            if ($isSource && $targetPocketId > 0 && $targetPocketId !== $pocketId) {
                $expense += $amount + $adminFee;
                $transferOut += $amount;
                continue;
            }

            if ($isTarget && $sourcePocketId > 0 && $sourcePocketId !== $pocketId) {
                $income += $amount;
                $transferIn += $amount;
                continue;
            }

            if ($isSource) {
                $expense += $adminFee;
            }
        }

        return [
            'income' => $income,
            'expense' => $expense,
            'balance' => $income - $expense,
            'transfer_in' => $transferIn,
            'transfer_out' => $transferOut,
            'contract_income' => $contractIncome,
            'transaction_count' => $transactionCount,
            'category_breakdown' => $categoryBreakdown,
        ];
    }

    private function getInvolvedReceivers($db, $tBuilder): array
    {
        $receiverSums = clone $tBuilder;
        $receiverSums = $receiverSums
            ->where('receiver_id IS NOT NULL')
            ->select('receiver_id, SUM(amount + admin_fee) as total')
            ->groupBy('receiver_id')
            ->get()->getResultArray();
            
        $receivers = [];
        foreach ($receiverSums as $rs) {
            $rec = $db->table('receivers')->where('id', $rs['receiver_id'])->get()->getRowArray();
            if ($rec) {
                $receivers[] = [
                    'id' => (int) $rec['id'],
                    'name' => $rec['name'],
                    'type' => $rec['type'] ?? 'Lainnya',
                    'total_received' => (float) $rs['total'],
                    'detail_url' => site_url('penerima/' . $rec['id']),
                ];
            }
        }
        usort($receivers, fn($a, $b) => $b['total_received'] <=> $a['total_received']);
        return $receivers;
    }

    private function getInvolvedAccounts($db, array $rows): array
    {
        $accountIds = [];
        $movementMap = [];

        foreach ($rows as $row) {
            $fromId = (int) ($row['from_account_id'] ?? 0);
            $toId = (int) ($row['to_account_id'] ?? 0);
            $type = (string) ($row['type'] ?? '');
            $amount = (float) ($row['amount'] ?? 0);
            $adminFee = (float) ($row['admin_fee'] ?? 0);

            if ($fromId > 0) {
                $accountIds[$fromId] = $fromId;
                $movementMap[$fromId] = ($movementMap[$fromId] ?? ['income' => 0.0, 'expense' => 0.0, 'count' => 0]);
                $movementMap[$fromId]['expense'] += in_array($type, ['keluar', 'honor', 'pindah'], true) ? ($amount + $adminFee) : 0.0;
                $movementMap[$fromId]['count']++;
            }

            if ($toId > 0) {
                $accountIds[$toId] = $toId;
                $movementMap[$toId] = ($movementMap[$toId] ?? ['income' => 0.0, 'expense' => 0.0, 'count' => 0]);
                $movementMap[$toId]['income'] += in_array($type, ['masuk', 'pindah'], true) ? $amount : 0.0;
                $movementMap[$toId]['count']++;
            }
        }

        if ($accountIds === []) {
            return [];
        }

        $accounts = $db->table('accounts')
            ->whereIn('id', array_values($accountIds))
            ->where('deleted_at', null)
            ->where('is_active', 1)
            ->get()
            ->getResultArray();

        $items = [];
        foreach ($accounts as $account) {
            $accountId = (int) $account['id'];
            $meta = $movementMap[$accountId] ?? ['income' => 0.0, 'expense' => 0.0, 'count' => 0];
            $items[] = [
                'id' => $accountId,
                'name' => $account['name'],
                'slug' => $account['slug'] ?? ('acc-' . $accountId),
                'mark' => $account['mark'] ?? '',
                'kind' => $account['kind'] ?? 'Rekening',
                'logo_asset' => $account['logo_asset'] ?? '',
                'account_number' => $account['account_number'] ?? '',
                'balance' => (float) $meta['income'] - (float) $meta['expense'],
                'income' => (float) $meta['income'],
                'expense' => (float) $meta['expense'],
                'transaction_count' => (int) $meta['count'],
                'movement_count' => (int) $meta['count'],
                'preview_activity' => 'Dipakai pada transaksi terkait',
                'detail_url' => site_url('rekening/' . ($account['slug'] ?? ('acc-' . $accountId))),
            ];
        }

        usort($items, static fn(array $a, array $b): int => $b['transaction_count'] <=> $a['transaction_count']);
        return $items;
    }

    private function getInvolvedActivities($db, array $rows): array
    {
        $activityIds = [];
        foreach ($rows as $row) {
            $activityId = (int) ($row['activity_id'] ?? 0);
            if ($activityId > 0) {
                $activityIds[$activityId] = $activityId;
            }
        }

        if ($activityIds === []) {
            return [];
        }

        $activities = $db->table('activities')
            ->whereIn('id', array_values($activityIds))
            ->where('deleted_at', null)
            ->where('is_active', 1)
            ->get()
            ->getResultArray();

        $unitIds = array_values(array_filter(array_map(static fn(array $activity): int => (int) ($activity['unit_id'] ?? 0), $activities)));
        $unitMap = [];
        if ($unitIds !== []) {
            foreach ($db->table('units')->whereIn('id', $unitIds)->where('deleted_at', null)->where('is_active', 1)->get()->getResultArray() as $unit) {
                $unitMap[(int) $unit['id']] = $unit;
            }
        }

        $stats = [];
        foreach ($rows as $row) {
            $activityId = (int) ($row['activity_id'] ?? 0);
            if ($activityId <= 0) {
                continue;
            }

            $type = (string) ($row['type'] ?? '');
            $amount = (float) ($row['amount'] ?? 0);
            $adminFee = (float) ($row['admin_fee'] ?? 0);
            $stats[$activityId] = $stats[$activityId] ?? ['income' => 0.0, 'expense' => 0.0, 'count' => 0];
            if ($type === 'masuk') {
                $stats[$activityId]['income'] += $amount;
            } elseif (in_array($type, ['keluar', 'honor'], true)) {
                $stats[$activityId]['expense'] += $amount + $adminFee;
            }
            $stats[$activityId]['count']++;
        }

        $items = [];
        foreach ($activities as $activity) {
            $activityId = (int) $activity['id'];
            $unit = $unitMap[(int) ($activity['unit_id'] ?? 0)] ?? null;
            $meta = $stats[$activityId] ?? ['income' => 0.0, 'expense' => 0.0, 'count' => 0];
            $items[] = [
                'id' => $activityId,
                'slug' => $activity['slug'] ?? ('act-' . $activityId),
                'name' => $activity['name'],
                'short_name' => substr((string) $activity['name'], 0, 4),
                'unit_name' => is_array($unit) ? ($unit['name'] ?? '') : '',
                'income' => (float) $meta['income'],
                'expense' => (float) $meta['expense'],
                'surplus' => (float) $meta['income'] - (float) $meta['expense'],
                'related_balance' => (float) $meta['income'] - (float) $meta['expense'],
                'transaction_count' => (int) $meta['count'],
                'detail_url' => site_url('kegiatan/' . ($activity['slug'] ?? ('act-' . $activityId))),
                'masuk_url' => route_query('catat/masuk', ['unit' => is_array($unit) ? ($unit['slug'] ?? null) : null, 'kegiatan' => $activity['slug'] ?? null]),
                'keluar_url' => route_query('catat/keluar', ['unit' => is_array($unit) ? ($unit['slug'] ?? null) : null, 'kegiatan' => $activity['slug'] ?? null]),
            ];
        }

        usort($items, static fn(array $a, array $b): int => $b['transaction_count'] <=> $a['transaction_count']);
        return $items;
    }

    /**
     * @param array<int, array<string, mixed>> $rows
     * @return array<int, array<string, mixed>>
     */
    private function indexById(array $rows): array
    {
        $indexed = [];
        foreach ($rows as $row) {
            if (isset($row['id'])) {
                $indexed[(int) $row['id']] = $row;
            }
        }

        return $indexed;
    }

    /**
     * @param array<int, array<string, mixed>> $units
     */
    private function resolveUnitId(string $value, array $units): ?int
    {
        if ($value === '' || $value === 'semua') {
            return null;
        }

        if (str_starts_with($value, 'unit-')) {
            $id = (int) str_replace('unit-', '', $value);
            return $id > 0 ? $id : null;
        }

        foreach ($units as $unit) {
            if (($unit['slug'] ?? '') === $value) {
                return (int) $unit['id'];
            }
        }

        $id = (int) $value;
        return $id > 0 ? $id : null;
    }

    /**
     * @param array<int, array<string, mixed>> $activities
     */
    private function resolveActivityId(string $value, array $activities): ?int
    {
        if ($value === '' || $value === 'semua') {
            return null;
        }

        if (str_starts_with($value, 'act-')) {
            $id = (int) str_replace('act-', '', $value);
            return $id > 0 ? $id : null;
        }

        foreach ($activities as $activity) {
            if (($activity['slug'] ?? '') === $value) {
                return (int) $activity['id'];
            }
        }

        $id = (int) $value;
        return $id > 0 ? $id : null;
    }
}
