<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<?php $surfaceText = surface_label($activity['short_name'] ?? $activity['name']); ?>
<div class="space-y-3">
    <?= view('partials/top_nav_back', [
        'title' => $activity['name'],
        'subtitle' => $activity['unit_name'],
        'backUrl' => $backUrl ?? site_url('rekap'),
    ]) ?>

    <section class="relative overflow-hidden rounded-3xl bg-zinc-950 p-5 text-white shadow-sm">
        <div class="absolute inset-0 bg-white/5" aria-hidden="true"></div>
        <div class="relative flex items-start justify-between gap-4">
            <div class="min-w-0">
                <p class="text-xs font-medium uppercase tracking-wide text-zinc-400">Kegiatan</p>
                <p class="mt-2 text-2xl font-semibold leading-tight text-white"><?= esc($activity['name']) ?></p>
                <p class="mt-2 text-sm text-zinc-400"><?= esc($activity['unit_name']) ?></p>
            </div>
            <p class="text-lg font-black uppercase tracking-tight text-white"><?= esc($surfaceText) ?></p>
        </div>

        <div class="relative mt-6">
            <p class="text-xs font-medium uppercase tracking-wide text-zinc-400">Saldo Terkait</p>
            <p class="mt-2 text-4xl font-black tracking-tight text-white"><?= esc(rupiah($activity['related_balance'] ?? 0)) ?></p>
            <?php if (!empty($activity['related_accounts'])): ?>
            <p class="mt-3 text-sm text-zinc-300"><?= esc(implode(', ', $activity['related_accounts'])) ?></p>
            <?php else: ?>
            <p class="mt-3 text-sm text-zinc-400">Semua Rekening</p>
            <?php endif; ?>
        </div>

        <p class="pointer-events-none absolute -bottom-3 left-4 text-7xl font-black uppercase tracking-tight text-white/10" aria-hidden="true"><?= esc($surfaceText) ?></p>

        <div class="relative mt-4 grid grid-cols-2 gap-3 border-t border-white/10 pt-4 sm:grid-cols-4">
            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-zinc-500">Masuk</p>
                <p class="mt-1 text-sm font-semibold text-white"><?= esc(rupiah($activity['income'])) ?></p>
            </div>
            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-zinc-500">Biaya</p>
                <p class="mt-1 text-sm font-semibold text-white"><?= esc(rupiah($activity['expense'])) ?></p>
            </div>
            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-zinc-500">Surplus</p>
                <p class="mt-1 text-sm font-semibold text-white"><?= esc(rupiah($activity['surplus'])) ?></p>
            </div>
            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-zinc-500">Saldo Terkait</p>
                <p class="mt-1 text-sm font-semibold text-white"><?= esc(rupiah($activity['related_balance'] ?? 0)) ?></p>
            </div>
        </div>

        <?php if (isset($activeContext)): ?>
        <div class="relative mt-4 flex items-center justify-between gap-3">
            <p class="text-sm font-semibold tracking-wide text-zinc-300">•••• <?= esc(surface_tail($activity['slug'] ?? $activity['name'])) ?></p>
            <span class="inline-flex items-center gap-1 rounded-full bg-lime-400 px-3 py-2 text-xs font-semibold text-zinc-950">
                <span class="material-symbols-rounded text-sm" aria-hidden="true">radio_button_checked</span>
                <span>Konteks Aktif</span>
            </span>
        </div>

        <div class="relative mt-4 grid grid-cols-2 gap-3">
            <a href="<?= esc($activeContext['masuk_url']) ?>" class="inline-flex h-12 items-center justify-center gap-2 rounded-full bg-lime-400 px-4 text-sm font-semibold text-zinc-950">
                <span class="material-symbols-rounded text-base" aria-hidden="true">add</span>
                <span>Uang Masuk</span>
            </a>
            <a href="<?= esc($activeContext['keluar_url']) ?>" class="inline-flex h-12 items-center justify-center gap-2 rounded-full bg-white px-4 text-sm font-semibold text-zinc-950">
                <span class="material-symbols-rounded text-base" aria-hidden="true">remove</span>
                <span>Uang Keluar</span>
            </a>
        </div>
        <?php endif; ?>
    </section>

    <section class="rounded-3xl bg-white p-4 shadow-sm">
        <div class="flex items-center justify-between">
            <h2 class="text-base font-semibold text-zinc-950">Rincian Biaya per Kategori</h2>
            <p class="text-xs text-zinc-500">Kegiatan aktif</p>
        </div>
        <div class="mt-3 space-y-3">
            <?php $hasCategoryCost = false; ?>
            <?php foreach ($categoryBreakdown as $item): ?>
                <?php if ($item['total_amount'] <= 0) {
                    continue;
                } ?>
                <?php $hasCategoryCost = true; ?>
                <div class="flex items-center justify-between rounded-2xl bg-zinc-50 px-4 py-3">
                    <span class="text-sm font-medium text-zinc-800"><?= esc($item['category_name']) ?></span>
                    <span class="text-sm font-semibold text-zinc-950"><?= esc(rupiah($item['total_amount'])) ?></span>
                </div>
            <?php endforeach; ?>
            <?php if (! $hasCategoryCost): ?>
                <?= view('partials/empty_state', [
                    'icon' => 'sell',
                    'title' => 'Belum ada rincian biaya.',
                    'description' => 'Biaya per kategori akan muncul setelah ada transaksi pengeluaran pada kegiatan ini.',
                    'compact' => true,
                ]) ?>
            <?php endif; ?>
        </div>
    </section>

    <section class="rounded-3xl bg-white py-4 shadow-sm">
        <div class="px-4 flex items-center justify-between">
            <h2 class="text-base font-semibold text-zinc-950">Pindah Dana</h2>
            <span class="rounded-full bg-sky-50 px-3 py-2 text-xs font-medium text-sky-700">Bukan biaya</span>
        </div>
        <div class="mt-3 divide-y divide-zinc-100">
            <?php if ($transferItems === []): ?>
                <div class="px-4 pb-1">
                    <?= view('partials/empty_state', [
                        'icon' => 'swap_horiz',
                        'title' => 'Belum ada pindah dana.',
                        'description' => 'Pindah dana akan muncul di sini saat ada perpindahan antar rekening pada kegiatan ini.',
                        'compact' => true,
                    ]) ?>
                </div>
            <?php endif; ?>
            <?php foreach ($transferItems as $transaction): ?>
                <?= view('partials/transaction_item', ['transaction' => $transaction]) ?>
            <?php endforeach; ?>
        </div>
        <?= view('partials/pagination_controls', [
            'pagination' => $activityTransferPagination,
            'prevUrl' => route_query('kegiatan/' . $activity['slug'], ['periode' => service('request')->getGet('periode') ?: 'semua', 'unit' => service('request')->getGet('unit') ?: 'semua', 'mutasi_page' => $activityTransferPagination['prevPage'], 'transaksi_page' => $activityTransactionPagination['page']]),
            'nextUrl' => route_query('kegiatan/' . $activity['slug'], ['periode' => service('request')->getGet('periode') ?: 'semua', 'unit' => service('request')->getGet('unit') ?: 'semua', 'mutasi_page' => $activityTransferPagination['nextPage'], 'transaksi_page' => $activityTransactionPagination['page']]),
        ]) ?>
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
                        'description' => 'Penerima akan muncul dari transaksi honor atau pengeluaran yang terkait ke kegiatan ini.',
                        'compact' => true,
                    ]) ?>
                </div>
            <?php else: ?>
                <div class="flex flex-nowrap gap-3 overflow-x-auto px-4 pb-4 snap-x snap-mandatory scroll-p-4" style="scrollbar-width: none;">
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

    <section class="rounded-3xl bg-white p-4 shadow-sm">
        <div class="flex items-center justify-between">
            <h2 class="text-base font-semibold text-zinc-950">Rekening Terlibat</h2>
            <p class="text-xs text-zinc-500">Dipakai kegiatan ini</p>
        </div>
        <div class="mt-3 space-y-3">
            <?php if ($involvedAccounts === []): ?>
                <?= view('partials/empty_state', [
                    'icon' => 'account_balance_wallet',
                    'title' => 'Belum ada rekening terlibat.',
                    'description' => 'Rekening akan muncul saat ada transaksi yang memakai rekening atau dompet pada kegiatan ini.',
                    'compact' => true,
                ]) ?>
            <?php endif; ?>
            <?php foreach ($involvedAccounts as $account): ?>
                <a href="<?= esc($account['detail_url']) ?>" class="block rounded-2xl bg-zinc-50 p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-zinc-950"><?= esc($account['name']) ?></p>
                            <p class="mt-1 text-xs text-zinc-500"><?= esc($account['kind']) ?><?= !empty($account['mark']) ? ' · ' . esc($account['mark']) : '' ?></p>
                        </div>
                        <span class="inline-flex items-center gap-1 rounded-full bg-white px-3 py-2 text-xs font-medium text-zinc-700">
                            <span class="material-symbols-rounded text-sm" aria-hidden="true">arrow_outward</span>
                            <span>Detail</span>
                        </span>
                    </div>
                    <div class="mt-3 grid grid-cols-3 gap-2 text-xs">
                        <div>
                            <p class="text-zinc-500">Masuk</p>
                            <p class="mt-1 font-semibold text-emerald-600"><?= esc(rupiah($account['income'])) ?></p>
                        </div>
                        <div>
                            <p class="text-zinc-500">Biaya</p>
                            <p class="mt-1 font-semibold text-rose-500"><?= esc(rupiah($account['expense'])) ?></p>
                        </div>
                        <div>
                            <p class="text-zinc-500">Transaksi</p>
                            <p class="mt-1 font-semibold text-zinc-950"><?= esc((string) $account['transaction_count']) ?></p>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="rounded-3xl bg-white p-4 shadow-sm">
        <div class="flex items-center justify-between">
            <h2 class="text-base font-semibold text-zinc-950">Transaksi Terakhir Kegiatan</h2>
            <p class="text-xs text-zinc-500"><?= esc($activity['name']) ?></p>
        </div>
        <div class="mt-3 divide-y divide-zinc-100">
            <?php if ($activityTransactions === []): ?>
                <div class="px-4 pb-1">
                    <?= view('partials/empty_state', [
                        'icon' => 'receipt_long',
                        'title' => 'Belum ada transaksi untuk kegiatan ini.',
                        'description' => 'Transaksi terbaru akan muncul di sini setelah ada pencatatan pada kegiatan ini.',
                        'compact' => true,
                    ]) ?>
                </div>
            <?php endif; ?>
            <?php foreach ($activityTransactions as $transaction): ?>
                <?= view('partials/transaction_item', ['transaction' => $transaction]) ?>
            <?php endforeach; ?>
        </div>
        <?= view('partials/pagination_controls', [
            'pagination' => $activityTransactionPagination,
            'prevUrl' => route_query('kegiatan/' . $activity['slug'], ['periode' => service('request')->getGet('periode') ?: 'semua', 'unit' => service('request')->getGet('unit') ?: 'semua', 'transaksi_page' => $activityTransactionPagination['prevPage'], 'mutasi_page' => $activityTransferPagination['page']]),
            'nextUrl' => route_query('kegiatan/' . $activity['slug'], ['periode' => service('request')->getGet('periode') ?: 'semua', 'unit' => service('request')->getGet('unit') ?: 'semua', 'transaksi_page' => $activityTransactionPagination['nextPage'], 'mutasi_page' => $activityTransferPagination['page']]),
        ]) ?>
    </section>
</div>
<?= $this->endSection() ?>
