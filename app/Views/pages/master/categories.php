<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<div class="space-y-3">
    <?= view('partials/top_nav_back', [
        'title' => 'Kategori Transaksi',
        'subtitle' => 'Master Data',
        'backUrl' => $backUrl,
        'breadcrumbs' => [
            ['label' => 'Pengaturan', 'url' => site_url('pengaturan')],
            ['label' => 'Kategori Transaksi'],
        ],
    ]) ?>

    <div class="flex justify-end">
        <a href="<?= site_url('pengaturan/kategori-biaya/tambah') ?>" class="inline-flex h-11 items-center justify-center gap-2 rounded-full bg-zinc-950 px-5 text-sm font-semibold text-white">
            <span class="material-symbols-rounded text-base" aria-hidden="true">add</span>
            <span>Tambah Kategori</span>
        </a>
    </div>

    <section class="relative rounded-3xl border border-zinc-950 bg-white p-5">
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-zinc-500">Masuk dan Keluar</p>
                <p class="mt-3 text-lg font-semibold text-zinc-950"><?= esc(count($transactionCategories)) ?> kategori transaksi aktif</p>
                <p class="mt-1 text-sm text-zinc-500">Kategori ini langsung dipakai di form uang masuk dan uang keluar, sekaligus terhubung ke pos laporan yang sesuai.</p>
            </div>
            <span class="absolute top-2 right-2 rounded-full bg-lime-100 px-3 py-2 text-xs font-medium text-lime-950">Satu master kategori</span>
        </div>
    </section>

    <section class="space-y-3">
        <?php if (empty($transactionCategories)): ?>
            <?= view('partials/empty_state', [
                'icon' => 'category', 'title' => 'Belum Ada Kategori',
                'message' => 'Kategori transaksi digunakan untuk mengelompokkan uang masuk dan keluar. Tambahkan kategori pertama.',
                'actionUrl' => site_url('pengaturan/kategori-biaya/tambah'), 'actionLabel' => 'Tambah Kategori',
            ]) ?>
        <?php else: ?>
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                <?php foreach ($transactionCategories as $category): ?>
                    <div class="rounded-3xl bg-white p-4 shadow-sm">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-xs text-zinc-500">Kategori <?= esc($category['order']) ?></p>
                                <p class="mt-2 text-base font-semibold text-zinc-950"><?= esc($category['name']) ?></p>
                                <p class="mt-1 text-sm text-zinc-500"><?= esc($category['note']) ?></p>
                                <div class="mt-3 flex flex-wrap gap-2">
                                    <p class="<?= $category['type'] === 'Masuk' ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-600' ?> inline-flex rounded-full px-3 py-2 text-xs font-medium"><?= esc($category['type']) ?></p>
                                    <p class="inline-flex rounded-full bg-zinc-100 px-3 py-2 text-xs font-medium text-zinc-700"><?= esc($category['report_position_name']) ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="mt-3 flex items-center justify-end gap-3 border-t border-zinc-100 pt-3">
                            <button type="button" onclick="openDeleteModal('<?= site_url('pengaturan/kategori-biaya/' . $category['slug'] . '/hapus') ?>', '<?= esc($category['name'], 'js') ?>', '<?= csrf_hash() ?>')" class="text-sm font-medium text-rose-600">Hapus</button>
                            <a href="<?= site_url('pengaturan/kategori-biaya/' . $category['slug'] . '/edit') ?>" class="text-sm font-medium text-zinc-700">Edit</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</div>

<?= view('partials/confirm_delete_modal') ?>
<?= $this->endSection() ?>
