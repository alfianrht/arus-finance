<?php
$icon = $icon ?? 'inbox';
$title = $title ?? 'Belum ada data';
$description = $description ?? 'Data akan muncul setelah Anda mulai menambahkan aktivitas.';
$compact = (bool) ($compact ?? false);
?>

<div class="<?= $compact ? 'rounded-2xl bg-zinc-50 p-5' : 'rounded-3xl bg-white p-6 shadow-sm' ?>">
    <div class="px-4">
        <div class="rounded-2xl bg-zinc-50 p-6 text-center">
            <div class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-zinc-100 text-zinc-950 mb-3">
                <span class="material-symbols-rounded text-[22px]" aria-hidden="true"><?= esc($icon) ?></span>
            </div>
            <p class="text-sm font-medium text-zinc-950"><?= esc($title) ?></p>
            <p class="text-xs text-zinc-500"><?= esc($description) ?></p>
        </div>
    </div>
</div>
