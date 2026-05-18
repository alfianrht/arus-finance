<?php
$widthClass = $widthClass ?? 'w-48';
$surfaceText = surface_label($receiver['name'] ?? 'Penerima');
$typeLabel = $receiver['type'] ?? 'Penerima';
$totalAmount = (float) ($receiver['total_received'] ?? $receiver['total_amount'] ?? 0);
$metaLabel = $receiver['meta_label'] ?? 'Total terkait';
?>

<a href="<?= esc($receiver['detail_url']) ?>" class="group relative flex <?= esc($widthClass) ?> shrink-0 snap-start overflow-hidden rounded-3xl border border-zinc-100 bg-white p-4 text-left shadow-sm transition hover:-translate-y-0.5 hover:border-lime-400">
    <p class="pointer-events-none absolute -bottom-2 right-2 text-6xl font-black uppercase tracking-tight text-zinc-100" aria-hidden="true"><?= esc($surfaceText) ?></p>

    <div class="relative flex min-w-0 flex-1 flex-col">
        <div class="flex items-start justify-between gap-3">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-zinc-950 text-white">
                <span class="material-symbols-rounded text-2xl" aria-hidden="true">account_circle</span>
            </div>
            <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-lime-400 text-zinc-950 transition group-hover:translate-x-0.5">
                <span class="material-symbols-rounded text-base" aria-hidden="true">arrow_outward</span>
            </span>
        </div>

        <div class="mt-4 min-w-0">
            <p class="mt-1 text-sm font-semibold leading-snug text-zinc-950"><?= esc($receiver['name']) ?></p>
            <span class="mt-1 flex max-w-full items-center gap-1 rounded-full text-[10px] font-regular text-zinc-600">
                <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-lime-400"></span>
                <span class="break-words leading-none"><?= esc($typeLabel) ?></span>
            </span>
        </div>

        <div class="mt-4 border-t border-zinc-100 pt-3">
            <p class="text-[10px] font-bold uppercase tracking-normal text-zinc-500"><?= esc($metaLabel) ?></p>
            <p class="mt-0 text-xl font-black tracking-tight text-zinc-950"><?= esc(rupiah($totalAmount)) ?></p>
        </div>

        <div class="mt-3 flex items-center justify-between gap-3">
            <p class="text-xs text-zinc-500">Lihat rincian</p>
            <span class="text-[10px] font-bold uppercase tracking-wider text-zinc-400">Penerima</span>
        </div>
    </div>
</a>
