<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<div class="space-y-3">
    <header class="flex items-center gap-3">
        <a href="<?= esc($backUrl) ?>" class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-white text-zinc-700 shadow-sm">
            <span class="material-symbols-rounded text-base" aria-hidden="true">arrow_back</span>
        </a>
        <div>
            <p class="text-sm text-zinc-500">Master Data</p>
            <p class="text-2xl font-semibold tracking-tight text-zinc-950">Unit / Program</p>
        </div>
    </header>

    <div class="flex justify-end">
        <a href="<?= site_url('pengaturan/unit-program/tambah') ?>" class="inline-flex h-11 items-center justify-center gap-2 rounded-full bg-zinc-950 px-5 text-sm font-semibold text-white">
            <span class="material-symbols-rounded text-base" aria-hidden="true">add</span>
            <span>Tambah Unit</span>
        </a>
    </div>

    <section class="rounded-3xl border border-zinc-950 bg-white p-5">
        <div class="relative flex items-start justify-between gap-4">
            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-zinc-500">Struktur Usaha</p>
                <p class="mt-3 text-lg font-semibold text-zinc-950"><?= esc(count($units)) ?> unit aktif di prototype ini</p>
                <p class="mt-1 text-sm text-zinc-500">Setiap unit menaungi beberapa kegiatan dan menjadi layer ringkasan utama di Beranda serta Rekap.</p>
            </div>
            <span class="absolute top-0 right-0 rounded-full bg-lime-100 px-3 py-2 text-xs font-medium text-lime-950">Konteks level 1</span>
        </div>
    </section>

    <section class="space-y-3">
        <?php foreach ($units as $unit): ?>
            <a href="<?= site_url('pengaturan/unit-program/' . $unit['slug'] . '/edit') ?>" class="block rounded-3xl bg-lime-400 p-4 text-zinc-950">
                <div class="flex items-start justify-between gap-4">
                    <div class="min-w-0">
                        <p class="text-xs font-medium uppercase tracking-wide text-zinc-950/60"><?= esc($unit['short_name']) ?></p>
                        <p class="mt-2 text-xl font-black tracking-tight"><?= esc($unit['name']) ?></p>
                    </div>
                    <span class="rounded-full bg-white/70 px-3 py-2 text-xs font-semibold text-zinc-950/70">Edit</span>
                </div>
                <div class="mt-4 grid grid-cols-3 gap-2 text-xs">
                    <div class="rounded-2xl bg-white/70 p-3">
                        <p class="text-zinc-950/60">Masuk</p>
                        <p class="mt-1 font-semibold"><?= esc(rupiah($unit['income'])) ?></p>
                    </div>
                    <div class="rounded-2xl bg-white/70 p-3">
                        <p class="text-zinc-950/60">Biaya</p>
                        <p class="mt-1 font-semibold"><?= esc(rupiah($unit['expense'])) ?></p>
                    </div>
                    <div class="rounded-2xl bg-white/70 p-3">
                        <p class="text-zinc-950/60">Surplus</p>
                        <p class="mt-1 font-semibold"><?= esc(rupiah($unit['surplus'])) ?></p>
                    </div>
                </div>
                <p class="mt-3 text-xs font-medium text-zinc-950/70"><?= esc(count($unit['activities'])) ?> kegiatan · klik untuk buka form unit</p>
            </a>
        <?php endforeach; ?>
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
<?= $this->endSection() ?>
