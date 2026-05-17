<?php
/**
 * Empty State Card — Reusable Partial
 *
 * Cara pakai:
 * <?= view('partials/empty_state', [
 *     'icon'    => 'folder_off',       // Material Symbols icon name
 *     'title'   => 'Belum ada data',
 *     'message' => 'Tambahkan data pertama Anda.',
 *     'actionUrl'   => site_url('...'),   // opsional
 *     'actionLabel' => 'Tambah Data',     // opsional
 * ]) ?>
 */
$icon    = $icon ?? 'folder_off';
$title   = $title ?? 'Belum Ada Data';
$message = $message ?? 'Data yang Anda cari belum tersedia. Mulai dengan menambahkan data baru.';
?>
<div class="flex flex-col items-center justify-center rounded-3xl border border-dashed border-zinc-300 bg-white px-6 py-12 text-center">
    <div class="flex h-16 w-16 items-center justify-center rounded-full bg-zinc-100">
        <span class="material-symbols-rounded text-3xl text-zinc-400"><?= esc($icon) ?></span>
    </div>
    <p class="mt-4 text-base font-semibold text-zinc-950"><?= esc($title) ?></p>
    <p class="mt-1 max-w-xs text-sm text-zinc-500"><?= esc($message) ?></p>
    <?php if (!empty($actionUrl)): ?>
        <a href="<?= esc($actionUrl) ?>" class="mt-5 inline-flex h-11 items-center justify-center gap-2 rounded-full bg-zinc-950 px-5 text-sm font-semibold text-white">
            <span class="material-symbols-rounded text-base">add</span>
            <span><?= esc($actionLabel ?? 'Tambah Data') ?></span>
        </a>
    <?php endif; ?>
</div>
