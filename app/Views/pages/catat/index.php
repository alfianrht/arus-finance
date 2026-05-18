<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<div class="space-y-3">
    <?= view('partials/top_nav_back', [
        'title' => 'Catat',
        'subtitle' => 'Pilih aksi cepat untuk konteks yang sedang aktif.',
        'showBackButton' => false,
    ]) ?>

    <?= $this->include('partials/active_context') ?>

    <div class="grid grid-cols-2 gap-3">
        <a href="<?= esc($activeContext['masuk_url']) ?>" class="inline-flex h-16 items-center justify-center gap-2 rounded-3xl bg-lime-400 px-4 text-sm font-semibold text-zinc-950">
            <span class="material-symbols-rounded text-lg" aria-hidden="true">arrow_downward</span>
            <span>Uang Masuk</span>
        </a>
        <a href="<?= esc($activeContext['keluar_url']) ?>" class="inline-flex h-16 items-center justify-center gap-2 rounded-3xl bg-zinc-950 px-4 text-sm font-semibold text-white">
            <span class="material-symbols-rounded text-lg" aria-hidden="true">arrow_outward</span>
            <span>Uang Keluar</span>
        </a>
    </div>

    <section class="rounded-3xl bg-white py-4 shadow-sm">
        <div class="px-4 flex items-center justify-between gap-3">
            <h2 class="text-base font-semibold text-zinc-950">Riwayat Transaksi</h2>
            <a href="<?= site_url('rekap') ?>" class="text-xs font-medium text-zinc-500">Lihat rekap</a>
        </div>
        <div class="mt-3 px-4 flex flex-wrap gap-2">
            <?php foreach ($transactionFilters as $filter): ?>
                <?php $isActive = $selectedTransactionFilter === $filter['key']; ?>
                <a
                    href="<?= esc(route_query('catat', ['jenis' => $filter['key'] === 'semua' ? null : $filter['key'], 'page' => null])) ?>"
                    class="<?= $isActive ? 'bg-zinc-950 text-white' : 'border border-zinc-200 bg-white text-zinc-600' ?> inline-flex items-center rounded-full px-3 py-2 text-xs font-semibold transition-colors"
                >
                    <?= esc($filter['label']) ?>
                </a>
            <?php endforeach; ?>
        </div>
        <div class="mt-3 divide-y divide-zinc-100">
            <?php if ($recentTransactions === []): ?>
                <div class="py-6 text-sm text-zinc-500">Belum ada transaksi untuk filter ini di tahun buku aktif.</div>
            <?php endif; ?>
            <?php foreach ($recentTransactions as $transaction): ?>
                <?= view('partials/transaction_item', ['transaction' => $transaction, 'allow_delete' => true]) ?>
            <?php endforeach; ?>
        </div>
        <?php if (($transactionPagination['totalPages'] ?? 1) > 1): ?>
            <div class="mt-4 flex items-center justify-between gap-3 border-t border-zinc-100 pt-4">
                <?php if ($transactionPagination['hasPrev']): ?>
                    <a
                        href="<?= esc(route_query('catat', ['jenis' => $selectedTransactionFilter === 'semua' ? null : $selectedTransactionFilter, 'page' => $transactionPagination['prevPage']])) ?>"
                        class="inline-flex items-center gap-1 rounded-full border border-zinc-200 bg-white px-3 py-2 text-xs font-semibold text-zinc-700"
                    >
                        <span class="material-symbols-rounded text-sm" aria-hidden="true">chevron_left</span>
                        <span>Sebelumnya</span>
                    </a>
                <?php else: ?>
                    <span class="inline-flex items-center gap-1 rounded-full border border-zinc-100 bg-zinc-50 px-3 py-2 text-xs font-semibold text-zinc-300">
                        <span class="material-symbols-rounded text-sm" aria-hidden="true">chevron_left</span>
                        <span>Sebelumnya</span>
                    </span>
                <?php endif; ?>

                <p class="text-xs font-medium text-zinc-500">
                    Halaman <?= esc((string) $transactionPagination['page']) ?> dari <?= esc((string) $transactionPagination['totalPages']) ?>
                </p>

                <?php if ($transactionPagination['hasNext']): ?>
                    <a
                        href="<?= esc(route_query('catat', ['jenis' => $selectedTransactionFilter === 'semua' ? null : $selectedTransactionFilter, 'page' => $transactionPagination['nextPage']])) ?>"
                        class="inline-flex items-center gap-1 rounded-full border border-zinc-200 bg-white px-3 py-2 text-xs font-semibold text-zinc-700"
                    >
                        <span>Berikutnya</span>
                        <span class="material-symbols-rounded text-sm" aria-hidden="true">chevron_right</span>
                    </a>
                <?php else: ?>
                    <span class="inline-flex items-center gap-1 rounded-full border border-zinc-100 bg-zinc-50 px-3 py-2 text-xs font-semibold text-zinc-300">
                        <span>Berikutnya</span>
                        <span class="material-symbols-rounded text-sm" aria-hidden="true">chevron_right</span>
                    </span>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </section>
</div>

<?= view('partials/confirm_delete_modal') ?>
<?= $this->endSection() ?>
