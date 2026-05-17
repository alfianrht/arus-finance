<?php
$request = service('request');
$query = $request->getGet();
$path = trim($request->getUri()->getPath(), '/');
$currentUrl = site_url($path === '' ? '/' : $path);

if ($query !== []) {
    $currentUrl .= '?' . http_build_query($query);
}

$detailUrl = site_url('transaksi/' . $transaction['id']) . '?from=' . rawurlencode($currentUrl);
?>
<a href="<?= esc($detailUrl) ?>" class="group flex items-center gap-3 py-3 transition hover:opacity-90">
    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full <?= esc($transaction['badge_class']) ?>">
        <span class="material-symbols-rounded text-base" aria-hidden="true"><?= esc($transaction['icon']) ?></span>
    </div>
    <div class="min-w-0 flex-1">
        <p class="truncate text-sm font-semibold text-zinc-950"><?= $transaction['headline'] ?></p>
        <p class="mt-1 text-xs text-zinc-500"><?= esc($transaction['subline']) ?></p>
        <p class="mt-1 text-xs text-zinc-400"><?= esc($transaction['meta']) ?></p>
    </div>
    <div class="text-right">
        <span class="rounded-full px-2 py-1 text-[11px] font-medium <?= esc($transaction['badge_class']) ?>">
            <?= esc($transaction['badge_label']) ?>
        </span>
        <p class="mt-2 text-sm font-semibold tabular-nums <?= esc($transaction['amount_class']) ?>">
            <?= esc($transaction['amount_prefix']) ?><?= esc(rupiah($transaction['amount'])) ?>
        </p>
        <span class="mt-2 inline-flex items-center gap-1 text-[11px] font-medium text-zinc-400">
            <span>Lihat</span>
            <span class="material-symbols-rounded text-sm transition group-hover:translate-x-0.5" aria-hidden="true">chevron_right</span>
        </span>
    </div>
</a>
