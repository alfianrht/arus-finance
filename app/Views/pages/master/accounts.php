<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<div class="space-y-3">
    <?= view('partials/top_nav_back', [
        'title' => 'Rekening / Dompet',
        'subtitle' => 'Master Data',
        'backUrl' => $backUrl,
    ]) ?>

    <div class="flex justify-end">
        <a href="<?= site_url('pengaturan/rekening-dompet/tambah') ?>" class="inline-flex h-11 items-center justify-center gap-2 rounded-full bg-zinc-950 px-5 text-sm font-semibold text-white">
            <span class="material-symbols-rounded text-base" aria-hidden="true">add</span>
            <span>Tambah Rekening</span>
        </a>
    </div>

    <section class="relative rounded-3xl border border-zinc-950 bg-white p-5">
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-zinc-500">Arus Uang</p>
                <p class="mt-3 text-lg font-semibold text-zinc-950"><?= esc(count($accountSummaries)) ?> sumber dan tujuan dana</p>
                <p class="mt-1 text-sm text-zinc-500">Master ini dipakai untuk uang masuk, biaya, pindah dana, dan tiap rekening langsung menyimpan pos laporan terkaitnya.</p>
            </div>
            <span class="absolute top-2 right-2 rounded-full bg-zinc-100 px-3 py-2 text-xs font-medium text-zinc-700">Tanpa mapping terpisah</span>
        </div>
    </section>

    <section class="space-y-3">
        <?php if (session()->getFlashdata('error')): ?>
            <div class="rounded-3xl border border-rose-300 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-950">
                <?= (string) session()->getFlashdata('error') ?>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('success')): ?>
            <div class="rounded-3xl border border-lime-300 bg-lime-50 px-4 py-3 text-sm font-medium text-lime-950">
                <?= (string) session()->getFlashdata('success') ?>
            </div>
        <?php endif; ?>

        <?php if (empty($accountSummaries)): ?>
            <?= view('partials/empty_state', [
                'icon'        => 'account_balance',
                'title'       => 'Belum Ada Rekening',
                'message'     => 'Rekening / Dompet menyimpan sumber dan tujuan dana. Tambahkan rekening pertama agar transaksi bisa dicatat.',
                'actionUrl'   => site_url('pengaturan/rekening-dompet/tambah'),
                'actionLabel' => 'Tambah Rekening',
            ]) ?>
        <?php else: ?>
            <?php foreach ($accountSummaries as $account): ?>
                <div class="space-y-2">
                    <?php
                    $masterAccount = $account;
                    $masterAccount['detail_url'] = site_url('pengaturan/rekening-dompet/' . $account['slug'] . '/edit');
                    ?>
                    <?= view('partials/account_card', ['account' => $masterAccount, 'cardWidthClass' => 'w-full']) ?>
                    <div class="flex items-center justify-between px-1">
                        <p class="text-xs text-zinc-500"><?= esc($account['report_position_name'] ?? 'Belum dipetakan') ?></p>
                        <div class="flex items-center gap-3">
                            <button
                                type="button"
                                onclick="openDeleteModal('<?= site_url('pengaturan/rekening-dompet/' . $account['slug'] . '/hapus') ?>', '<?= esc($account['name'], 'js') ?>', '<?= csrf_hash() ?>')"
                                class="text-sm font-medium text-rose-600"
                            >Hapus</button>
                            <a href="<?= site_url('pengaturan/rekening-dompet/' . $account['slug'] . '/edit') ?>" class="text-sm font-medium text-zinc-700">Edit</a>
                            <a href="<?= esc(site_url('rekening/' . $account['slug'])) ?>" class="text-sm font-medium text-zinc-700">Lihat Mutasi</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>

    <section class="rounded-3xl bg-white p-4 shadow-sm">
        <div class="flex items-center justify-between gap-3">
            <h2 class="text-base font-semibold text-zinc-950">Field yang Disiapkan</h2>
            <a href="<?= site_url('pengaturan/rekening-dompet/tambah') ?>" class="text-sm font-medium text-zinc-700">Buka Form</a>
        </div>
        <div class="mt-4 grid gap-3 sm:grid-cols-2">
            <?php foreach (['Nama rekening / dompet', 'Jenis penyimpanan dana', 'Singkatan / logo', 'Pos laporan terkait', 'Catatan penggunaan', 'Status aktif'] as $field): ?>
                <a href="<?= site_url('pengaturan/rekening-dompet/tambah') ?>" class="block rounded-2xl border border-zinc-200 px-4 py-3 text-sm text-zinc-700"><?= esc($field) ?></a>
            <?php endforeach; ?>
        </div>
    </section>
</div>

<?= view('partials/confirm_delete_modal') ?>
<?= $this->endSection() ?>
