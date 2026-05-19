<?= $this->extend('layouts/public_report') ?>

<?= $this->section('content') ?>
<?php $surfaceText = surface_label($unit['short_name'] ?? $unit['name']); ?>
<?php
$scopeHeaderIsActivity = ($scopeMode ?? 'unit') === 'kegiatan';
$scopeHeaderCardClass = $scopeHeaderIsActivity ? 'bg-zinc-950 text-white' : 'bg-lime-400 text-zinc-950';
$scopeHeaderOverlayClass = $scopeHeaderIsActivity ? 'bg-white/5' : 'bg-white/10';
$scopeHeaderMutedTextClass = $scopeHeaderIsActivity ? 'text-zinc-400' : 'text-zinc-700';
$scopeHeaderMetaTextClass = $scopeHeaderIsActivity ? 'text-zinc-300' : 'text-zinc-700';
$scopeHeaderBadgeClass = $scopeHeaderIsActivity
    ? 'border border-white/15 bg-white/10 text-white'
    : 'bg-zinc-950 text-white';
$scopeHeaderActionClass = $scopeHeaderIsActivity
    ? 'border border-white/15 bg-white/10 text-white'
    : 'border border-zinc-950/10 bg-white/40 text-zinc-950';
$scopeHeaderDividerClass = $scopeHeaderIsActivity ? 'border-white/10' : 'border-white/50';
$scopeHeaderHighlightClass = $scopeHeaderIsActivity
    ? 'bg-white/10 text-white border border-white/10'
    : 'bg-white/40 text-zinc-950 border border-zinc-950/10';
$scopeStatLabelClass = $scopeHeaderIsActivity ? 'text-zinc-400' : 'text-zinc-700';
$scopeStatCardClass = $scopeHeaderIsActivity
    ? 'rounded-2xl border border-white/10 bg-white/5 p-2.5'
    : 'rounded-2xl border border-zinc-950/10 bg-white/35 p-2.5';
$scopeIncomeValueClass = $scopeHeaderIsActivity ? 'text-emerald-400' : 'text-emerald-700';
$scopeExpenseValueClass = $scopeHeaderIsActivity ? 'text-rose-400' : 'text-rose-700';
$scopeSurplusValueClass = $scopeHeaderIsActivity ? 'text-white' : 'text-zinc-950';
?>
<div class="">
    <section class="rounded-3xl bg-white px-3 py-2.5 shadow-sm">
        <div class="flex flex-col gap-2.5 sm:flex-row sm:items-center sm:justify-between">
            <div class="min-w-0">
                <h1 class="truncate text-base font-semibold tracking-tight text-zinc-950 sm:text-lg"><?= esc($unit['name']) ?></h1>
                <div class="flex flex-wrap items-center gap-1.5">
                    <span class="inline-flex rounded-full border border-zinc-200 bg-zinc-50 px-2.5 py-1 text-[9px] font-medium text-zinc-700">
                        <?= esc($reportSummary['period']) ?>
                    </span>
                    <span class="inline-flex rounded-full border border-zinc-200 bg-white px-2.5 py-1 text-[9px] font-medium text-zinc-700">
                        View only
                    </span>
                </div>
            </div>
            <div class="hidden items-center justify-between gap-2 rounded-2xl bg-zinc-950 px-3 py-2 text-white sm:flex sm:min-w-56">
                <div class="min-w-0">
                    <p class="text-[9px] text-white/55">Status akses</p>
                    <div class="mt-1 flex items-center gap-1.5">
                        <span class="material-symbols-rounded text-[12px] text-lime-300" aria-hidden="true"><?= $unlocked ? 'verified' : 'lock' ?></span>
                        <span class="inline-flex rounded-full border border-white/15 bg-white/10 px-2 py-1 text-[9px] font-semibold text-white">
                            <?= $unlocked ? 'Terbuka terbatas' : 'Terkunci PIN' ?>
                        </span>
                    </div>
                </div>
                <span class="inline-flex h-8 min-w-8 items-center justify-center rounded-full border border-white/10 bg-white/5 px-2 text-[10px] font-black uppercase tracking-tight text-white/45"><?= esc($surfaceText) ?></span>
            </div>
        </div>
    </section>

    <?php if (! $unlocked): ?>
        <section class="grid gap-3 lg:grid-cols-[1fr_0.9fr]">
            <div class="rounded-3xl bg-white p-4 shadow-sm">
                <div class="flex items-start gap-3">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-3xl bg-zinc-100 text-zinc-950">
                        <span class="material-symbols-rounded text-[24px]" aria-hidden="true">lock</span>
                    </div>
                    <div class="min-w-0">
                        <h2 class="text-xl font-semibold tracking-tight text-zinc-950">Masukkan PIN akses</h2>
                        <p class="mt-1 text-sm text-zinc-500">Laporan unit ini hanya bisa dilihat oleh pihak yang menerima PIN.</p>
                    </div>
                </div>
                <form method="get" action="<?= esc($shareUrl) ?>" class="mt-5 grid gap-3 sm:grid-cols-[minmax(0,1fr)_auto]">
                    <input type="hidden" name="preview" value="1">
                    <label class="block">
                        <span class="text-xs font-medium uppercase tracking-wide text-zinc-500">PIN</span>
                        <input type="password" inputmode="numeric" placeholder="6 digit PIN" class="mt-2 h-12 w-full rounded-2xl border border-zinc-200 bg-zinc-50 px-4 text-center text-lg font-semibold tracking-[0.3em] text-zinc-950 outline-none">
                    </label>
                    <button type="submit" class="inline-flex h-12 items-center justify-center gap-2 self-end rounded-full bg-zinc-950 px-6 text-sm font-semibold text-white">
                        <span class="material-symbols-rounded text-base" aria-hidden="true">lock_open</span>
                        <span>Buka</span>
                    </button>
                </form>
            </div>
            <div class="rounded-3xl bg-white p-4 shadow-sm">
                <h2 class="text-base font-semibold text-zinc-950">Isi Laporan</h2>
                <div class="mt-3 grid grid-cols-2 gap-2">
                    <?php foreach ($reportHighlights as $item): ?>
                        <div class="rounded-2xl bg-zinc-50 px-4 py-3">
                            <p class="text-xs text-zinc-500"><?= esc($item['label']) ?></p>
                            <p class="mt-1 text-sm font-semibold text-zinc-950"><?= esc($item['value']) ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php else: ?>
        <section class="grid gap-2 overflow-x-hidden lg:grid-cols-[minmax(0,0.95fr)_minmax(0,1.05fr)_minmax(0,1.05fr)] lg:gap-3">
            <div class="min-w-0 space-y-2.5 lg:col-span-1">
                <section class="hidden space-y-2 rounded-3xl mt-2 bg-white p-3 shadow-sm lg:block">
                    <div class="flex items-center justify-between">
                        <h2 class="text-sm font-semibold text-zinc-950">Ringkasan Unit / Program</h2>
                        <p class="text-[11px] text-zinc-500">Unit aktif</p>
                    </div>
                    <?php $unitSurfaceText = surface_label($reportUnitCard['short_name'] ?? $reportUnitCard['name']); ?>
                    <a href="<?= esc($reportUnitCard['detail_url']) ?>" class="group relative block overflow-hidden rounded-3xl bg-lime-400 p-3 text-zinc-950 shadow-sm">
                        <div class="absolute inset-0 bg-white/10" aria-hidden="true"></div>
                        <div class="relative flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="text-[11px] font-medium uppercase tracking-wide text-zinc-900/60">Unit / Program</p>
                                <h3 class="mt-1 text-sm font-semibold leading-tight text-zinc-950"><?= esc($reportUnitCard['name']) ?></h3>
                            </div>
                            <p class="text-sm font-black uppercase tracking-tight text-zinc-950"><?= esc($unitSurfaceText) ?></p>
                        </div>

                        <div class="relative mt-3">
                            <p class="text-[11px] font-medium uppercase tracking-wide text-zinc-900/60">Saldo</p>
                            <p class="mt-1 text-xl font-black tracking-tight text-zinc-950"><?= esc(rupiah($reportUnitCard['related_balance'] ?? $reportUnitCard['surplus'])) ?></p>
                        </div>

                        <div class="relative mt-3 grid grid-cols-3 gap-2 border-t border-white/50 pt-2.5">
                            <div>
                                <p class="text-[10px] font-medium uppercase tracking-wide text-zinc-900/60">Masuk</p>
                                <p class="mt-1 text-[11px] font-semibold text-zinc-950"><?= esc(rupiah($reportUnitCard['income'])) ?></p>
                            </div>
                            <div>
                                <p class="text-[10px] font-medium uppercase tracking-wide text-zinc-900/60">Keluar</p>
                                <p class="mt-1 text-[11px] font-semibold text-zinc-950"><?= esc(rupiah($reportUnitCard['expense'])) ?></p>
                            </div>
                            <div>
                                <p class="text-[10px] font-medium uppercase tracking-wide text-zinc-900/60">Laba</p>
                                <p class="mt-1 text-[11px] font-semibold text-zinc-950"><?= esc(rupiah($reportUnitCard['surplus'])) ?></p>
                            </div>
                        </div>

                        <div class="relative mt-2 flex items-center justify-between gap-3">
                            <span class="text-[10px] font-semibold tracking-wide text-zinc-950">•••• <?= esc(surface_tail($reportUnitCard['slug'])) ?></span>
                            <span class="inline-flex items-center gap-1 rounded-full bg-zinc-950 px-2.5 py-1 text-[10px] font-semibold text-white">
                                <span class="material-symbols-rounded text-[13px]" aria-hidden="true">stacks</span>
                                <span><?= esc((string) count($reportUnitCard['activities'] ?? [])) ?> Kegiatan</span>
                            </span>
                        </div>
                    </a>
                </section>

                <section class="rounded-3xl bg-white p-3 shadow-sm hidden lg:block">
                    <div class="flex items-center justify-between">
                        <h2 class="text-sm font-semibold text-zinc-950">Kegiatan dalam Unit</h2>
                        <p class="text-[11px] text-zinc-500">Pilih scope</p>
                    </div>
                    <div class="mt-2.5 space-y-2.5">
                        <?php foreach ($reportActivities as $activity): ?>
                            <a href="<?= esc($activity['detail_url']) ?>" class="<?= !empty($activity['is_selected']) ? 'ring-[3px] ring-lime-400' : '' ?> group relative block overflow-hidden rounded-3xl bg-zinc-950 p-3 text-white shadow-sm">
                                <div class="absolute inset-0 <?= !empty($activity['is_selected']) ? 'bg-white/10' : 'bg-white/5' ?>" aria-hidden="true"></div>
                                <?php if (!empty($activity['is_selected'])): ?>
                                    <span class="absolute right-3 top-3 z-10 inline-flex rounded-full bg-lime-400 px-2 py-1 text-[9px] font-semibold text-zinc-950">Sedang ditinjau</span>
                                <?php endif; ?>
                                <div class="relative flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="text-[11px] font-medium uppercase tracking-wide text-zinc-400">Kegiatan</p>
                                        <h3 class="mt-1 text-sm font-semibold leading-tight text-white"><?= esc($activity['name']) ?></h3>
                                    </div>
                                </div>

                                <div class="relative mt-3">
                                    <p class="text-[11px] font-medium uppercase tracking-wide text-zinc-400">Saldo</p>
                                    <p class="mt-1 text-xl font-black tracking-tight text-white"><?= esc(rupiah($activity['related_balance'] ?? $activity['surplus'])) ?></p>
                                </div>

                                <div class="relative mt-3 grid grid-cols-3 gap-2 border-t border-white/10 pt-2.5">
                                    <div>
                                        <p class="text-[10px] font-medium uppercase tracking-wide text-zinc-500">Masuk</p>
                                        <p class="mt-1 text-[11px] font-semibold text-white"><?= esc(rupiah($activity['income'])) ?></p>
                                    </div>
                                    <div>
                                        <p class="text-[10px] font-medium uppercase tracking-wide text-zinc-500">Keluar</p>
                                        <p class="mt-1 text-[11px] font-semibold text-white"><?= esc(rupiah($activity['expense'])) ?></p>
                                    </div>
                                    <div>
                                        <p class="text-[10px] font-medium uppercase tracking-wide text-zinc-500">Laba</p>
                                        <p class="mt-1 text-[11px] font-semibold text-white"><?= esc(rupiah($activity['surplus'])) ?></p>
                                    </div>
                                </div>

                                <div class="relative mt-2 flex items-center justify-between gap-3">
                                    <span class="text-[10px] font-semibold tracking-wide text-zinc-300">•••• <?= esc(surface_tail($activity['slug'])) ?></span>
                                    <span class="material-symbols-rounded text-sm text-zinc-300 opacity-70 transition-all group-hover:translate-x-0.5 group-hover:text-white" aria-hidden="true">arrow_outward</span>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </section>

            </div>

            <div class="min-w-0 space-y-2 lg:col-span-2">
                <section class="rounded-3xl bg-white p-3 shadow-sm lg:hidden">
                    <div class="flex items-center justify-between gap-3">
                        <h2 class="text-sm font-semibold text-zinc-950">Pilih Konteks</h2>
                        <p class="text-[11px] text-zinc-500">Swipe</p>
                    </div>
                    <div class="-mx-3 mt-2 overflow-x-auto px-3 pb-1" style="scrollbar-width: none;">
                        <div class="flex w-max min-w-full gap-2 pt-1">
                            <a
                                href="<?= esc($scopeResetUrl) ?>"
                                class="<?= $scopeMode === 'unit' ? 'border-lime-400 bg-lime-400 text-zinc-950 ring-2 ring-lime-400' : 'border-zinc-200 bg-white text-zinc-700' ?> relative flex h-32 w-52 shrink-0 flex-col rounded-2xl border px-3 py-3 shadow-sm"
                            >
                                <?php if ($scopeMode === 'unit'): ?>
                                    <span class="absolute right-2 top-2 inline-flex rounded-full bg-lime-400 px-2 py-1 text-[9px] font-semibold text-zinc-950">Aktif</span>
                                <?php endif; ?>
                                <span class="block text-[10px] font-medium uppercase tracking-wide <?= $scopeMode === 'unit' ? 'text-zinc-800' : 'text-zinc-400' ?>">Unit</span>
                                <span class="mt-1 line-clamp-2 block min-h-[0.5rem] text-sm font-semibold leading-tight"><?= esc($reportUnitCard['name']) ?></span>
                                <span class="mt-auto block text-[10px] font-medium uppercase tracking-wide <?= $scopeMode === 'unit' ? 'text-zinc-800/70' : 'text-zinc-400' ?>">Saldo</span>
                                <span class="mt-1 block whitespace-nowrap text-[12px] font-semibold tabular-nums <?= $scopeMode === 'unit' ? 'text-zinc-950' : 'text-zinc-700' ?>">
                                    <?= esc(rupiah($reportSummary['balance'])) ?>
                                </span>
                            </a>
                        <?php foreach ($reportActivities as $activity): ?>
                            <a
                                href="<?= esc($activity['detail_url']) ?>"
                                class="<?= !empty($activity['is_selected']) ? 'border-zinc-950 bg-zinc-950 text-white ring-2 ring-lime-400' : 'border-zinc-200 bg-white text-zinc-700' ?> relative flex h-32 w-52 shrink-0 flex-col rounded-2xl border px-3 py-3 shadow-sm"
                            >
                                <?php if (!empty($activity['is_selected'])): ?>
                                    <span class="absolute right-2 top-2 inline-flex rounded-full bg-lime-400 px-2 py-1 text-[9px] font-semibold text-zinc-950">Aktif</span>
                                <?php endif; ?>
                                <span class="block text-[10px] font-medium uppercase tracking-wide <?= !empty($activity['is_selected']) ? 'text-zinc-300' : 'text-zinc-400' ?>">Kegiatan</span>
                                <span class="mt-1 line-clamp-2 block min-h-[0.5rem] text-sm font-semibold leading-tight"><?= esc($activity['name']) ?></span>
                                <span class="mt-auto block text-[10px] font-medium uppercase tracking-wide <?= !empty($activity['is_selected']) ? 'text-zinc-400' : 'text-zinc-400' ?>">Saldo</span>
                                <span class="mt-1 block whitespace-nowrap text-[12px] font-semibold tabular-nums <?= !empty($activity['is_selected']) ? 'text-white' : 'text-zinc-700' ?>">
                                    <?= esc(rupiah($activity['related_balance'] ?? $activity['surplus'])) ?>
                                </span>
                            </a>
                        <?php endforeach; ?>
                        </div>
                    </div>
                </section>

                <section class="rounded-3xl bg-white py-3 shadow-sm sm:py-4">
                    <div class="px-3 sm:px-4 flex items-center justify-between gap-3">
                        <div class="min-w-0">
                            <h2 class="text-sm font-semibold text-zinc-950 sm:text-base">
                                <?= esc($scopeMode === 'kegiatan' ? 'Transaksi Kegiatan' : 'Transaksi Unit') ?>
                            </h2>
                            <p class="mt-0.5 truncate text-[11px] text-zinc-500"><?= esc($scopeTitle) ?></p>
                        </div>
                        <div class="flex items-center gap-2">
                            <?php if ($scopeMode === 'kegiatan'): ?>
                                <a href="<?= esc($scopeResetUrl) ?>" class="inline-flex items-center gap-1 rounded-full border border-zinc-200 bg-white px-2.5 py-1.5 text-[10px] font-semibold text-zinc-700">
                                    <span class="material-symbols-rounded text-[13px]" aria-hidden="true">keyboard_backspace</span>
                                    <span>Unit</span>
                                </a>
                            <?php endif; ?>
                            <p class="text-[11px] text-zinc-500">View only</p>
                        </div>
                    </div>
                    <div class="<?= $scopeMode === 'unit' ? 'hidden sm:block' : '' ?> mt-2.5 px-3 sm:px-4">
                        <div class="grid grid-cols-3 gap-2 rounded-2xl bg-zinc-50 p-2.5">
                            <div class="rounded-xl bg-white px-2.5 py-2">
                                <p class="text-[10px] font-medium uppercase tracking-wide text-zinc-500">Saldo</p>
                                <p class="mt-1 whitespace-nowrap text-[13px] font-semibold tabular-nums text-zinc-950 sm:text-sm"><?= esc(rupiah($reportSummary['balance'])) ?></p>
                            </div>
                            <div class="rounded-xl bg-white px-2.5 py-2">
                                <p class="text-[10px] font-medium uppercase tracking-wide text-zinc-500">Masuk</p>
                                <p class="mt-1 whitespace-nowrap text-[13px] font-semibold tabular-nums text-emerald-700 sm:text-sm"><?= esc(rupiah($reportSummary['income'])) ?></p>
                            </div>
                            <div class="rounded-xl bg-white px-2.5 py-2">
                                <p class="text-[10px] font-medium uppercase tracking-wide text-zinc-500">Keluar</p>
                                <p class="mt-1 whitespace-nowrap text-[13px] font-semibold tabular-nums text-rose-700 sm:text-sm"><?= esc(rupiah($reportSummary['expense'])) ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="mt-2.5 px-3 sm:px-4 flex flex-wrap gap-2">
                        <?php foreach ($transactionFilters as $filter): ?>
                            <?php $isActive = $selectedTransactionFilter === $filter['key']; ?>
                            <a
                                href="<?= esc(route_query('laporan/unit/' . $unit['slug'], ['preview' => 1, 'kegiatan' => $selectedActivitySlug !== '' ? $selectedActivitySlug : null, 'jenis' => $filter['key'] === 'semua' ? null : $filter['key'], 'transaksi_page' => null])) ?>"
                                class="<?= $isActive ? 'bg-zinc-950 text-white' : 'border border-zinc-200 bg-white text-zinc-600' ?> inline-flex items-center rounded-full px-3 py-2 text-xs font-semibold transition-colors"
                            >
                                <?= esc($filter['label']) ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                    <div class="mt-2.5 divide-y divide-zinc-100">
                        <?php foreach ($reportTransactions as $transaction): ?>
                            <div class="flex items-start gap-3 px-4 py-2">
                                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl <?= esc($transaction['badge_class']) ?>">
                                    <span class="material-symbols-rounded text-xl" aria-hidden="true"><?= esc($transaction['icon']) ?></span>
                                </div>

                                <div class="flex min-w-0 flex-1 items-start justify-between gap-3 pt-0.5">
                                    <div class="min-w-0 flex-1">
                                        <p class="truncate text-sm font-bold text-zinc-950"><?= esc($transaction['headline']) ?></p>

                                        <div class="mt-0.5 flex min-w-0 items-center gap-1.5">
                                            <span class="shrink-0 rounded bg-zinc-100 px-1 py-[2px] text-[8px] font-bold uppercase tracking-wider text-zinc-500">
                                                <?= esc($transaction['badge_label']) ?>
                                            </span>
                                            <p class="truncate text-[11px] font-medium text-zinc-500"><?= esc($transaction['subline']) ?></p>
                                        </div>

                                        <div class="mt-1 flex items-center gap-3">
                                            <span class="truncate text-[10px] font-medium text-zinc-400"><?= esc($transaction['meta']) ?></span>
                                        </div>
                                    </div>

                                    <div class="shrink-0 flex w-fit items-start justify-end gap-3">
                                        <div class="min-w-0 flex-1 text-right">
                                            <p class="text-sm font-bold tabular-nums <?= esc($transaction['amount_class']) ?>">
                                                <?= esc($transaction['amount_prefix']) ?><?= esc(rupiah($transaction['amount'])) ?>
                                            </p>
                                            <?php if (!empty($transaction['admin_fee']) && $transaction['admin_fee'] > 0): ?>
                                                <p class="mt-0.5 text-[10px] font-semibold tabular-nums text-rose-500">
                                                    -<?= esc(rupiah($transaction['admin_fee'])) ?>
                                                </p>
                                            <?php endif; ?>
                                        </div>
                                        <?php if (!empty($transaction['bill_preview_url'])): ?>
                                            <button
                                                type="button"
                                                class="inline-flex h-8 w-8 items-center justify-center rounded-full border border-zinc-200 bg-white text-zinc-700"
                                                onclick="openPublicBillPreview('<?= esc($transaction['bill_preview_url'], 'js') ?>', '<?= esc($transaction['headline'], 'js') ?>')"
                                                aria-label="Lihat bukti transaksi"
                                            >
                                                <span class="material-symbols-rounded text-base" aria-hidden="true">receipt_long</span>
                                            </button>
                                        <?php else: ?>
                                            <span class="inline-flex h-8 w-8 shrink-0"></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?= view('partials/pagination_controls', [
                        'pagination' => $reportTransactionPagination,
                        'prevUrl' => route_query('laporan/unit/' . $unit['slug'], ['preview' => 1, 'kegiatan' => $selectedActivitySlug !== '' ? $selectedActivitySlug : null, 'jenis' => $selectedTransactionFilter === 'semua' ? null : $selectedTransactionFilter, 'transaksi_page' => $reportTransactionPagination['prevPage']]),
                        'nextUrl' => route_query('laporan/unit/' . $unit['slug'], ['preview' => 1, 'kegiatan' => $selectedActivitySlug !== '' ? $selectedActivitySlug : null, 'jenis' => $selectedTransactionFilter === 'semua' ? null : $selectedTransactionFilter, 'transaksi_page' => $reportTransactionPagination['nextPage']]),
                    ]) ?>
                </section>

            </div>
        </section>

        <div id="publicBillPreviewModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-zinc-950/80 p-4" onclick="closePublicBillPreview()">
            <div class="relative w-full max-w-3xl" onclick="event.stopPropagation()">
                <button
                    type="button"
                    class="absolute right-3 top-3 z-10 inline-flex h-10 w-10 items-center justify-center rounded-full bg-white/90 text-zinc-950 shadow-sm"
                    onclick="closePublicBillPreview()"
                    aria-label="Tutup preview bukti"
                >
                    <span class="material-symbols-rounded text-base" aria-hidden="true">close</span>
                </button>
                <img id="publicBillPreviewImage" src="" alt="" class="w-full rounded-3xl bg-white object-contain shadow-2xl">
            </div>
        </div>

        <script>
            function openPublicBillPreview(url, label) {
                const modal = document.getElementById('publicBillPreviewModal');
                const image = document.getElementById('publicBillPreviewImage');
                image.src = url;
                image.alt = label ? 'Bukti transaksi: ' + label : 'Bukti transaksi';
                modal.classList.remove('hidden');
                modal.classList.add('flex');
                document.body.classList.add('overflow-hidden');
            }

            function closePublicBillPreview() {
                const modal = document.getElementById('publicBillPreviewModal');
                const image = document.getElementById('publicBillPreviewImage');
                image.src = '';
                image.alt = '';
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                document.body.classList.remove('overflow-hidden');
            }

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape') {
                    closePublicBillPreview();
                }
            });
        </script>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>
