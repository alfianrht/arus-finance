<?php
$activityCount = count($unit['activities']);
$surfaceText = surface_label($unit['short_name'] ?? $unit['name']);
?>

<article class="space-y-3">
    <a href="<?= esc($unit['detail_url']) ?>" class="relative block overflow-hidden rounded-3xl bg-lime-400 p-4 text-zinc-950 shadow-sm">
        <div class="absolute inset-0 bg-white/10" aria-hidden="true"></div>
        <div class="relative flex items-start justify-between gap-4">
            <div class="min-w-0">
                <p class="text-xs font-medium uppercase tracking-wide text-zinc-900/60">Unit / Program</p>
                <h3 class="mt-1.5 text-lg font-semibold leading-tight text-zinc-950"><?= esc($unit['name']) ?></h3>
            </div>
            <p class="text-lg font-black uppercase tracking-tight text-zinc-950"><?= esc($surfaceText) ?></p>
        </div>

        <div class="relative mt-4">
            <p class="text-xs font-medium uppercase tracking-wide text-zinc-900/60">Laba Sementara</p>
            <p class="mt-1.5 text-2xl font-black tracking-tight text-zinc-950"><?= esc(rupiah($unit['surplus'])) ?></p>
            <p class="mt-2 text-xs text-zinc-900/75">Kegiatan utama: <?= esc($unit['quick_activity_name']) ?></p>
        </div>

        <p class="pointer-events-none absolute bottom-12 left-3 text-6xl font-black uppercase tracking-tight text-white/25" aria-hidden="true"><?= esc($surfaceText) ?></p>

        <div class="relative mt-4 grid grid-cols-3 gap-2 border-t border-white/50 pt-3">
            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-zinc-900/60">Masuk</p>
                <p class="mt-1 text-xs font-semibold text-zinc-950"><?= esc(rupiah($unit['income'])) ?></p>
            </div>
            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-zinc-900/60">Biaya</p>
                <p class="mt-1 text-xs font-semibold text-zinc-950"><?= esc(rupiah($unit['expense'])) ?></p>
            </div>
            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-zinc-900/60">Jumlah</p>
                <p class="mt-1 text-xs font-semibold text-zinc-950"><?= esc($activityCount) ?> Kegiatan</p>
            </div>
        </div>

        <div class="relative mt-3 flex items-center justify-between gap-3">
            <p class="text-xs font-semibold tracking-wide text-zinc-950">•••• <?= esc(surface_tail($unit['slug'])) ?></p>
            <span class="inline-flex items-center gap-1 rounded-full bg-zinc-950 px-3 py-1.5 text-[11px] font-semibold text-white">
                <span class="material-symbols-rounded text-sm" aria-hidden="true">stacks</span>
                <span><?= esc($activityCount) ?> Kegiatan</span>
            </span>
        </div>
    </a>

    <div class="grid grid-cols-2 gap-3">
        <a href="<?= esc($unit['masuk_url']) ?>" class="inline-flex h-10 items-center justify-center gap-2 rounded-full bg-white px-4 text-sm font-semibold text-zinc-950 shadow-sm">
            <span class="material-symbols-rounded text-base" aria-hidden="true">add</span>
            <span>Uang Masuk</span>
        </a>
        <a href="<?= esc($unit['keluar_url']) ?>" class="inline-flex h-10 items-center justify-center gap-2 rounded-full bg-zinc-950 px-4 text-sm font-semibold text-white">
            <span class="material-symbols-rounded text-base" aria-hidden="true">remove</span>
            <span>Uang Keluar</span>
        </a>
    </div>
</article>
