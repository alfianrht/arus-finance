<?php
$items = [
    'beranda' => ['label' => 'Beranda', 'href' => route_query('beranda', $activeContext['query'] ?? []), 'icon' => 'home'],
    'catat'   => ['label' => 'Pencatatan', 'href' => route_query('catat', $activeContext['query'] ?? []), 'icon' => 'edit_square'],
    'rekap'   => ['label' => 'Rekap', 'href' => site_url('rekap'), 'icon' => 'assessment'],
];
?>
<div class="fixed inset-x-0 bottom-0 z-50 px-4 pb-4">
    <nav class="mx-auto max-w-4xl rounded-full bg-zinc-950 p-2 shadow-lg" aria-label="Navigasi utama">
        <div class="grid grid-cols-3 gap-2">
            <?php foreach ($items as $key => $item): ?>
                <a
                    href="<?= esc($item['href']) ?>"
                    class="<?= $activeNav === $key ? 'bg-lime-400 text-zinc-950' : 'text-zinc-400' ?> inline-flex h-12 items-center justify-center gap-2 rounded-full px-3 text-sm font-semibold transition"
                >
                    <span class="material-symbols-rounded text-base" aria-hidden="true"><?= esc($item['icon']) ?></span>
                    <?= esc($item['label']) ?>
                </a>
            <?php endforeach; ?>
        </div>
    </nav>
</div>
