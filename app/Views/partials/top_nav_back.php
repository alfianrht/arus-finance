<?php
$title = $title ?? '';
$subtitle = $subtitle ?? '';
$backUrl = $backUrl ?? '#';
$showBackButton = $showBackButton ?? true;
$showSettingsButton = $showSettingsButton ?? true;
$showLogoutButton = $showLogoutButton ?? false;
?>
<header class="flex items-start justify-between gap-4 h-16">
    <div class="flex min-w-0 items-center gap-3">
        <?php if ($showBackButton): ?>
            <a href="<?= esc($backUrl) ?>" class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-white text-zinc-700 shadow-sm" aria-label="Kembali">
                <span class="material-symbols-rounded text-base" aria-hidden="true">arrow_back</span>
            </a>
        <?php endif; ?>
        
        <div class="min-w-0 space-y-0">
            <div class="flex items-center gap-2">
                <p class="truncate text-2xl font-semibold tracking-tight text-zinc-950"><?= esc($title) ?></p>
                <span class="shrink-0 whitespace-nowrap rounded-full border border-lime-200 bg-lime-100 px-2 py-1 text-[10px] font-medium text-lime-950 shadow-sm">Tahun Buku 2026</span>
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
