<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<div class="space-y-3">
    <?php
    $positionsByGroup = [];
    foreach ($reportPositions as $position) {
        $positionsByGroup[$position['group']][] = $position;
    }
    ?>
    <header class="flex items-center gap-3">
        <a href="<?= esc($backUrl) ?>" class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-white text-zinc-700 shadow-sm">
            <span class="material-symbols-rounded text-base" aria-hidden="true">arrow_back</span>
        </a>
        <div>
            <p class="text-sm text-zinc-500">Fondasi Laporan Tahunan</p>
            <p class="text-2xl font-semibold tracking-tight text-zinc-950">Pos Laporan</p>
        </div>
    </header>

    <div class="flex justify-end">
        <a href="<?= site_url('pengaturan/pos-laporan/tambah') ?>" class="inline-flex h-11 items-center justify-center gap-2 rounded-full bg-zinc-950 px-5 text-sm font-semibold text-white">
            <span class="material-symbols-rounded text-base" aria-hidden="true">add</span>
            <span>Tambah Pos</span>
        </a>
    </div>

    <section class="rounded-3xl border border-zinc-950 bg-white p-5">
        <div class="relative flex items-start justify-between gap-4">
            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-zinc-500">Jembatan ke Laporan</p>
                <p class="mt-3 text-lg font-semibold text-zinc-950"><?= esc(count($reportPositions)) ?> pos disiapkan untuk laba rugi, neraca, dan arus kas.</p>
                <p class="mt-1 text-sm text-zinc-500">Di tahap ini belum ada laporan tahunan, tetapi struktur klasifikasinya sudah disiapkan supaya kategori dan rekening bisa langsung dipetakan.</p>
            </div>
            <span class="absolute top-0 right-0 rounded-full bg-lime-100 px-3 py-2 text-xs font-medium text-lime-950"><?= esc(count($reportGroups)) ?> kelompok</span>
        </div>
    </section>

    <?php foreach ($positionsByGroup as $group => $positions): ?>
        <section class="space-y-3">
            <div class="flex items-center justify-between">
                <h2 class="text-base font-semibold text-zinc-950"><?= esc($group) ?></h2>
                <p class="text-xs text-zinc-500"><?= esc(count($positions)) ?> pos</p>
            </div>
            <?php foreach ($positions as $position): ?>
                <a href="<?= site_url('pengaturan/pos-laporan/' . $position['slug'] . '/edit') ?>" class="block rounded-3xl bg-white p-4 shadow-sm">
                    <div class="flex items-start justify-between gap-4">
                        <div class="min-w-0">
                            <p class="text-xs font-medium uppercase tracking-wide text-zinc-500"><?= esc($position['code']) ?></p>
                            <p class="mt-2 text-base font-semibold text-zinc-950"><?= esc($position['name']) ?></p>
                            <p class="mt-1 text-sm text-zinc-500"><?= esc($position['note']) ?></p>
                        </div>
                        <div class="shrink-0 text-right">
                            <p class="rounded-full bg-zinc-100 px-3 py-2 text-xs font-medium text-zinc-700"><?= esc($position['kind']) ?></p>
                            <p class="mt-2 text-xs text-zinc-500">Saldo normal <?= esc($position['normal_balance']) ?></p>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </section>
    <?php endforeach; ?>
</div>
<?= $this->endSection() ?>
