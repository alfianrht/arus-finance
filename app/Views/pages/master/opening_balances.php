<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<div class="space-y-3">
    <?= view('partials/top_nav_back', [
        'title' => 'Saldo Awal',
        'subtitle' => 'Fondasi Laporan Tahunan',
        'backUrl' => $backUrl,
    ]) ?>

    <div class="flex justify-end">
        <a href="<?= site_url('pengaturan/saldo-awal/tambah') ?>" class="inline-flex h-11 items-center justify-center gap-2 rounded-full bg-zinc-950 px-5 text-sm font-semibold text-white">
            <span class="material-symbols-rounded text-base" aria-hidden="true">add</span>
            <span>Tambah Saldo</span>
        </a>
    </div>

    <section class="rounded-3xl border border-zinc-950 bg-white p-5">
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-zinc-500">Titik Awal Posisi Keuangan</p>
                <p class="mt-3 text-lg font-semibold text-zinc-950"><?= esc(count($openingBalances)) ?> saldo awal disiapkan untuk prototype ini.</p>
                <p class="mt-1 text-sm text-zinc-500">Saldo awal tidak hanya untuk rekening, tetapi juga untuk pos neraca seperti modal, hutang, atau piutang.</p>
            </div>
            <span class="rounded-full bg-zinc-100 px-3 py-2 text-xs font-medium text-zinc-700"><?= esc(count($bookPeriods)) ?> periode buku</span>
        </div>
    </section>

    <section class="space-y-3">
        <?php foreach ($openingBalances as $balance): ?>
            <a href="<?= site_url('pengaturan/saldo-awal/' . $balance['slug'] . '/edit') ?>" class="block rounded-3xl bg-white p-4 shadow-sm">
                <div class="flex items-start justify-between gap-4">
                    <div class="min-w-0">
                        <p class="text-xs text-zinc-500"><?= esc($balance['type']) ?> · <?= esc($balance['book_period_name']) ?></p>
                        <p class="mt-2 text-base font-semibold text-zinc-950"><?= esc($balance['label']) ?></p>
                        <p class="mt-1 text-sm text-zinc-500"><?= esc($balance['report_position_name']) ?></p>
                        <p class="mt-2 text-sm text-zinc-500"><?= esc($balance['note']) ?></p>
                    </div>
                    <div class="shrink-0 text-right">
                        <p class="text-sm font-semibold text-zinc-950"><?= esc(rupiah($balance['amount'])) ?></p>
                        <p class="mt-2 text-xs text-zinc-500">Klik untuk edit</p>
                    </div>
                </div>
            </a>
        <?php endforeach; ?>
    </section>
</div>
<?= $this->endSection() ?>
