<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<div class="space-y-3">
    <?= view('partials/top_nav_back', [
        'title' => $account['name'],
        'subtitle' => 'Rekening / Dompet',
        'backUrl' => $backUrl,
    ]) ?>

    <div class="space-y-3 xl:grid xl:grid-cols-[minmax(0,1.45fr)_minmax(0,0.92fr)] xl:items-start xl:gap-4 xl:space-y-0">
        <div class="space-y-3">
            <section class="relative block">
        <div class="relative overflow-hidden rounded-3xl border border-zinc-950 bg-white p-5 text-zinc-950 shadow-sm">
            <span class="absolute left-1/2 top-0 h-1 w-24 -translate-x-1/2 rounded-b-2xl bg-zinc-950"></span>
            <span class="absolute bottom-0 left-1/2 h-2 w-24 -translate-x-1/2 rounded-t-2xl bg-zinc-950"></span>
            <div class="flex items-start justify-between gap-4">
                <div class="min-w-0">
                    <p class="text-xs font-medium uppercase tracking-wide text-zinc-950/55"><?= esc($account['kind']) ?></p>
                    <p class="mt-3 text-4xl font-black tracking-tight tabular-nums text-zinc-950"><?= esc(rupiah($account['balance'])) ?></p>
                </div>
                <?php if (!empty($account['logo_asset'])): ?>
                    <img src="<?= esc(base_url($account['logo_asset'])) ?>" alt="<?= esc($account['mark']) ?>" data-image-preview data-image-preview-alt="<?= esc($account['name']) ?>" class="mt-1 h-8 w-auto cursor-zoom-in object-contain">
                <?php else: ?>
                    <p class="text-2xl font-black uppercase tracking-tight text-zinc-950"><?= esc($account['mark']) ?></p>
                <?php endif; ?>
            </div>

            <div class="mt-2 flex items-center gap-2 text-zinc-950">
                <?php if (!empty($account['account_number'])): ?>
                    <span class="text-sm font-black tracking-wide text-zinc-950/70"><?= esc($account['account_number']) ?></span>
                    <button
                        type="button"
                        class="flex items-center justify-center text-zinc-400 transition hover:text-zinc-950"
                        onclick="copyToClipboard('<?= esc($account['account_number'], 'js') ?>', this)"
                        title="Salin nomor rekening"
                    >
                        <span class="material-symbols-rounded text-sm">content_copy</span>
                    </button>
                <?php else: ?>
                    <span class="text-sm font-black tracking-widest text-zinc-950"><?= esc(surface_tail($account['slug'])) ?></span>
                    <span class="h-1.5 w-1.5 rounded-full bg-zinc-950"></span>
                    <span class="h-1.5 w-1.5 rounded-full bg-zinc-950"></span>
                    <span class="h-1.5 w-1.5 rounded-full bg-zinc-950"></span>
                    <span class="h-1.5 w-1.5 rounded-full bg-zinc-950"></span>
                <?php endif; ?>
            </div>

            <div class="mt-5 flex items-end justify-between gap-4">
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-zinc-950"><?= esc($account['name']) ?></p>
                    <p class="mt-1 text-sm text-zinc-950/65"><?= esc($account['note']) ?></p>
                </div>
                <p class="shrink-0 text-xs font-medium text-zinc-950/70"><?= esc($account['movement_count']) ?> mutasi</p>
            </div>
        </div>
            </section>

            <div class="grid grid-cols-2 gap-2 sm:gap-3 sm:grid-cols-4">
        <div class="rounded-2xl border border-zinc-100 bg-white p-3 shadow-sm sm:rounded-3xl sm:p-4">
            <div class="flex items-center gap-1.5 sm:gap-2">
                <div class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-zinc-100 text-zinc-700 sm:h-8 sm:w-8">
                    <span class="material-symbols-rounded text-[11px] sm:text-sm">account_balance_wallet</span>
                </div>
                <p class="truncate text-[9px] font-bold uppercase tracking-wider text-zinc-500 sm:text-[11px]">Saldo</p>
            </div>
            <p class="mt-1.5 truncate text-sm font-black tabular-nums text-zinc-950 sm:mt-3 sm:text-base"><?= esc(rupiah($account['balance'])) ?></p>
        </div>
        <div class="rounded-2xl border border-zinc-100 bg-white p-3 shadow-sm sm:rounded-3xl sm:p-4">
            <div class="flex items-center gap-1.5 sm:gap-2">
                <div class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-700 sm:h-8 sm:w-8">
                    <span class="material-symbols-rounded text-[11px] sm:text-sm">south_west</span>
                </div>
                <p class="truncate text-[9px] font-bold uppercase tracking-wider text-zinc-500 sm:text-[11px]">Masuk</p>
            </div>
            <p class="mt-1.5 truncate text-sm font-black tabular-nums text-zinc-950 sm:mt-3 sm:text-base"><?= esc(rupiah($account['income'])) ?></p>
        </div>
        <div class="rounded-2xl border border-zinc-100 bg-white p-3 shadow-sm sm:rounded-3xl sm:p-4">
            <div class="flex items-center gap-1.5 sm:gap-2">
                <div class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-rose-100 text-rose-700 sm:h-8 sm:w-8">
                    <span class="material-symbols-rounded text-[11px] sm:text-sm">north_east</span>
                </div>
                <p class="truncate text-[9px] font-bold uppercase tracking-wider text-zinc-500 sm:text-[11px]">Keluar</p>
            </div>
            <p class="mt-1.5 truncate text-sm font-black tabular-nums text-zinc-950 sm:mt-3 sm:text-base"><?= esc(rupiah($account['expense'])) ?></p>
        </div>
        <div class="rounded-2xl border border-zinc-100 bg-white p-3 shadow-sm sm:rounded-3xl sm:p-4">
            <div class="flex items-center gap-1.5 sm:gap-2">
                <div class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-indigo-700 sm:h-8 sm:w-8">
                    <span class="material-symbols-rounded text-[11px] sm:text-sm">receipt_long</span>
                </div>
                <p class="truncate text-[9px] font-bold uppercase tracking-wider text-zinc-500 sm:text-[11px]">Transaksi</p>
            </div>
            <p class="mt-1.5 truncate text-sm font-black text-zinc-950 sm:mt-3 sm:text-base"><?= esc((string) $account['transaction_count']) ?></p>
        </div>
            </div>

            <section class="rounded-3xl bg-white p-4 shadow-sm">
        <div class="flex items-center justify-between">
            <h2 class="text-base font-semibold text-zinc-950">Kegiatan Terkait</h2>
            <p class="text-xs text-zinc-500">Memakai rekening ini</p>
        </div>
        <div class="mt-3 space-y-3">
            <?php if ($accountActivities === []): ?>
                <div class="px-4 pb-4">
                    <?= view('partials/empty_state', [
                        'icon' => 'folder_supervised',
                        'title' => 'Belum ada kegiatan terkait.',
                        'description' => 'Kegiatan akan muncul dari mutasi rekening atau dompet ini saat sudah ada transaksi yang terkait.',
                        'compact' => true,
                    ]) ?>
                </div>
            <?php endif; ?>
            <?php foreach ($accountActivities as $activity): ?>
                <?= view('partials/activity_card', ['activity' => $activity]) ?>
            <?php endforeach; ?>
        </div>
            </section>

            <section class="rounded-3xl bg-white pt-4 pb-1 shadow-sm overflow-hidden">
        <div class="flex items-center justify-between px-4">
            <h2 class="text-base font-semibold text-zinc-950">Penerima Terlibat</h2>
        </div>
        <div class="mt-4">
            <?php if (empty($involvedReceivers)): ?>
                <div class="px-4 pb-4">
                    <?= view('partials/empty_state', [
                        'icon' => 'groups',
                        'title' => 'Belum ada penerima terlibat.',
                        'description' => 'Penerima akan muncul dari transaksi yang memakai rekening atau dompet ini.',
                        'compact' => true,
                    ]) ?>
                </div>
            <?php else: ?>
                <div class="flex flex-nowrap gap-3 overflow-x-auto px-4 pb-4 pt-2 snap-x snap-mandatory scroll-pl-4" style="scrollbar-width: none;">
                    <style>
                        .overflow-x-auto::-webkit-scrollbar { display: none; }
                    </style>
                    
                    <?php foreach ($involvedReceivers as $receiver): ?>
                        <?= view('partials/receiver_card', ['receiver' => $receiver, 'widthClass' => 'w-48 sm:w-52']) ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
            </section>
        </div>

        <div class="space-y-3">
            <section class="rounded-3xl bg-white py-4 shadow-sm">
        <div class="px-4 flex items-center justify-between">
            <h2 class="text-base font-semibold text-zinc-950">Transaksi Terkait</h2>
            <p class="text-xs text-zinc-500"><?= esc($account['name']) ?></p>
        </div>
        <div class="mt-3 divide-y divide-zinc-100">
            <?php if ($accountTransactions === []): ?>
                <div class="px-4">
                    <?= view('partials/empty_state', [
                        'icon' => 'receipt_long',
                        'title' => 'Belum ada transaksi terkait.',
                        'description' => 'Transaksi akan muncul di sini setelah rekening atau dompet ini dipakai dalam pencatatan.',
                        'compact' => true,
                    ]) ?>
                </div>
            <?php endif; ?>
            <?php foreach ($accountTransactions as $transaction): ?>
                <?= view('partials/transaction_item', ['transaction' => $transaction]) ?>
            <?php endforeach; ?>
        </div>
        <?= view('partials/pagination_controls', [
            'pagination' => $accountTransactionPagination,
            'prevUrl' => route_query('rekening/' . $account['slug'], ['periode' => service('request')->getGet('periode') ?: 'semua', 'unit' => service('request')->getGet('unit') ?: 'semua', 'kegiatan' => service('request')->getGet('kegiatan') ?: 'semua', 'transaksi_page' => $accountTransactionPagination['prevPage']]),
            'nextUrl' => route_query('rekening/' . $account['slug'], ['periode' => service('request')->getGet('periode') ?: 'semua', 'unit' => service('request')->getGet('unit') ?: 'semua', 'kegiatan' => service('request')->getGet('kegiatan') ?: 'semua', 'transaksi_page' => $accountTransactionPagination['nextPage']]),
        ]) ?>
            </section>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
