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

    <section class="rounded-3xl bg-white p-4 shadow-sm">
        <div class="flex items-center justify-between">
            <h2 class="text-base font-semibold text-zinc-950">Kategori Cepat</h2>
            <p class="text-xs text-zinc-500">Arahkan ke form biaya</p>
        </div>
        <div class="mt-4 flex flex-wrap gap-2">
            <?php foreach ($quickCategories as $index => $chip): ?>
                <a href="<?= esc($chip['href']) ?>" class="<?= $index === 0 ? 'bg-lime-400 text-zinc-950' : 'bg-zinc-100 text-zinc-700' ?> rounded-full px-3 py-2 text-sm font-medium">
                    <?= esc($chip['label']) ?>
                </a>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="rounded-3xl bg-white p-4 shadow-sm">
        <div class="flex items-center justify-between">
            <h2 class="text-base font-semibold text-zinc-950">Riwayat Terakhir</h2>
            <a href="<?= site_url('rekap') ?>" class="text-xs font-medium text-zinc-500">Lihat rekap</a>
        </div>
        <div class="mt-3 divide-y divide-zinc-100">
            <?php if ($recentTransactions === []): ?>
                <div class="py-6 text-sm text-zinc-500">Belum ada transaksi di tahun buku ini.</div>
            <?php endif; ?>
            <?php foreach ($recentTransactions as $transaction): ?>
                <?= view('partials/transaction_item', ['transaction' => $transaction, 'allow_delete' => true]) ?>
            <?php endforeach; ?>
        </div>
    </section>
</div>

<?= view('partials/confirm_delete_modal') ?>
<?= $this->endSection() ?>
