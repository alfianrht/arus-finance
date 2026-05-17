<?php

namespace App\Controllers;

use App\Models\ActivityModel;
use App\Models\BookPeriodModel;
use App\Models\UnitModel;
use App\Services\TransactionService;
use CodeIgniter\Database\BaseBuilder;

class ReportController extends BaseController
{
    private TransactionService $transactionService;

    public function __construct()
    {
        $this->transactionService = new TransactionService();
    }

    public function index(): string
    {
        $institutionId = $this->currentInstitutionId();
        $db = \Config\Database::connect();
        $request = service('request');

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
                ->get()->getResultArray();
        } else {
            if (empty($allUnitIds)) {
                $filterActivities = [];
            } else {
                $filterActivities = $db->table('activities')
                    ->whereIn('unit_id', $allUnitIds)
                    ->where('deleted_at', null)
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

        // 4. Accounts
        $accounts = $db->table('accounts')
            ->where('institution_id', $institutionId)
            ->where('deleted_at', null)
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
                'movement_count' => 0,
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
            $u['masuk_url'] = route_query('catat/masuk', ['unit' => $u['slug'], 'kegiatan' => $uFirstAct['slug'] ?? null]);
            $u['keluar_url'] = route_query('catat/keluar', ['unit' => $u['slug'], 'kegiatan' => $uFirstAct['slug'] ?? null]);
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
            $a['detail_url'] = route_query('kegiatan/' . $a['slug'], ['periode' => $selectedPeriodSlug, 'unit' => $selectedUnitSlug]);
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
                    'name' => $rec['name'],
                    'type' => $rec['type'],
                    'total_received' => (float) $rs['total'],
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
            'rekapTransactions' => $rekapTransactions,
            'rekapTransferItems' => $rekapTransferItems,
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

        $filterPeriodId = $selectedPeriodSlug !== 'semua' ? (int) str_replace('period-', '', $selectedPeriodSlug) : null;
        $units = $this->loadUnitProgramRows($institutionId);
        $allUnitIds = array_column($units, 'id');
        $allActivities = $allUnitIds === []
            ? []
            : $db->table('activities')->whereIn('unit_id', $allUnitIds)->where('deleted_at', null)->get()->getResultArray();
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
        
        $account = [
            'name' => $acc['name'],
            'balance' => $accOb + $accInc - $accExp,
            'income' => $accInc,
            'expense' => $accExp,
            'transfer_in' => $accIncPindah,
            'transfer_out' => $accExpPindah,
            'icon' => 'account_balance_wallet',
            'color' => 'emerald',
            'surplus' => $accInc - $accExp,
            'slug' => $acc['slug'] ?? ('acc-' . $accountId),
            'kind' => $acc['kind'] ?? 'Tunai',
            'mark' => $acc['mark'] ?? '',
            'note' => 'Rekening aktif',
            'movement_count' => count($recentRows),
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
                        'name' => $a['name'] ?? 'Tanpa Kegiatan',
                        'unit_name' => $u['name'] ?? 'Tanpa Unit',
                        'amount' => 0,
                        'income' => 0,
                        'expense' => 0,
                        'transfer_in' => 0,
                        'transfer_out' => 0,
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
            }
        }

        usort($accountActivities, fn($a, $b) => abs($b['amount']) <=> abs($a['amount']));

        $data = [
            'pageTitle'           => $account['name'],
            'activeNav'           => 'rekap',
            'backUrl'             => route_query('rekap', ['periode' => $selectedPeriodSlug, 'unit' => $selectedUnitSlug, 'kegiatan' => $selectedActivitySlug]),
            'account'             => $account,
            'accountTransactions' => $formattedTransactions,
            'involvedReceivers'   => $this->getInvolvedReceivers($db, clone $tBuilder),
            'accountActivities'   => $accountActivities,
            'rekapFilterSummary'  => [
                'period_label' => $selectedPeriodSlug === 'semua' ? 'Semua Periode' : 'Periode Terpilih',
                'unit_label' => $selectedUnitSlug === 'semua' ? 'Semua Unit' : 'Unit Terpilih',
                'activity_label' => $selectedActivitySlug === 'semua' ? 'Semua Kegiatan' : 'Kegiatan Terpilih',
            ],
        ];

        return view('pages/account_detail', $data);
    }

    public function unit(string $slug): string
    {
        $institutionId = $this->currentInstitutionId();
        $db = \Config\Database::connect();
        $units = $this->loadUnitProgramRows($institutionId);
        $unitId = $this->resolveUnitId($slug, $units);
        $u = $unitId === null
            ? null
            : $db->table('units')->where('id', $unitId)->where('institution_id', $institutionId)->where('deleted_at', null)->get()->getRowArray();
        
        if (! $u) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $request = service('request');
        $selectedPeriodSlug = $request->getGet('periode') ?: 'semua';
        $selectedActivitySlug = $request->getGet('kegiatan') ?: 'semua';

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
                'detail_url' => route_query('kegiatan/' . ($act['slug'] ?? ('act-' . $act['id'])), ['periode' => $selectedPeriodSlug, 'unit' => $u['slug']]),
            ];
        }

        $unit = [
            'slug' => $u['slug'],
            'name' => $u['name'],
            'short_name' => substr($u['name'], 0, 4),
            'income' => $uInc,
            'expense' => $uExp,
            'surplus' => $uInc - $uExp,
            'activities' => $formattedActivities,
            'quick_activity_name' => $uActivities[0]['name'] ?? '-',
            'detail_url' => site_url('unit/' . $u['slug']),
            'masuk_url' => route_query('catat/masuk', ['unit' => $u['slug'], 'kegiatan' => $uActivities[0]['slug'] ?? null]),
            'keluar_url' => route_query('catat/keluar', ['unit' => $u['slug'], 'kegiatan' => $uActivities[0]['slug'] ?? null]),
        ];

        $recentRows = (clone $tBuilder)->orderBy('transaction_date', 'DESC')->orderBy('transaction_time', 'DESC')->orderBy('id', 'DESC')->get()->getResultArray();
        
        $data = [
            'pageTitle' => $unit['name'],
            'activeNav' => 'rekap',
            'backUrl' => site_url('rekap'),
            'unit' => $unit,
            'unitTransactions' => $this->transactionService->formatTransactions($recentRows),
            'involvedReceivers' => $this->getInvolvedReceivers($db, $tBuilder),
        ];

        return view('pages/unit_detail', $data);
    }

    public function kegiatan(string $slug): string
    {
        $institutionId = $this->currentInstitutionId();
        $db = \Config\Database::connect();
        $units = $this->loadUnitProgramRows($institutionId);
        $allUnitIds = array_column($units, 'id');
        $allActivities = $allUnitIds === []
            ? []
            : $db->table('activities')->whereIn('unit_id', $allUnitIds)->where('deleted_at', null)->get()->getResultArray();
        $activityId = $this->resolveActivityId($slug, $allActivities);
        $act = $activityId === null
            ? null
            : $db->table('activities')->where('id', $activityId)->where('deleted_at', null)->get()->getRowArray();
        
        if (! $act) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $unit = $db->table('units')->where('id', $act['unit_id'])->where('institution_id', $institutionId)->get()->getRowArray();
        if (!$unit) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $request = service('request');
        $selectedPeriodSlug = $request->getGet('periode') ?: 'semua';
        $selectedUnitSlug = $request->getGet('unit') ?: 'semua';

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
            'name' => $act['name'],
            'short_name' => substr($act['name'], 0, 4),
            'unit_name' => $unit['name'] ?? '',
            'income' => $actInc,
            'expense' => $actExp,
            'surplus' => $actInc - $actExp,
            'related_accounts' => [],
            'related_balance' => 0,
        ];

        $recentRows = (clone $tBuilder)->orderBy('transaction_date', 'DESC')->orderBy('transaction_time', 'DESC')->orderBy('id', 'DESC')->get()->getResultArray();
        $formattedTransactions = $this->transactionService->formatTransactions($recentRows);
        
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

        $data = [
            'pageTitle' => $activity['name'],
            'activeNav' => 'rekap',
            'backUrl' => site_url('rekap'),
            'activity' => $activity,
            'activityTransactions' => $activityTransactions,
            'transferItems' => $transferItems,
            'categoryBreakdown' => $categoryBreakdown,
            'involvedReceivers' => $this->getInvolvedReceivers($db, clone $tBuilder),
        ];

        return view('pages/activity_detail', $data);
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
                    'name' => $rec['name'],
                    'type' => $rec['type'] ?? 'Lainnya',
                    'total_received' => (float) $rs['total'],
                ];
            }
        }
        usort($receivers, fn($a, $b) => $b['total_received'] <=> $a['total_received']);
        return $receivers;
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
