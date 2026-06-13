<?php
$contextQuery = $activeContext['query'] ?? [];
$items = [
    'beranda' => ['label' => 'Beranda', 'href' => route_query('beranda', $contextQuery), 'icon' => 'home'],
    'catat'   => ['label' => 'Pencatatan', 'href' => route_query('catat', $contextQuery), 'icon' => 'edit_square'],
    'rekap'   => ['label' => 'Rekap', 'href' => site_url('rekap'), 'icon' => 'assessment'],
];
$quickActions = [
    ['label' => 'Masuk', 'href' => route_query('catat/masuk', $contextQuery), 'icon' => 'south_west'],
    ['label' => 'Keluar', 'href' => route_query('catat/keluar', $contextQuery), 'icon' => 'north_east'],
];
$showQuickActions = ($activeNav ?? '') !== 'catat';
?>
<div class="fixed inset-x-0 bottom-0 z-50 px-4 pb-4">
    <div class="mx-auto max-w-4xl space-y-2">
        <?php if ($showQuickActions): ?>
            <div class="flex justify-end" aria-label="Aksi cepat pencatatan">
                <div class="flex flex-col items-end gap-2">
                    <?php foreach ($quickActions as $action): ?>
                        <?php
                        $isMasuk = $action['label'] === 'Masuk';
                        $actionClass = $isMasuk
                            ? 'bg-lime-400 text-zinc-950'
                            : 'bg-zinc-950 text-white';
                        ?>
                        <div class="rounded-full border border-zinc-100 bg-white p-2 shadow-[0_8px_30px_rgba(0,0,0,0.08)]">
                            <a
                                href="<?= esc($action['href']) ?>"
                                class="<?= esc($actionClass) ?> inline-flex h-[49px] w-[49px] items-center justify-center rounded-full shadow-sm transition"
                                aria-label="<?= esc($action['label']) ?>"
                                title="<?= esc($action['label']) ?>"
                            >
                                <span class="material-symbols-rounded text-[20px]" aria-hidden="true"><?= esc($action['icon']) ?></span>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
        <nav class="rounded-full bg-zinc-950 p-2 shadow-lg" aria-label="Navigasi utama">
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
</div>
