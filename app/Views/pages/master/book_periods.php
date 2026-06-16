<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<div class="space-y-3">
    <?= view('partials/top_nav_back', [
        'title' => 'Tahun Buku',
        'subtitle' => 'Fondasi Laporan Tahunan',
        'backUrl' => $backUrl,
        'breadcrumbs' => [
            ['label' => 'Pengaturan', 'url' => site_url('pengaturan')],
            ['label' => 'Tahun Buku'],
        ],
    ]) ?>

    <div class="flex justify-end">
        <a href="<?= site_url('pengaturan/tahun-buku/tambah') ?>" class="inline-flex h-11 items-center justify-center gap-2 rounded-full bg-zinc-950 px-5 text-sm font-semibold text-white">
            <span class="material-symbols-rounded text-base" aria-hidden="true">add</span>
            <span>Tambah Periode</span>
        </a>
    </div>

    <section class="rounded-3xl border border-zinc-950 bg-white p-5">
        <p class="text-xs font-medium uppercase tracking-wide text-zinc-500">Pengikat Saldo dan Laporan</p>
        <p class="mt-3 text-lg font-semibold text-zinc-950">Periode buku perlu disiapkan dulu sebelum saldo awal dan laporan tahunan dijalankan.</p>
        <p class="mt-1 text-sm text-zinc-500">Walau saat ini rekap masih sederhana, struktur tahun buku perlu ada dari awal agar data tidak bercampur antarperiode.</p>
    </section>

    <section class="space-y-3">
        <?php if (empty($bookPeriods)): ?>
            <?= view('partials/empty_state', [
                'icon' => 'calendar_month', 'title' => 'Belum Ada Tahun Buku',
                'message' => 'Tahun buku mengikat saldo awal dan laporan keuangan per periode. Tambahkan periode pertama.',
                'actionUrl' => site_url('pengaturan/tahun-buku/tambah'), 'actionLabel' => 'Tambah Periode',
            ]) ?>
        <?php else: ?>
            <?php foreach ($bookPeriods as $period): ?>
                <div class="rounded-3xl bg-white p-4 shadow-sm">
                    <div class="flex items-start justify-between gap-4">
                        <div class="min-w-0">
                            <p class="text-xs text-zinc-500">Periode Buku</p>
                            <p class="mt-2 text-base font-semibold text-zinc-950"><?= esc($period['name']) ?></p>
                            <p class="mt-1 text-sm text-zinc-500"><?= esc($period['start']) ?> - <?= esc($period['end']) ?></p>
                            <p class="mt-2 text-sm text-zinc-500"><?= esc($period['note']) ?></p>
                        </div>
                        <div class="shrink-0 text-right">
                            <p class="rounded-full <?= $period['status'] === 'Aktif' ? 'bg-lime-100 text-lime-950' : 'bg-zinc-100 text-zinc-700' ?> px-3 py-2 text-xs font-medium"><?= esc($period['status']) ?></p>
                        </div>
                    </div>
                    <div class="mt-3 flex items-center justify-end gap-3 border-t border-zinc-100 pt-3">
                        <button type="button" onclick="openDeleteModal('<?= site_url('pengaturan/tahun-buku/' . $period['slug'] . '/hapus') ?>', '<?= esc($period['name'], 'js') ?>', '<?= csrf_hash() ?>')" class="text-sm font-medium text-rose-600">Hapus</button>
                        <a href="<?= site_url('pengaturan/tahun-buku/' . $period['slug'] . '/edit') ?>" class="text-sm font-medium text-zinc-700">Edit</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>
</div>

<?= view('partials/confirm_delete_modal') ?>
<?= $this->endSection() ?>
