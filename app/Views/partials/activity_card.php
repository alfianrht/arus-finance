<?php
$isCurrent = $activity['is_current'] ?? false;
$accountNames = $activity['related_accounts'] ?? [];
$accountNote = $accountNames === []
    ? $activity['unit_name']
    : implode(' · ', array_slice($accountNames, 0, 2));

if (count($accountNames) > 2) {
    $accountNote .= ' +' . (count($accountNames) - 2);
}

$mainAmount = ($activity['related_balance'] ?? 0) > 0
    ? $activity['related_balance']
    : $activity['surplus'];

$mainLabel = ($activity['related_balance'] ?? 0) > 0
    ? 'Saldo Terkait'
    : 'Laba Sementara';
$surfaceText = surface_label($activity['short_name'] ?? $activity['name']);
?>

<a href="<?= esc($activity['detail_url']) ?>" class="<?= $isCurrent ? 'ring-2 ring-lime-400' : '' ?> relative block overflow-hidden rounded-3xl bg-zinc-950 p-4 text-white shadow-sm">
    <div class="absolute inset-0 bg-white/5" aria-hidden="true"></div>
    <div class="relative flex items-start justify-between gap-4">
        <div class="min-w-0">
            <p class="text-xs font-medium uppercase tracking-wide text-zinc-400">Kegiatan</p>
            <h3 class="mt-1.5 text-base font-semibold leading-tight text-white"><?= esc($activity['name']) ?></h3>
            <p class="mt-1 text-xs text-zinc-400"><?= esc($activity['unit_name']) ?></p>
        </div>
        <p class="text-lg font-black uppercase tracking-tight text-white"><?= esc($surfaceText) ?></p>
    </div>

    <div class="relative mt-4">
        <p class="text-xs font-medium uppercase tracking-wide text-zinc-400"><?= esc($mainLabel) ?></p>
        <p class="mt-1.5 text-2xl font-black tracking-tight text-white"><?= esc(rupiah($mainAmount)) ?></p>
        <p class="mt-2 text-xs text-zinc-300"><?= esc($accountNote) ?></p>
    </div>

    <p class="pointer-events-none absolute bottom-12 left-3 text-6xl font-black uppercase tracking-tight text-white/10" aria-hidden="true"><?= esc($surfaceText) ?></p>

    <div class="relative mt-4 grid grid-cols-3 gap-2 border-t border-white/10 pt-3">
        <div>
            <p class="text-xs font-medium uppercase tracking-wide text-zinc-500">Masuk</p>
            <p class="mt-1 text-xs font-semibold text-white"><?= esc(rupiah($activity['income'])) ?></p>
        </div>
        <div>
            <p class="text-xs font-medium uppercase tracking-wide text-zinc-500">Biaya</p>
            <p class="mt-1 text-xs font-semibold text-white"><?= esc(rupiah($activity['expense'])) ?></p>
        </div>
        <div>
            <p class="text-xs font-medium uppercase tracking-wide text-zinc-500">Surplus</p>
            <p class="mt-1 text-xs font-semibold text-white"><?= esc(rupiah($activity['surplus'])) ?></p>
        </div>
    </div>

    <div class="relative mt-3 flex items-center justify-between gap-3">
        <p class="text-xs font-semibold tracking-wide text-zinc-300">•••• <?= esc(surface_tail($activity['slug'])) ?></p>
        <span class="<?= $isCurrent ? 'bg-lime-400 text-zinc-950' : 'bg-white text-zinc-950' ?> inline-flex items-center gap-1 rounded-full px-3 py-1.5 text-[11px] font-semibold">
            <span class="material-symbols-rounded text-sm" aria-hidden="true"><?= $isCurrent ? 'radio_button_checked' : 'arrow_outward' ?></span>
            <span><?= $isCurrent ? 'Aktif' : 'Rincian' ?></span>
        </span>
    </div>
</a>
