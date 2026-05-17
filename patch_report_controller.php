<?php
$file = 'app/Controllers/ReportController.php';
$content = file_get_contents($file);

// Remove the last "}"
$content = preg_replace('/}\s*$/', '', $content);

$newMethods = <<<METHODS

    public function rekening(string \$slug): string
    {
        \$institutionId = \$this->currentInstitutionId();
        \$db = \Config\Database::connect();
        \$request = service('request');

        \$accountId = (int) str_replace('acc-', '', \$slug);
        \$acc = \$db->table('accounts')->where('id', \$accountId)->where('institution_id', \$institutionId)->where('deleted_at', null)->get()->getRowArray();
        
        if (! \$acc) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        \$selectedPeriodSlug = \$request->getGet('periode') ?: 'semua';
        \$selectedUnitSlug = \$request->getGet('unit') ?: 'semua';
        \$selectedActivitySlug = \$request->getGet('kegiatan') ?: 'semua';

        \$filterPeriodId = \$selectedPeriodSlug !== 'semua' ? (int) str_replace('period-', '', \$selectedPeriodSlug) : null;
        \$filterUnitId = \$selectedUnitSlug !== 'semua' ? (int) str_replace('unit-', '', \$selectedUnitSlug) : null;
        \$filterActivityId = \$selectedActivitySlug !== 'semua' ? (int) str_replace('act-', '', \$selectedActivitySlug) : null;

        \$tBuilder = \$db->table('transactions')
            ->where('institution_id', \$institutionId)
            ->where('deleted_at', null)
            ->groupStart()
                ->where('to_account_id', \$accountId)
                ->orWhere('from_account_id', \$accountId)
            ->groupEnd();
            
        if (\$filterPeriodId) \$tBuilder->where('book_period_id', \$filterPeriodId);
        if (\$filterUnitId) \$tBuilder->where('unit_id', \$filterUnitId);
        if (\$filterActivityId) \$tBuilder->where('activity_id', \$filterActivityId);

        \$accOb = (float) (\$db->table('opening_balances')->where('account_id', \$accountId)->where('deleted_at', null)->selectSum('amount')->get()->getRow()->amount ?? 0);
        \$accInc = (float) (clone \$tBuilder)->where('type', 'masuk')->where('to_account_id', \$accountId)->selectSum('amount')->get()->getRow()->amount ?? 0;
        \$accExpMain = (float) (clone \$tBuilder)->whereIn('type', ['keluar', 'honor'])->where('from_account_id', \$accountId)->select('SUM(amount + admin_fee) as total')->get()->getRow()->total ?? 0;
        \$accExpPindah = (float) (clone \$tBuilder)->where('type', 'pindah')->where('from_account_id', \$accountId)->selectSum('admin_fee')->get()->getRow()->admin_fee ?? 0;
        \$accExp = \$accExpMain + \$accExpPindah;

        \$recentRows = (clone \$tBuilder)->orderBy('transaction_date', 'DESC')->orderBy('transaction_time', 'DESC')->orderBy('id', 'DESC')->get()->getResultArray();
        \$formattedTransactions = \$this->transactionService->formatTransactions(\$recentRows);
        
        \$account = [
            'name' => \$acc['name'],
            'balance' => \$accOb + \$accInc - \$accExp,
            'income' => \$accInc,
            'expense' => \$accExp,
            'transfer_in' => 0,
            'transfer_out' => 0,
            'icon' => 'account_balance_wallet',
            'color' => 'emerald',
            'surplus' => \$accInc - \$accExp,
            'slug' => 'acc-' . \$accountId,
            'kind' => \$acc['kind'] ?? 'Tunai',
            'mark' => \$acc['mark'] ?? '',
            'note' => 'Rekening aktif',
            'movement_count' => count(\$recentRows),
        ];

        \$accountActivities = [];
        
        foreach (\$recentRows as \$row) {
            \$isIncome = (\$row['to_account_id'] == \$accountId);
            \$amount = \$row['amount'] + (\$isIncome ? 0 : \$row['admin_fee']);
            \$actId = \$row['activity_id'];
            if (\$actId) {
                if (!isset(\$accountActivities[\$actId])) {
                    \$a = \$db->table('activities')->where('id', \$actId)->get()->getRowArray();
                    \$u = \$db->table('units')->where('id', \$a['unit_id'])->get()->getRowArray();
                    \$accountActivities[\$actId] = [
                        'name' => \$a['name'],
                        'unit_name' => \$u['name'],
                        'amount' => 0,
                        'income' => 0,
                        'expense' => 0,
                        'transfer_in' => 0,
                        'transfer_out' => 0,
                        'detail_url' => route_query('kegiatan/act-' . \$actId),
                    ];
                }
                \$accountActivities[\$actId]['amount'] += (\$isIncome ? \$amount : -\$amount);
                if (\$isIncome) {
                    \$accountActivities[\$actId]['income'] += \$amount;
                } else {
                    \$accountActivities[\$actId]['expense'] += \$amount;
                }
            }
        }

        usort(\$accountActivities, fn(\$a, \$b) => abs(\$b['amount']) <=> abs(\$a['amount']));

        \$data = [
            'pageTitle'           => \$account['name'],
            'activeNav'           => 'rekap',
            'backUrl'             => route_query('rekap', ['periode' => \$selectedPeriodSlug, 'unit' => \$selectedUnitSlug, 'kegiatan' => \$selectedActivitySlug]),
            'account'             => \$account,
            'accountTransactions' => \$formattedTransactions,
            'involvedReceivers'   => [],
            'accountActivities'   => \$accountActivities,
            'rekapFilterSummary'  => [
                'period_label' => \$selectedPeriodSlug === 'semua' ? 'Semua Periode' : 'Periode Terpilih',
                'unit_label' => \$selectedUnitSlug === 'semua' ? 'Semua Unit' : 'Unit Terpilih',
                'activity_label' => \$selectedActivitySlug === 'semua' ? 'Semua Kegiatan' : 'Kegiatan Terpilih',
            ],
        ];

        return view('pages/account_detail', \$data);
    }

    public function unit(string \$slug): string
    {
        \$institutionId = \$this->currentInstitutionId();
        \$db = \Config\Database::connect();

        \$unitId = (int) str_replace('unit-', '', \$slug);
        \$u = \$db->table('units')->where('id', \$unitId)->where('institution_id', \$institutionId)->where('deleted_at', null)->get()->getRowArray();
        
        if (! \$u) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        \$tBuilder = \$db->table('transactions')
            ->where('institution_id', \$institutionId)
            ->where('unit_id', \$unitId)
            ->where('deleted_at', null);

        \$uInc = (float) (clone \$tBuilder)->where('type', 'masuk')->selectSum('amount')->get()->getRow()->amount ?? 0;
        \$uExpMain = (float) (clone \$tBuilder)->whereIn('type', ['keluar', 'honor'])->select('SUM(amount + admin_fee) as total')->get()->getRow()->total ?? 0;
        \$uExpPindah = (float) (clone \$tBuilder)->where('type', 'pindah')->selectSum('admin_fee')->get()->getRow()->admin_fee ?? 0;
        \$uExp = \$uExpMain + \$uExpPindah;
        
        \$uActCount = \$db->table('activities')->where('unit_id', \$u['id'])->where('deleted_at', null)->countAllResults();
        \$uFirstAct = \$db->table('activities')->where('unit_id', \$u['id'])->where('deleted_at', null)->orderBy('id', 'ASC')->get()->getRowArray();

        \$unit = [
            'slug' => 'unit-' . \$u['id'],
            'name' => \$u['name'],
            'short_name' => substr(\$u['name'], 0, 4),
            'income' => \$uInc,
            'expense' => \$uExp,
            'surplus' => \$uInc - \$uExp,
            'activities' => array_fill(0, \$uActCount, 1),
            'quick_activity_name' => \$uFirstAct['name'] ?? '-',
            'detail_url' => '#',
            'masuk_url' => route_query('catat/masuk', ['unit' => 'unit-' . \$u['id']]),
            'keluar_url' => route_query('catat/keluar', ['unit' => 'unit-' . \$u['id']]),
        ];

        \$recentRows = (clone \$tBuilder)->orderBy('transaction_date', 'DESC')->orderBy('transaction_time', 'DESC')->orderBy('id', 'DESC')->get()->getResultArray();
        
        \$data = [
            'pageTitle' => \$unit['name'],
            'activeNav' => 'rekap',
            'backUrl' => site_url('rekap'),
            'unit' => \$unit,
            'unitTransactions' => \$this->transactionService->formatTransactions(\$recentRows),
            'involvedReceivers' => [],
        ];

        return view('pages/unit_detail', \$data);
    }

    public function kegiatan(string \$slug): string
    {
        \$institutionId = \$this->currentInstitutionId();
        \$db = \Config\Database::connect();
        
        \$activityId = (int) str_replace('act-', '', \$slug);
        \$act = \$db->table('activities')->where('id', \$activityId)->where('deleted_at', null)->get()->getRowArray();
        
        if (! \$act) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        \$unit = \$db->table('units')->where('id', \$act['unit_id'])->where('institution_id', \$institutionId)->get()->getRowArray();
        if (!\$unit) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        \$tBuilder = \$db->table('transactions')
            ->where('institution_id', \$institutionId)
            ->where('activity_id', \$activityId)
            ->where('deleted_at', null);

        \$actInc = (float) (clone \$tBuilder)->where('type', 'masuk')->selectSum('amount')->get()->getRow()->amount ?? 0;
        \$actExpMain = (float) (clone \$tBuilder)->whereIn('type', ['keluar', 'honor'])->select('SUM(amount + admin_fee) as total')->get()->getRow()->total ?? 0;
        \$actExpPindah = (float) (clone \$tBuilder)->where('type', 'pindah')->selectSum('admin_fee')->get()->getRow()->admin_fee ?? 0;
        \$actExp = \$actExpMain + \$actExpPindah;

        \$activity = [
            'name' => \$act['name'],
            'unit_name' => \$unit['name'] ?? '',
            'income' => \$actInc,
            'expense' => \$actExp,
            'surplus' => \$actInc - \$actExp,
        ];

        \$recentRows = (clone \$tBuilder)->orderBy('transaction_date', 'DESC')->orderBy('transaction_time', 'DESC')->orderBy('id', 'DESC')->get()->getResultArray();
        \$formattedTransactions = \$this->transactionService->formatTransactions(\$recentRows);
        
        \$activityTransactions = [];
        \$transferItems = [];
        \$categoryBreakdownMap = [];
        
        foreach (\$formattedTransactions as \$item) {
            if (\$item['type_key'] === 'pindah') {
                \$transferItems[] = \$item;
            } else {
                \$activityTransactions[] = \$item;
            }
            if (\$item['type_key'] === 'keluar') {
                \$catName = \$item['category'] ?: 'Lainnya';
                if (!isset(\$categoryBreakdownMap[\$catName])) {
                    \$categoryBreakdownMap[\$catName] = 0;
                }
                \$categoryBreakdownMap[\$catName] += \$item['amount'];
            }
        }

        \$categoryBreakdown = [];
        foreach (\$categoryBreakdownMap as \$name => \$amount) {
            \$categoryBreakdown[] = [
                'category_name' => \$name,
                'total_amount' => \$amount,
                'percentage' => \$actExp > 0 ? (\$amount / \$actExp) * 100 : 0,
            ];
        }

        usort(\$categoryBreakdown, fn(\$a, \$b) => \$b['total_amount'] <=> \$a['total_amount']);

        \$data = [
            'pageTitle' => \$activity['name'],
            'activeNav' => 'rekap',
            'backUrl' => site_url('rekap'),
            'activity' => \$activity,
            'activityTransactions' => \$activityTransactions,
            'transferItems' => \$transferItems,
            'categoryBreakdown' => \$categoryBreakdown,
            'involvedReceivers' => [],
        ];

        return view('pages/activity_detail', \$data);
    }
}
METHODS;

file_put_contents($file, $content . "\n" . $newMethods);
echo "Patch applied successfully.\n";
