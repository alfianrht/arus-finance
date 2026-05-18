<?php
$icon = $icon ?? 'inbox';
$title = $title ?? 'Belum ada data';
$description = $description ?? 'Data akan muncul setelah Anda mulai menambahkan aktivitas.';
$compact = (bool) ($compact ?? false);
?>

<div class="<?= $compact ? 'rounded-2xl bg-zinc-50 p-5' : 'rounded-3xl bg-white p-6 shadow-sm' ?>">
    <div class="flex items-start gap-3">
        <div class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-lime-100 text-lime-700">
            <span class="material-symbols-rounded text-[22px]" aria-hidden="true"><?= esc($icon) ?></span>
        </div>
        <div class="min-w-0">
            <p class="text-sm font-semibold text-zinc-950"><?= esc($title) ?></p>
            <p class="mt-1 text-sm leading-6 text-zinc-500"><?= esc($description) ?></p>
        </div>
    </div>
</div>
