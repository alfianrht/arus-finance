<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<div class="space-y-3">
    <?= view('partials/top_nav_back', [
        'title' => $receiver['name'],
        'subtitle' => 'Penerima',
        'backUrl' => $backUrl,
    ]) ?>

    <?php $surfaceText = surface_label($receiver['name']); ?>
    <section class="relative overflow-hidden rounded-3xl bg-white p-5 text-zinc-950 shadow-sm">
        <div class="absolute inset-0 bg-white/40" aria-hidden="true"></div>
        <p class="pointer-events-none absolute -bottom-3 left-3 text-7xl font-black uppercase tracking-tight text-zinc-100" aria-hidden="true"><?= esc($surfaceText) ?></p>

        <div class="relative flex items-start justify-between gap-4">
            <div class="min-w-0">
                <div class="inline-flex items-center gap-2 rounded-full bg-zinc-100 px-3 py-2 text-[10px] font-bold uppercase tracking-wider text-zinc-600">
                    <span class="h-1.5 w-1.5 rounded-full bg-lime-400"></span>
                    <span><?= esc($receiver['type']) ?></span>
                </div>
                <p class="mt-3 text-2xl font-semibold leading-tight text-zinc-950"><?= esc($receiver['name']) ?></p>
                <?php if ($receiver['notes'] !== ''): ?>
                    <p class="mt-2 max-w-xl text-sm text-zinc-500"><?= esc($receiver['notes']) ?></p>
                <?php endif; ?>
            </div>
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-zinc-900 text-white">
                <span class="material-symbols-rounded text-2xl" aria-hidden="true">account_circle</span>
            </div>
        </div>

        <div class="relative mt-3 border-t border-zinc-100 pt-4">
            <p class="text-[10px] font-bold uppercase tracking-wider text-zinc-500">Total Nominal Terkait</p>
            <p class="mt-1 text-4xl font-black tracking-tight text-zinc-950"><?= esc(rupiah($receiver['total_amount'])) ?></p>
            <p class="mt-2 text-sm text-zinc-500"><?= esc((string) $receiver['transaction_count']) ?> transaksi terkait</p>
        </div>
    </section>

    <div class="grid grid-cols-2 gap-2 sm:gap-3">
        <div class="rounded-2xl bg-white p-3 shadow-sm sm:rounded-3xl sm:p-4">
            <div class="flex items-center gap-1.5 sm:gap-2">
                <div class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-lime-100 text-zinc-950 sm:h-8 sm:w-8">
                    <span class="material-symbols-rounded text-[11px] sm:text-sm">payments</span>
                </div>
                <p class="truncate text-[9px] font-bold uppercase tracking-wider text-zinc-500 sm:text-[11px]">Total Nominal</p>
            </div>
            <p class="mt-1.5 truncate text-sm font-black text-zinc-950 sm:mt-3 sm:text-base"><?= esc(rupiah($receiver['total_amount'])) ?></p>
        </div>
        <div class="rounded-2xl bg-white p-3 shadow-sm sm:rounded-3xl sm:p-4">
            <div class="flex items-center gap-1.5 sm:gap-2">
                <div class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-zinc-100 text-zinc-700 sm:h-8 sm:w-8">
                    <span class="material-symbols-rounded text-[11px] sm:text-sm">receipt_long</span>
                </div>
                <p class="truncate text-[9px] font-bold uppercase tracking-wider text-zinc-500 sm:text-[11px]">Jumlah Transaksi</p>
            </div>
            <p class="mt-1.5 truncate text-sm font-black text-zinc-950 sm:mt-3 sm:text-base"><?= esc((string) $receiver['transaction_count']) ?></p>
        </div>
    </div>

    <section class="rounded-3xl bg-white p-4 shadow-sm">
        <div class="flex items-center justify-between">
            <h2 class="text-base font-semibold text-zinc-950">Kegiatan Terkait</h2>
            <p class="text-xs text-zinc-500">Melibatkan penerima ini</p>
        </div>
        <div class="mt-3 space-y-3 md:grid md:grid-cols-2 md:gap-3 md:space-y-0">
            <?php if ($receiverActivities === []): ?>
                <?= view('partials/empty_state', [
                    'icon' => 'folder_supervised',
                    'title' => 'Belum ada kegiatan terkait.',
                    'description' => 'Kegiatan akan muncul setelah penerima ini dipakai di transaksi yang terkait ke kegiatan tertentu.',
                    'compact' => true,
                ]) ?>
            <?php endif; ?>
            <?php foreach ($receiverActivities as $activity): ?>
                <?= view('partials/activity_card', ['activity' => $activity]) ?>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="rounded-3xl bg-white p-4 shadow-sm">
        <div class="flex items-center justify-between">
            <h2 class="text-base font-semibold text-zinc-950">Rekening Terkait</h2>
            <p class="text-xs text-zinc-500">Dipakai transaksi penerima ini</p>
        </div>
        <div class="mt-3 space-y-3 md:grid md:grid-cols-2 md:gap-3 md:space-y-0">
            <?php if ($receiverAccounts === []): ?>
                <?= view('partials/empty_state', [
                    'icon' => 'account_balance_wallet',
                    'title' => 'Belum ada rekening terkait.',
                    'description' => 'Rekening akan muncul saat transaksi yang melibatkan penerima ini sudah menggunakan rekening atau dompet tertentu.',
                    'compact' => true,
                ]) ?>
            <?php endif; ?>
            <?php foreach ($receiverAccounts as $account): ?>
                <?= view('partials/account_card', ['account' => $account, 'cardWidthClass' => 'w-full']) ?>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="rounded-3xl bg-white py-4 shadow-sm">
        <div class="px-4 flex items-center justify-between">
            <h2 class="text-base font-semibold text-zinc-950">Transaksi Terkait</h2>
            <p class="text-xs text-zinc-500"><?= esc($receiver['name']) ?></p>
        </div>
        <div class="mt-3 divide-y divide-zinc-100">
            <?php if ($receiverTransactions === []): ?>
                <div class="px-4 pb-1">
                    <?= view('partials/empty_state', [
                        'icon' => 'receipt_long',
                        'title' => 'Belum ada transaksi terkait.',
                        'description' => 'Transaksi akan muncul di sini setelah penerima ini dipakai dalam pencatatan.',
                        'compact' => true,
                    ]) ?>
                </div>
            <?php endif; ?>
            <?php foreach ($receiverTransactions as $transaction): ?>
                <?= view('partials/transaction_item', ['transaction' => $transaction]) ?>
            <?php endforeach; ?>
        </div>
        <?= view('partials/pagination_controls', [
            'pagination' => $receiverTransactionPagination,
            'prevUrl' => route_query('penerima/' . $receiver['id'], ['transaksi_page' => $receiverTransactionPagination['prevPage']]),
            'nextUrl' => route_query('penerima/' . $receiver['id'], ['transaksi_page' => $receiverTransactionPagination['nextPage']]),
        ]) ?>
    </section>
</div>
<?= $this->endSection() ?>
