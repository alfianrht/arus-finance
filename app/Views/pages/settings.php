<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<div class="space-y-3">
    <?php
    $groupedShortcuts = [];
    foreach ($settingsShortcuts as $shortcut) {
        $groupedShortcuts[$shortcut['group']][] = $shortcut;
    }
    ?>
    <header class="flex items-center gap-3">
        <a href="<?= esc($backUrl) ?>" class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-white text-zinc-700 shadow-sm">
            <span class="material-symbols-rounded text-base" aria-hidden="true">arrow_back</span>
        </a>
        <div>
            <p class="text-sm text-zinc-500">Pengaturan</p>
            <p class="text-2xl font-semibold tracking-tight text-zinc-950">Master Data Arus</p>
        </div>
    </header>

    <section class="rounded-3xl border border-zinc-950 bg-white p-5">
        <p class="text-xs font-medium uppercase tracking-wide text-zinc-500">Tujuan Halaman</p>
        <p class="mt-3 text-lg font-semibold tracking-tight text-zinc-950">Menyiapkan struktur dasar agar pencatatan, konteks aktif, dan rekap berjalan konsisten.</p>
        <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">
            <div>
                <p class="text-xs text-zinc-500">Profil</p>
                <p class="mt-2 text-sm font-semibold text-zinc-950">1 lembaga</p>
            </div>
            <div>
                <p class="text-xs text-zinc-500">Unit</p>
                <p class="mt-2 text-sm font-semibold text-zinc-950"><?= esc(count($units)) ?> unit</p>
            </div>
            <div>
                <p class="text-xs text-zinc-500">Kegiatan</p>
                <p class="mt-2 text-sm font-semibold text-zinc-950"><?= esc(count($activitySummaries)) ?> kegiatan</p>
            </div>
            <div>
                <p class="text-xs text-zinc-500">Rekening & Kategori</p>
                <p class="mt-2 text-sm font-semibold text-zinc-950"><?= esc(count($accounts) + count($transactionCategories)) ?> item</p>
            </div>
            <div>
                <p class="text-xs text-zinc-500">Fondasi Laporan</p>
                <p class="mt-2 text-sm font-semibold text-zinc-950"><?= esc(count($reportPositions) + count($bookPeriods) + count($openingBalances)) ?> item</p>
            </div>
        </div>
    </section>

    <?php foreach ($groupedShortcuts as $group => $shortcuts): ?>
        <section class="space-y-3">
            <div class="flex items-center justify-between">
                <h2 class="text-base font-semibold text-zinc-950"><?= esc($group) ?></h2>
                <p class="text-xs text-zinc-500"><?= esc(count($shortcuts)) ?> halaman</p>
            </div>
            <?php foreach ($shortcuts as $shortcut): ?>
                <a href="<?= esc($shortcut['href']) ?>" class="block rounded-3xl bg-white p-4 shadow-sm">
                    <div class="relative flex items-start justify-between gap-4">
                        <div class="min-w-0">
                            <div class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-zinc-100 text-zinc-700">
                                <span class="material-symbols-rounded text-base" aria-hidden="true"><?= esc($shortcut['icon']) ?></span>
                            </div>
                            <p class="mt-4 text-base font-semibold text-zinc-950"><?= esc($shortcut['title']) ?></p>
                            <p class="mt-1 text-sm text-zinc-500"><?= esc($shortcut['description']) ?></p>
                        </div>
                        <div class="shrink-0 text-right">
                            <p class="absolute top-0 right-0 rounded-full bg-lime-100 px-3 py-2 text-xs font-medium text-lime-950"><?= esc($shortcut['meta']) ?></p>
                            <span class="mt-10 inline-flex items-center gap-1 text-sm font-medium text-zinc-700">
                                <span>Buka</span>
                                <span class="material-symbols-rounded text-base" aria-hidden="true">arrow_outward</span>
                            </span>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </section>
    <?php endforeach; ?>
</div>
<?= $this->endSection() ?>
