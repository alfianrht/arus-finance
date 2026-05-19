<?= $this->extend('layouts/public_report') ?>

<?= $this->section('content') ?>
<?php $surfaceText = surface_label($unit['short_name'] ?? $unit['name']); ?>
<div class="space-y-2">
    <section class="rounded-3xl bg-white px-3 py-2 shadow-sm">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-1.5">
                    <h1 class="truncate text-base font-semibold tracking-tight text-zinc-950 sm:text-lg"><?= esc($unit['name']) ?></h1>
                    <span class="inline-flex rounded-full border border-zinc-200 bg-zinc-50 px-2.5 py-1 text-[9px] font-medium text-zinc-700">
                        <?= esc($reportSummary['period']) ?>
                    </span>
                    <span class="inline-flex rounded-full border border-zinc-200 bg-white px-2.5 py-1 text-[9px] font-medium text-zinc-700">
                        View only
                    </span>
                </div>
                <p class="mt-0.5 text-[10px] text-zinc-500">Laporan publik unit dengan akses PIN terbatas.</p>
            </div>
            <div class="flex items-center justify-between gap-2 rounded-2xl bg-zinc-950 px-3 py-2 text-white sm:min-w-56">
                <div class="min-w-0">
                    <p class="text-[9px] text-white/55">Status akses</p>
                    <div class="mt-0.5 flex items-center gap-1.5">
                        <span class="material-symbols-rounded text-[12px] text-lime-300" aria-hidden="true"><?= $unlocked ? 'verified' : 'lock' ?></span>
                        <span class="inline-flex rounded-full border border-white/15 bg-white/10 px-2 py-1 text-[9px] font-semibold text-white">
                            <?= $unlocked ? 'Terbuka terbatas' : 'Terkunci PIN' ?>
                        </span>
                    </div>
                </div>
                <span class="rounded-full border border-white/10 bg-white/5 px-2 py-1 text-[10px] font-black uppercase tracking-tight text-white/45"><?= esc($surfaceText) ?></span>
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
        <section class="grid gap-3 lg:grid-cols-[minmax(0,0.95fr)_minmax(0,1.05fr)_minmax(0,1.05fr)]">
            <div class="space-y-2.5 lg:col-span-1">
                <section class="space-y-2 rounded-3xl bg-white p-3 shadow-sm">
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

                <section class="rounded-3xl bg-white p-3 shadow-sm">
                    <div class="flex items-center justify-between">
                        <h2 class="text-sm font-semibold text-zinc-950">Kegiatan dalam Unit</h2>
                        <p class="text-[11px] text-zinc-500">Masuk, biaya, saldo</p>
                    </div>
                    <div class="mt-2.5 space-y-2.5">
                        <?php foreach ($reportActivities as $activity): ?>
                            <?php $activitySurfaceText = surface_label($activity['short_name'] ?? $activity['name']); ?>
                            <a href="<?= esc($activity['detail_url']) ?>" class="group relative block overflow-hidden rounded-3xl bg-zinc-950 p-3 text-white shadow-sm">
                                <div class="absolute inset-0 bg-white/5" aria-hidden="true"></div>
                                <div class="relative flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="text-[11px] font-medium uppercase tracking-wide text-zinc-400">Kegiatan</p>
                                        <h3 class="mt-1 text-sm font-semibold leading-tight text-white"><?= esc($activity['name']) ?></h3>
                                    </div>
                                    <p class="text-sm font-black uppercase tracking-tight text-white"><?= esc($activitySurfaceText) ?></p>
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

                <section class="rounded-3xl bg-white p-3 shadow-sm">
                    <div class="flex items-center justify-between gap-3">
                        <h2 class="text-sm font-semibold text-zinc-950">Sorotan</h2>
                        <span class="rounded-full bg-lime-100 px-3 py-1.5 text-[11px] font-medium text-zinc-950">Aktif</span>
                    </div>
                    <div class="mt-2.5 grid grid-cols-2 gap-2">
                        <?php foreach ($reportHighlights as $item): ?>
                            <div class="rounded-2xl bg-zinc-50 px-3 py-2.5">
                                <p class="text-xs text-zinc-500"><?= esc($item['label']) ?></p>
                                <p class="mt-1 text-sm font-semibold text-zinc-950"><?= esc($item['value']) ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>

                <section class="rounded-3xl bg-white p-3 shadow-sm">
                    <div class="flex items-center gap-2 text-zinc-500">
                        <span class="material-symbols-rounded text-base" aria-hidden="true">shield_lock</span>
                        <p class="text-sm">Akses Terbatas</p>
                    </div>
                    <p class="mt-2 text-lg font-semibold tracking-tight text-zinc-950">View only</p>
                    <div class="mt-2.5 space-y-2">
                        <div class="flex items-center justify-between gap-3 rounded-2xl bg-zinc-50 px-3 py-2.5">
                            <span class="text-xs text-zinc-500">Akses</span>
                            <span class="text-sm font-semibold text-zinc-950">Publik dengan PIN</span>
                        </div>
                        <div class="flex items-center justify-between gap-3 rounded-2xl bg-zinc-50 px-3 py-2.5">
                            <span class="text-xs text-zinc-500">Mode</span>
                            <span class="text-sm font-semibold text-zinc-950">Tidak bisa ubah data</span>
                        </div>
                        <div class="flex items-center justify-between gap-3 rounded-2xl bg-zinc-50 px-3 py-2.5">
                            <span class="text-xs text-zinc-500">PIN</span>
                            <span class="text-sm font-semibold text-zinc-950">Terverifikasi</span>
                        </div>
                    </div>
                </section>
            </div>

            <div class="space-y-2.5 lg:col-span-2">
                <section class="rounded-3xl bg-white p-3 shadow-sm sm:p-4">
                    <div class="flex items-center justify-between gap-3 text-zinc-500">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-rounded text-base" aria-hidden="true">account_balance_wallet</span>
                            <p class="text-sm">Saldo Total</p>
                        </div>
                        <p class="text-[11px]">Ringkasan unit</p>
                    </div>
                    <p class="mt-2.5 text-3xl font-semibold tracking-tight text-zinc-950 tabular-nums sm:text-4xl"><?= esc(rupiah($reportSummary['balance'])) ?></p>
                    <div class="mt-3 grid grid-cols-3 gap-3">
                        <div>
                            <p class="text-xs text-zinc-500">Uang Masuk</p>
                            <p class="mt-1.5 text-sm font-semibold text-emerald-600 tabular-nums"><?= esc(rupiah($reportSummary['income'])) ?></p>
                        </div>
                        <div>
                            <p class="text-xs text-zinc-500">Uang Keluar</p>
                            <p class="mt-1.5 text-sm font-semibold text-rose-500 tabular-nums"><?= esc(rupiah($reportSummary['expense'])) ?></p>
                        </div>
                        <div>
                            <p class="text-xs text-zinc-500">Laba Sementara</p>
                            <p class="mt-1.5 text-sm font-semibold text-zinc-950 tabular-nums"><?= esc(rupiah($reportSummary['surplus'])) ?></p>
                        </div>
                    </div>
                </section>

                <section class="rounded-3xl bg-white py-3 shadow-sm sm:py-4">
                    <div class="px-3 sm:px-4 flex items-center justify-between gap-3">
                        <h2 class="text-sm font-semibold text-zinc-950 sm:text-base">Transaksi Terbaru</h2>
                        <p class="text-[11px] text-zinc-500">View only</p>
                    </div>
                    <div class="mt-2.5 px-3 sm:px-4 flex flex-wrap gap-2">
                        <?php foreach ($transactionFilters as $filter): ?>
                            <?php $isActive = $selectedTransactionFilter === $filter['key']; ?>
                            <a
                                href="<?= esc(route_query('laporan/unit/' . $unit['slug'], ['preview' => 1, 'jenis' => $filter['key'] === 'semua' ? null : $filter['key'], 'transaksi_page' => null])) ?>"
                                class="<?= $isActive ? 'bg-zinc-950 text-white' : 'border border-zinc-200 bg-white text-zinc-600' ?> inline-flex items-center rounded-full px-3 py-2 text-xs font-semibold transition-colors"
                            >
                                <?= esc($filter['label']) ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                    <div class="mt-2.5 divide-y divide-zinc-100">
                        <?php foreach ($reportTransactions as $transaction): ?>
                            <?= view('partials/transaction_item', ['transaction' => $transaction]) ?>
                        <?php endforeach; ?>
                    </div>
                    <?= view('partials/pagination_controls', [
                        'pagination' => $reportTransactionPagination,
                        'prevUrl' => route_query('laporan/unit/' . $unit['slug'], ['preview' => 1, 'jenis' => $selectedTransactionFilter === 'semua' ? null : $selectedTransactionFilter, 'transaksi_page' => $reportTransactionPagination['prevPage']]),
                        'nextUrl' => route_query('laporan/unit/' . $unit['slug'], ['preview' => 1, 'jenis' => $selectedTransactionFilter === 'semua' ? null : $selectedTransactionFilter, 'transaksi_page' => $reportTransactionPagination['nextPage']]),
                    ]) ?>
                </section>
            </div>
        </section>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>
