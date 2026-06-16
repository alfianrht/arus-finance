<?php
$title = $title ?? '';
$subtitle = $subtitle ?? '';
$backUrl = $backUrl ?? '#';
$showBackButton = $showBackButton ?? true;
$showSettingsButton = $showSettingsButton ?? true;
$showLogoutButton = $showLogoutButton ?? false;
$bookPeriodLabel = $bookPeriodLabel ?? 'Tahun Buku Aktif';
$breadcrumbs = is_array($breadcrumbs ?? null) ? $breadcrumbs : [];
?>
<header class="px-2 flex min-h-16 items-start justify-between gap-4">
    <div class="flex min-w-0 items-center gap-3">
        <?php if ($showBackButton): ?>
            <a href="<?= esc($backUrl) ?>" class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-white text-zinc-700 shadow-sm" aria-label="Kembali">
                <span class="material-symbols-rounded text-base" aria-hidden="true">arrow_back</span>
            </a>
        <?php endif; ?>
        
        <div class="min-w-0 space-y-0">
            <?php if ($breadcrumbs !== []): ?>
                <nav aria-label="Breadcrumb" class="mb-1 flex min-w-0 flex-wrap items-center gap-1.5 text-[11px] font-medium text-zinc-400">
                    <?php foreach ($breadcrumbs as $index => $crumb): ?>
                        <?php
                        $isLast = $index === array_key_last($breadcrumbs);
                        $label = (string) ($crumb['label'] ?? '');
                        $url = (string) ($crumb['url'] ?? '');
                        ?>
                        <?php if ($index > 0): ?>
                            <span class="material-symbols-rounded text-[14px] text-zinc-300" aria-hidden="true">chevron_right</span>
                        <?php endif; ?>

                        <?php if (! $isLast && $url !== ''): ?>
                            <a href="<?= esc($url) ?>" class="truncate transition hover:text-zinc-700"><?= esc($label) ?></a>
                        <?php else: ?>
                            <span class="truncate <?= $isLast ? 'text-zinc-600' : '' ?>"><?= esc($label) ?></span>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </nav>
            <?php endif; ?>
            <div class="flex items-center gap-2">
                <p class="truncate text-2xl font-semibold tracking-tight text-zinc-950"><?= esc($title) ?></p>
                <span class="shrink-0 whitespace-nowrap rounded-full border border-lime-200 bg-lime-100 px-2 py-1 text-[10px] font-medium text-lime-950 shadow-sm"><?= esc($bookPeriodLabel) ?></span>
            </div>
            <?php if (!empty($subtitle)): ?>
                <p class="truncate text-sm text-zinc-500"><?= esc($subtitle) ?></p>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="flex items-center gap-2">
        <?php if ($showSettingsButton): ?>
            <a href="<?= site_url('pengaturan') ?>" class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-white text-zinc-700 shadow-sm" aria-label="Buka pengaturan">
                <span class="material-symbols-rounded text-base" style="font-size: 1.2rem;" aria-hidden="true">settings</span>
            </a>
        <?php endif; ?>
        <?php if ($showLogoutButton): ?>
            <a href="<?= site_url('auth/logout') ?>" class="inline-flex h-9 items-center justify-center gap-1.5 rounded-full bg-white px-3 text-rose-600 shadow-sm hover:bg-rose-50" aria-label="Keluar">
                <span class="material-symbols-rounded text-base" style="font-size: 1.2rem;" aria-hidden="true">logout</span>
                <span class="text-sm font-medium">Keluar</span>
            </a>
        <?php endif; ?>
    </div>
</header>
