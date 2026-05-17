<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<div class="space-y-3">
    <header class="flex items-center gap-3">
        <a href="<?= esc($backUrl) ?>" class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-white text-zinc-700 shadow-sm">
            <span class="material-symbols-rounded text-base" aria-hidden="true">arrow_back</span>
        </a>
        <div>
            <p class="text-sm text-zinc-500">Master Data</p>
            <p class="text-2xl font-semibold tracking-tight text-zinc-950">Kegiatan</p>
        </div>
    </header>

    <div class="flex justify-end">
        <a href="<?= site_url('pengaturan/kegiatan/tambah') ?>" class="inline-flex h-11 items-center justify-center gap-2 rounded-full bg-zinc-950 px-5 text-sm font-semibold text-white">
            <span class="material-symbols-rounded text-base" aria-hidden="true">add</span>
            <span>Tambah Kegiatan</span>
        </a>
    </div>

    <section class="relative rounded-3xl border border-zinc-950 bg-white p-5">
        <div class=" flex items-start justify-between gap-4">
            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-zinc-500">Konteks Aktif</p>
                <p class="mt-3 text-lg font-semibold text-zinc-950"><?= esc(count($activitySummaries)) ?> kegiatan siap dipilih saat mencatat</p>
                <p class="mt-1 text-sm text-zinc-500">Setiap kegiatan selalu terhubung ke satu unit dan bisa memiliki rekening terkait yang berbeda.</p>
            </div>
            <span class="absolute top-2 right-2 rounded-full bg-zinc-950 px-3 py-2 text-xs font-medium text-white">Konteks level 2</span>
        </div>
    </section>

    <section class="space-y-3">
        <?php foreach ($activitySummaries as $activity): ?>
            <a href="<?= site_url('pengaturan/kegiatan/' . $activity['slug'] . '/edit') ?>" class="block rounded-3xl bg-zinc-950 p-4 text-white">
                <div class="flex items-start justify-between gap-4">
                    <div class="min-w-0">
                        <p class="text-xs font-medium uppercase tracking-wide text-white/50"><?= esc($activity['short_name'] ?? 'KGT') ?></p>
                        <p class="mt-2 text-lg font-black tracking-tight"><?= esc($activity['name']) ?></p>
                        <p class="mt-1 text-xs text-white/60"><?= esc($activity['unit_name']) ?></p>
                    </div>
                    <span class="rounded-full bg-white/10 px-3 py-2 text-xs font-medium text-white/80">Edit</span>
                </div>
                <div class="mt-4 grid grid-cols-3 gap-2 text-xs">
                    <div class="rounded-2xl bg-white/10 p-3">
                        <p class="text-white/55">Masuk</p>
                        <p class="mt-1 font-semibold text-white"><?= esc(rupiah($activity['income'])) ?></p>
                    </div>
                    <div class="rounded-2xl bg-white/10 p-3">
                        <p class="text-white/55">Biaya</p>
                        <p class="mt-1 font-semibold text-white"><?= esc(rupiah($activity['expense'])) ?></p>
                    </div>
                    <div class="rounded-2xl bg-white/10 p-3">
                        <p class="text-white/55">Saldo</p>
                        <p class="mt-1 font-semibold text-white"><?= esc(rupiah($activity['related_balance'])) ?></p>
                    </div>
                </div>
                <p class="mt-3 text-xs font-medium text-white/70"><?= esc(count($activity['related_accounts'])) ?> rekening terkait · klik untuk buka form kegiatan</p>
            </a>
        <?php endforeach; ?>
    </section>

    <section class="rounded-3xl bg-white p-4 shadow-sm">
        <div class="flex items-center justify-between gap-3">
            <h2 class="text-base font-semibold text-zinc-950">Field yang Disiapkan</h2>
            <a href="<?= site_url('pengaturan/kegiatan/tambah') ?>" class="text-sm font-medium text-zinc-700">Buka Form</a>
        </div>
        <div class="mt-4 grid gap-3 sm:grid-cols-2">
            <?php foreach (['Nama kegiatan', 'Singkatan kegiatan', 'Unit induk', 'Rekening terkait', 'Saldo terkait dummy', 'Default rekening masuk / keluar'] as $field): ?>
                <a href="<?= site_url('pengaturan/kegiatan/tambah') ?>" class="block rounded-2xl border border-zinc-200 px-4 py-3 text-sm text-zinc-700"><?= esc($field) ?></a>
            <?php endforeach; ?>
        </div>
    </section>
</div>
<?= $this->endSection() ?>
