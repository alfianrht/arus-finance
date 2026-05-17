<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<div class="space-y-3">
    <?= view('partials/top_nav_back', [
        'title' => 'Penerima',
        'subtitle' => 'Master Data',
        'backUrl' => $backUrl,
    ]) ?>

    <div class="flex justify-end">
        <a href="<?= site_url('pengaturan/penerima/tambah') ?>" class="inline-flex h-11 items-center justify-center gap-2 rounded-full bg-zinc-950 px-5 text-sm font-semibold text-white">
            <span class="material-symbols-rounded text-base" aria-hidden="true">add</span>
            <span>Tambah Penerima</span>
        </a>
    </div>

    <section class="relative rounded-3xl border border-zinc-950 bg-white p-5">
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-zinc-500">Kontak, Vendor & Tim</p>
                <p class="mt-3 text-lg font-semibold text-zinc-950"><?= esc(count($receivers)) ?> kontak terdaftar</p>
                <p class="mt-1 text-sm text-zinc-500">Master kontak digunakan untuk mempercepat pencatatan pengeluaran dan pembayaran rutin untuk semua pihak.</p>
            </div>
            <span class="absolute top-2 right-2 rounded-full bg-lime-100 px-3 py-2 text-xs font-medium text-lime-950">Master Kontak</span>
        </div>
    </section>

    <section class="space-y-3">
        <?php if (empty($receivers)): ?>
            <?= view('partials/empty_state', [
                'icon' => 'contacts', 'title' => 'Belum Ada Penerima',
                'message' => 'Penerima mempercepat pencatatan pengeluaran. Tambahkan kontak vendor, staf, atau pihak ketiga lainnya.',
                'actionUrl' => site_url('pengaturan/penerima/tambah'), 'actionLabel' => 'Tambah Penerima',
            ]) ?>
        <?php else: ?>
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                <?php foreach ($receivers as $receiver): ?>
                    <div class="rounded-3xl bg-white p-4 shadow-sm">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="text-xs font-medium text-blue-600 mb-1"><?= esc($receiver['type'] ?? 'Lainnya') ?></p>
                                <p class="truncate text-base font-semibold text-zinc-950"><?= esc($receiver['name']) ?></p>
                                <?php if (!empty($receiver['bank_account'])): ?>
                                    <p class="mt-1 truncate text-sm text-zinc-500"><?= esc($receiver['bank_account']) ?></p>
                                <?php endif; ?>
                                <?php if (!empty($receiver['note'])): ?>
                                    <p class="mt-2 text-xs text-zinc-500 line-clamp-2"><?= esc($receiver['note']) ?></p>
                                <?php endif; ?>
                                <div class="mt-3 flex flex-wrap gap-2">
                                    <?php if (!empty($receiver['nik'])): ?>
                                        <p class="inline-flex rounded-full bg-zinc-100 px-3 py-2 text-[10px] font-medium text-zinc-700">NIK: <?= esc($receiver['nik']) ?></p>
                                    <?php endif; ?>
                                    <?php if (!empty($receiver['npwp'])): ?>
                                        <p class="inline-flex rounded-full bg-zinc-100 px-3 py-2 text-[10px] font-medium text-zinc-700">NPWP: <?= esc($receiver['npwp']) ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="mt-3 flex items-center justify-end gap-3 border-t border-zinc-100 pt-3">
                            <button type="button" onclick="openDeleteModal('<?= site_url('pengaturan/penerima/' . $receiver['slug'] . '/hapus') ?>', '<?= esc($receiver['name'], 'js') ?>', '<?= csrf_hash() ?>')" class="text-sm font-medium text-rose-600">Hapus</button>
                            <a href="<?= site_url('pengaturan/penerima/' . $receiver['slug'] . '/edit') ?>" class="text-sm font-medium text-zinc-700">Edit</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</div>

<?= view('partials/confirm_delete_modal') ?>
<?= $this->endSection() ?>
