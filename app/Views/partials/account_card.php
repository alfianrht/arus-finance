<?php
$cardWidthClass = $cardWidthClass ?? 'w-72 md:w-auto';
$notchClass = $notchClass ?? 'bg-white';
?>

<a href="<?= esc($account['detail_url']) ?>" class="<?= esc($cardWidthClass) ?> relative block shrink-0">
    <div class="relative rounded-3xl border border-zinc-950 bg-white p-4 text-zinc-950">
        <div class="flex items-start justify-between gap-4">
            <div class="min-w-0">
                <p class="text-xs font-medium uppercase tracking-wide text-zinc-950/55"><?= esc($account['kind']) ?></p>
                <p class="mt-2 mb09 text-2xl font-black tracking-tight text-zinc-950"><?= esc(rupiah($account['balance'])) ?></p>
            </div>
            <?php if (isset($account['logo_asset'])): ?>
                <img src="<?= esc(base_url($account['logo_asset'])) ?>" alt="<?= esc($account['mark']) ?>" class="mt-0 h-5 w-auto object-contain">
            <?php else: ?>
                <p class="text-lg font-black uppercase tracking-tight text-zinc-950"><?= esc($account['mark']) ?></p>
            <?php endif; ?>
        </div>

        <div class="mt-1 flex items-center gap-1.5 text-zinc-950">
            <?php if (!empty($account['account_number'])): ?>
                <span class="text-xs font-bold tracking-wide text-zinc-950/70"><?= esc($account['account_number']) ?></span>
                <button
                    type="button"
                    class="flex items-center justify-center text-zinc-400 hover:text-zinc-950 transition"
                    onclick="event.preventDefault(); event.stopPropagation(); copyToClipboard('<?= esc($account['account_number'], 'js') ?>', this)"
                    title="Salin nomor rekening"
                >
                    <span class="material-symbols-rounded text-sm">content_copy</span>
                </button>
            <?php else: ?>
                <span class="text-xs font-black text-zinc-950"><?= esc(surface_tail($account['slug'])) ?></span>
                <span class="h-1.5 w-1.5 rounded-full bg-zinc-950"></span>
                <span class="h-1.5 w-1.5 rounded-full bg-zinc-950"></span>
                <span class="h-1.5 w-1.5 rounded-full bg-zinc-950"></span>
                <span class="h-1.5 w-1.5 rounded-full bg-zinc-950"></span>
            <?php endif; ?>
        </div>

        <div class="mt-4 flex items-end justify-between gap-3">
            <div class="min-w-0">
                <p class="truncate text-sm font-semibold text-zinc-950"><?= esc($account['name']) ?></p>
                <p class="mt-1 truncate text-xs text-zinc-950/65"><?= esc($account['preview_activity']) ?></p>
            </div>
            <p class="shrink-0 text-xs font-medium text-zinc-950/70"><?= esc($account['movement_count']) ?> mutasi</p>
        </div>

        <span class="<?= esc($notchClass) ?> absolute bottom-0 left-1/2 h-2 w-20 -translate-x-1/2 rounded-t-2xl bg-zinc-950"></span>

        <span class="<?= esc($notchClass) ?> absolute top-0 left-1/2 h-1 w-20 -translate-x-1/2 rounded-b-2xl bg-zinc-950"></span>
    </div>
</a>
