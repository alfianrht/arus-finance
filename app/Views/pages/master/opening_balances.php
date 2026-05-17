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
                <p class="mt-3 text-lg font-semibold text-zinc-950"><?= esc(count($openingBalances)) ?> saldo awal disiapkan</p>
                <p class="mt-1 text-sm text-zinc-500">Saldo awal tidak hanya untuk rekening, tetapi juga untuk pos neraca seperti modal, hutang, atau piutang.</p>
            </div>
            <span class="rounded-full bg-zinc-100 px-3 py-2 text-xs font-medium text-zinc-700"><?= esc(count($bookPeriods)) ?> periode buku</span>
        </div>
    </section>

    <section class="space-y-3">
        <?php if (session()->getFlashdata('error')): ?>
            <div class="rounded-3xl border border-rose-300 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-950"><?= (string) session()->getFlashdata('error') ?></div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('success')): ?>
            <div class="rounded-3xl border border-lime-300 bg-lime-50 px-4 py-3 text-sm font-medium text-lime-950"><?= (string) session()->getFlashdata('success') ?></div>
        <?php endif; ?>

        <?php if (empty($openingBalances)): ?>
            <?= view('partials/empty_state', [
                'icon' => 'account_balance_wallet', 'title' => 'Belum Ada Saldo Awal',
                'message' => 'Saldo awal menjadi titik start posisi keuangan lembaga. Tambahkan saldo untuk setiap rekening dan pos neraca.',
                'actionUrl' => site_url('pengaturan/saldo-awal/tambah'), 'actionLabel' => 'Tambah Saldo',
            ]) ?>
        <?php else: ?>
            <?php foreach ($openingBalances as $balance): ?>
                <div class="rounded-3xl bg-white p-4 shadow-sm">
                    <div class="flex items-start justify-between gap-4">
                        <div class="min-w-0">
                            <p class="text-xs text-zinc-500"><?= esc($balance['type']) ?> · <?= esc($balance['book_period_name']) ?></p>
                            <p class="mt-2 text-base font-semibold text-zinc-950"><?= esc($balance['label']) ?></p>
                            <?php if (!empty($balance['account_name'])): ?>
                                <p class="mt-1 text-sm font-medium text-lime-500"><?= esc($balance['account_name']) ?></p>
                            <?php endif; ?>
                            <p class="mt-1 text-sm text-zinc-500"><?= esc($balance['report_position_name']) ?></p>
                            <p class="mt-2 text-sm text-zinc-500"><?= esc($balance['note']) ?></p>
                        </div>
                        <div class="shrink-0 text-right">
                            <p class="text-sm font-semibold text-zinc-950"><?= esc(rupiah($balance['amount'])) ?></p>
                        </div>
                    </div>
                    <div class="mt-3 flex items-center justify-end gap-3 border-t border-zinc-100 pt-3">
                        <button type="button" onclick="openDeleteModal('<?= site_url('pengaturan/saldo-awal/' . $balance['slug'] . '/hapus') ?>', '<?= esc($balance['label'], 'js') ?>', '<?= csrf_hash() ?>')" class="text-sm font-medium text-rose-600">Hapus</button>
                        <a href="<?= site_url('pengaturan/saldo-awal/' . $balance['slug'] . '/edit') ?>" class="text-sm font-medium text-zinc-700">Edit</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>
</div>

<?= view('partials/confirm_delete_modal') ?>
<?= $this->endSection() ?>
