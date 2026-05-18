<?php
$request = service('request');
$query = $request->getGet();
$path = trim($request->getUri()->getPath(), '/');
$currentUrl = site_url($path === '' ? '/' : $path);

if ($query !== []) {
    $currentUrl .= '?' . http_build_query($query);
}

$detailUrl = site_url('transaksi/' . $transaction['id']) . '?from=' . rawurlencode($currentUrl);
$allowDelete = $allow_delete ?? false;
?>

<?php if ($allowDelete): ?>
<style>
.swipe-container::-webkit-scrollbar { display: none; }
.swipe-container { -ms-overflow-style: none; scrollbar-width: none; }
</style>
<div class="relative overflow-hidden bg-rose-500 transition-all">
    <div class="swipe-container flex w-full snap-x snap-mandatory overflow-x-auto">
        <div class="w-full shrink-0 snap-center bg-white">
<?php endif; ?>

<a href="<?= esc($detailUrl) ?>" class="group flex items-start gap-3 py-2 px-4 transition-colors hover:bg-zinc-50 active:bg-zinc-100">
    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl <?= esc($transaction['badge_class']) ?>">
        <span class="material-symbols-rounded text-xl" aria-hidden="true"><?= esc($transaction['icon']) ?></span>
    </div>
    
    <div class="flex min-w-0 flex-1 flex-col justify-center pt-0.5">
        <div class="flex items-center justify-between gap-3">
            <p class="truncate text-sm font-bold text-zinc-950"><?= $transaction['headline'] ?></p>
            <p class="shrink-0 text-sm font-bold tabular-nums <?= esc($transaction['amount_class']) ?>">
                <?= esc($transaction['amount_prefix']) ?><?= esc(rupiah($transaction['amount'])) ?>
            </p>
        </div>
        
        <div class="mt-0.5 flex items-center justify-between gap-3">
            <div class="flex min-w-0 flex-1 items-center gap-1.5">
                <span class="shrink-0 rounded bg-zinc-100 px-1 py-[2px] text-[8px] font-bold uppercase tracking-wider text-zinc-500">
                    <?= esc($transaction['badge_label']) ?>
                </span>
                <p class="truncate text-[11px] font-medium text-zinc-500"><?= esc($transaction['subline']) ?></p>
            </div>
            
            <?php if (!empty($transaction['admin_fee']) && $transaction['admin_fee'] > 0): ?>
                <p class="shrink-0 text-[10px] font-bold tabular-nums text-rose-500">
                    -<?= esc(rupiah($transaction['admin_fee'])) ?>
                </p>
            <?php endif; ?>
        </div>
        
        <div class="mt-1 flex items-center justify-between">
            <span class="text-[10px] font-medium text-zinc-400"><?= esc($transaction['meta']) ?></span>
            <span class="material-symbols-rounded ml-auto text-sm text-zinc-300 opacity-0 transition-all group-hover:translate-x-1 group-hover:text-zinc-500 group-hover:opacity-100" aria-hidden="true">chevron_right</span>
        </div>
    </div>
</a>

<?php if ($allowDelete): ?>
        </div>
        <button type="button" onclick="openDeleteModal('<?= site_url('catat/hapus/' . $transaction['id']) ?>', '<?= esc(strip_tags($transaction['headline']), 'js') ?>', '<?= csrf_hash() ?>')" class="flex w-[72px] shrink-0 snap-end flex-col items-center justify-center bg-rose-500 text-white outline-none active:bg-rose-600 transition-colors rounded-r-lg">
            <span class="material-symbols-rounded text-[22px]" aria-hidden="true">delete</span>
            <span class="mt-1 text-[9px] font-bold uppercase tracking-wide">Hapus</span>
        </button>
    </div>
</div>
<?php endif; ?>
