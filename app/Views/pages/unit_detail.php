<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<?php $surfaceText = surface_label($unit['short_name'] ?? $unit['name']); ?>
<div class="space-y-3">
    <header class="flex items-center gap-3">
        <a href="<?= route_query('beranda', $activeContext['query']) ?>" class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-white text-zinc-700 shadow-sm">
            <span class="material-symbols-rounded text-base" aria-hidden="true">arrow_back</span>
        </a>
        <div>
            <p class="text-sm text-zinc-500">Unit / Program</p>
            <p class="text-2xl font-semibold tracking-tight text-zinc-950"><?= esc($unit['name']) ?></p>
        </div>
    </header>

    <section class="rounded-2xl bg-lime-50 p-4">
        <p class="text-xs font-medium uppercase tracking-wide text-zinc-500">Konteks cepat untuk pencatatan</p>
        <p class="mt-2 text-sm font-semibold text-zinc-950"><?= esc($activeContext['display']) ?></p>
        <p class="mt-1 text-sm text-zinc-600">Tombol aksi di halaman ini akan langsung memakai kegiatan tersebut.</p>
    </section>

    <section class="relative overflow-hidden rounded-3xl bg-lime-400 p-5 text-zinc-950 shadow-sm">
        <div class="absolute inset-0 bg-white/10" aria-hidden="true"></div>
        <div class="relative flex items-start justify-between gap-4">
            <div class="min-w-0">
                <p class="text-xs font-medium uppercase tracking-wide text-zinc-900/60">Unit / Program</p>
                <p class="mt-2 text-2xl font-semibold leading-tight text-zinc-950"><?= esc($unit['name']) ?></p>
            </div>
            <p class="text-lg font-black uppercase tracking-tight text-zinc-950"><?= esc($surfaceText) ?></p>
        </div>

        <div class="relative mt-6">
            <p class="text-xs font-medium uppercase tracking-wide text-zinc-900/60">Laba Sementara</p>
            <p class="mt-2 text-4xl font-black tracking-tight text-zinc-950"><?= esc(rupiah($unit['surplus'])) ?></p>
            <p class="mt-3 text-sm text-zinc-900/75">Kegiatan utama: <?= esc($activeContext['activity_name']) ?></p>
        </div>

        <p class="pointer-events-none absolute -bottom-3 left-4 text-7xl font-black uppercase tracking-tight text-white/30" aria-hidden="true"><?= esc($surfaceText) ?></p>

        <div class="relative mt-4 grid grid-cols-3 gap-3 border-t border-white/50 pt-4">
            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-zinc-900/60">Masuk</p>
                <p class="mt-1 text-sm font-semibold text-zinc-950"><?= esc(rupiah($unit['income'])) ?></p>
            </div>
            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-zinc-900/60">Biaya</p>
                <p class="mt-1 text-sm font-semibold text-zinc-950"><?= esc(rupiah($unit['expense'])) ?></p>
            </div>
            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-zinc-900/60">Jumlah</p>
                <p class="mt-1 text-sm font-semibold text-zinc-950"><?= esc(count($unit['activities'])) ?> Kegiatan</p>
            </div>
        </div>

        <div class="relative mt-4 flex items-center justify-between gap-3">
            <p class="text-sm font-semibold tracking-wide text-zinc-950">•••• <?= esc(surface_tail($unit['slug'])) ?></p>
            <span class="inline-flex items-center gap-1 rounded-full bg-zinc-950 px-3 py-2 text-xs font-semibold text-white">
                <span class="material-symbols-rounded text-sm" aria-hidden="true">radio_button_checked</span>
                <span>Konteks Aktif</span>
            </span>
        </div>

        <div class="relative mt-4 grid grid-cols-2 gap-3">
            <a href="<?= esc($activeContext['masuk_url']) ?>" class="inline-flex h-12 items-center justify-center gap-2 rounded-full bg-white px-4 text-sm font-semibold text-zinc-950">
                <span class="material-symbols-rounded text-base" aria-hidden="true">add</span>
                <span>Uang Masuk</span>
            </a>
            <a href="<?= esc($activeContext['keluar_url']) ?>" class="inline-flex h-12 items-center justify-center gap-2 rounded-full bg-zinc-950 px-4 text-sm font-semibold text-white">
                <span class="material-symbols-rounded text-base" aria-hidden="true">remove</span>
                <span>Uang Keluar</span>
            </a>
        </div>
    </section>

    <section class="space-y-3">
        <div class="flex items-center justify-between">
            <h2 class="text-base font-semibold text-zinc-950">Daftar Kegiatan</h2>
            <p class="text-xs text-zinc-500">Turunan dari unit ini</p>
        </div>
        <div class="space-y-3 md:grid md:grid-cols-2 md:gap-3 md:space-y-0">
            <?php foreach ($unit['activities'] as $activity): ?>
                <?= view('partials/activity_card', ['activity' => $activity]) ?>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="rounded-3xl bg-white p-4 shadow-sm">
        <div class="flex items-center justify-between">
            <h2 class="text-base font-semibold text-zinc-950">Transaksi Terakhir Unit</h2>
            <p class="text-xs text-zinc-500"><?= esc($unit['name']) ?></p>
        </div>
        <div class="mt-3 divide-y divide-zinc-100">
            <?php if ($unitTransactions === []): ?>
                <div class="py-6 text-sm text-zinc-500">Belum ada transaksi dummy untuk unit ini.</div>
            <?php endif; ?>
            <?php foreach ($unitTransactions as $transaction): ?>
                <?= view('partials/transaction_item', ['transaction' => $transaction]) ?>
            <?php endforeach; ?>
        </div>
    </section>
</div>
<?= $this->endSection() ?>
