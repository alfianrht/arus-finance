<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<div class="space-y-3">
    <?= view('partials/top_nav_back', [
        'title' => 'Kategori Transaksi',
        'subtitle' => 'Master Data',
        'backUrl' => $backUrl,
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

    <section class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
        <?php foreach ($transactionCategories as $category): ?>
            <a href="<?= site_url('pengaturan/kategori-biaya/' . $category['slug'] . '/edit') ?>" class="block rounded-3xl bg-white p-4 shadow-sm">
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
                    <span class="rounded-full bg-zinc-100 px-3 py-2 text-xs font-medium text-zinc-700">Edit</span>
                </div>
            </a>
        <?php endforeach; ?>
    </section>

    <section class="rounded-3xl bg-white p-4 shadow-sm">
        <div class="flex items-center justify-between gap-3">
            <h2 class="text-base font-semibold text-zinc-950">Field yang Disiapkan</h2>
            <a href="<?= site_url('pengaturan/kategori-biaya/tambah') ?>" class="text-sm font-medium text-zinc-700">Buka Form</a>
        </div>
        <div class="mt-4 grid gap-3 sm:grid-cols-2">
            <?php foreach (['Nama kategori', 'Jenis transaksi', 'Pos laporan terkait', 'Urutan tampil', 'Muncul sebagai kategori cepat', 'Catatan penggunaan'] as $field): ?>
                <a href="<?= site_url('pengaturan/kategori-biaya/tambah') ?>" class="block rounded-2xl border border-zinc-200 px-4 py-3 text-sm text-zinc-700"><?= esc($field) ?></a>
            <?php endforeach; ?>
        </div>
    </section>
</div>
<?= $this->endSection() ?>
