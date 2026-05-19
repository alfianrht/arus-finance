<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<div class="space-y-3">
    <?php
    $totalActivities = array_sum(array_map(static fn(array $unit): int => count($unit['activities'] ?? []), $units));
    $totalIncome = array_sum(array_map(static fn(array $unit): float => (float) ($unit['income'] ?? 0), $units));
    $totalExpense = array_sum(array_map(static fn(array $unit): float => (float) ($unit['expense'] ?? 0), $units));
    $totalBalance = array_sum(array_map(static fn(array $unit): float => (float) ($unit['related_balance'] ?? 0), $units));
    ?>
    <?= view('partials/top_nav_back', [
        'title' => 'Unit / Program',
        'subtitle' => 'Master Data',
        'backUrl' => $backUrl,
    ]) ?>

    <div class="flex justify-end">
        <a href="<?= site_url('pengaturan/unit-program/tambah') ?>" class="inline-flex h-11 items-center justify-center gap-2 rounded-full bg-zinc-950 px-5 text-sm font-semibold text-white">
            <span class="material-symbols-rounded text-base" aria-hidden="true">add</span>
            <span>Tambah Unit</span>
        </a>
    </div>

    <section class="rounded-3xl border border-zinc-100 bg-white p-5 shadow-sm">
        <div class="relative flex items-start justify-between gap-4">
            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-zinc-500">Struktur Usaha</p>
                <p class="mt-3 text-lg font-semibold text-zinc-950"><?= esc(count($units)) ?> unit aktif</p>
                <p class="mt-1 text-sm text-zinc-500">Setiap unit menaungi beberapa kegiatan dan sekarang langsung membaca ringkasan transaksi yang sudah tercatat.</p>
            </div>
            <span class="absolute top-0 right-0 rounded-full bg-lime-100 px-3 py-2 text-xs font-medium text-zinc-950">Konteks level 1</span>
        </div>
        <div class="mt-4 grid grid-cols-2 gap-2 sm:grid-cols-4">
            <div class="rounded-2xl border border-zinc-100 bg-zinc-50 px-4 py-3">
                <p class="text-[11px] font-bold uppercase tracking-wider text-zinc-500">Kegiatan</p>
                <p class="mt-1 text-base font-black text-zinc-950"><?= esc((string) $totalActivities) ?></p>
            </div>
            <div class="rounded-2xl border border-zinc-100 bg-zinc-50 px-4 py-3">
                <p class="text-[11px] font-bold uppercase tracking-wider text-zinc-500">Masuk</p>
                <p class="mt-1 text-base font-black text-zinc-950"><?= esc(rupiah($totalIncome)) ?></p>
            </div>
            <div class="rounded-2xl border border-zinc-100 bg-zinc-50 px-4 py-3">
                <p class="text-[11px] font-bold uppercase tracking-wider text-zinc-500">Keluar</p>
                <p class="mt-1 text-base font-black text-zinc-950"><?= esc(rupiah($totalExpense)) ?></p>
            </div>
            <div class="rounded-2xl border border-zinc-100 bg-zinc-50 px-4 py-3">
                <p class="text-[11px] font-bold uppercase tracking-wider text-zinc-500">Saldo</p>
                <p class="mt-1 text-base font-black text-zinc-950"><?= esc(rupiah($totalBalance)) ?></p>
            </div>
        </div>
    </section>

    <section class="space-y-3">
        <?php if (empty($units)): ?>
            <?= view('partials/empty_state', [
                'icon'        => 'account_tree',
                'title'       => 'Belum Ada Unit',
                'message'     => 'Unit / Program adalah struktur utama lembaga. Tambahkan unit pertama untuk mulai mengelola kegiatan.',
                'actionUrl'   => site_url('pengaturan/unit-program/tambah'),
                'actionLabel' => 'Tambah Unit',
            ]) ?>
        <?php else: ?>
            <?php foreach ($units as $unit): ?>
                <article class="space-y-3">
                    <?= view('partials/unit_card', ['unit' => $unit]) ?>
                    <div class="flex items-center justify-between gap-3 rounded-3xl bg-white px-4 py-3 shadow-sm">
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-zinc-950"><?= esc($unit['status_label']) ?></p>
                            <p class="mt-1 text-xs text-zinc-500"><?= esc(count($unit['activities'])) ?> kegiatan · klik kartu untuk buka form unit</p>
                        </div>
                        <div class="flex shrink-0 items-center gap-2">
                            <?php if (count($unit['activities']) === 0): ?>
                                <button
                                    type="button"
                                    onclick="openDeleteModal('<?= site_url('pengaturan/unit-program/' . $unit['slug'] . '/hapus') ?>', '<?= esc($unit['name'], 'js') ?>', '<?= csrf_hash() ?>')"
                                    class="rounded-full bg-rose-500 px-3 py-2 text-xs font-semibold text-white"
                                >Hapus</button>
                            <?php else: ?>
                                <span class="rounded-full border border-zinc-200 bg-white px-3 py-2 text-xs font-medium text-zinc-400 cursor-not-allowed" title="Unit memiliki <?= esc(count($unit['activities'])) ?> kegiatan. Hapus kegiatan terlebih dahulu.">Hapus</span>
                            <?php endif; ?>
                            <a href="<?= site_url('pengaturan/unit-program/' . $unit['slug'] . '/edit') ?>" class="rounded-full border border-zinc-200 bg-white px-3 py-2 text-xs font-semibold text-zinc-950">Edit</a>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>

    <section class="rounded-3xl bg-white p-4 shadow-sm">
        <div class="flex items-center justify-between gap-3">
            <h2 class="text-base font-semibold text-zinc-950">Field yang Disiapkan</h2>
            <a href="<?= site_url('pengaturan/unit-program/tambah') ?>" class="text-sm font-medium text-zinc-700">Buka Form</a>
        </div>
        <div class="mt-4 grid gap-3 sm:grid-cols-2">
            <?php foreach (['Nama unit / program', 'Singkatan unit', 'Status aktif', 'Urutan tampil', 'Catatan singkat', 'Daftar kegiatan turunan'] as $field): ?>
                <a href="<?= site_url('pengaturan/unit-program/tambah') ?>" class="block rounded-2xl border border-zinc-200 px-4 py-3 text-sm text-zinc-700"><?= esc($field) ?></a>
            <?php endforeach; ?>
        </div>
    </section>
</div>

<?= view('partials/confirm_delete_modal') ?>
<?= $this->endSection() ?>
