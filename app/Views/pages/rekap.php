<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<div class="space-y-3">
    <?= view('partials/top_nav_back', [
        'title' => 'Rekap',
        'subtitle' => 'Satu halaman ringkas validasi alur laporan.',
        'showBackButton' => false,
    ]) ?>

    <section class="rounded-3xl bg-white p-4 shadow-sm">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="material-symbols-rounded text-base text-zinc-500" aria-hidden="true">tune</span>
                <h2 class="text-base font-semibold text-zinc-950">Filter Rekap</h2>
            </div>
            <a href="<?= site_url('rekap') ?>" class="text-xs font-medium text-zinc-500">Reset</a>
        </div>

        <form method="get" action="<?= site_url('rekap') ?>" class="mt-4 space-y-3">
            <div class="space-y-2">
                <label class="text-sm font-medium text-zinc-700">Periode</label>
                <select name="periode" class="h-12 w-full rounded-2xl border border-zinc-100 bg-white px-4 text-sm text-zinc-950 focus:ring-2 focus:ring-lime-400">
                    <?php foreach ($periods as $period): ?>
                        <option value="<?= esc($period['slug']) ?>" <?= $selectedPeriodSlug === $period['slug'] ? 'selected' : '' ?>><?= esc($period['label']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="space-y-2">
                <label class="text-sm font-medium text-zinc-700">Unit / Program</label>
                <select name="unit" class="h-12 w-full rounded-2xl border border-zinc-100 bg-white px-4 text-sm text-zinc-950 focus:ring-2 focus:ring-lime-400">
                    <option value="semua" <?= $selectedUnitSlug === 'semua' ? 'selected' : '' ?>>Semua Unit / Program</option>
                    <?php foreach ($units as $unit): ?>
                        <option value="<?= esc($unit['slug']) ?>" <?= $selectedUnitSlug === $unit['slug'] ? 'selected' : '' ?>><?= esc($unit['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="space-y-2">
                <label class="text-sm font-medium text-zinc-700">Kegiatan</label>
                <select name="kegiatan" class="h-12 w-full rounded-2xl border border-zinc-100 bg-white px-4 text-sm text-zinc-950 focus:ring-2 focus:ring-lime-400">
                    <option value="semua" <?= $selectedActivitySlug === 'semua' ? 'selected' : '' ?>>Semua Kegiatan</option>
                    <?php foreach ($filterActivities as $activity): ?>
                        <option value="<?= esc($activity['slug']) ?>" <?= $selectedActivitySlug === $activity['slug'] ? 'selected' : '' ?>>
                            <?= esc($activity['name']) ?> · <?= esc($activity['unit_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit" class="inline-flex h-12 items-center justify-center gap-2 rounded-full bg-zinc-950 px-5 text-sm font-semibold text-white">
                <span class="material-symbols-rounded text-base" aria-hidden="true">assessment</span>
                <span>Terapkan Filter</span>
            </button>
        </form>
    </section>

    <section class="rounded-3xl bg-white p-5 shadow-sm">
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
            <div>
                <p class="text-xs text-zinc-500">Uang Masuk</p>
                <p class="mt-2 text-sm font-semibold text-emerald-600"><?= esc(rupiah($rekapSummary['income'])) ?></p>
            </div>
            <div>
                <p class="text-xs text-zinc-500">Biaya</p>
                <p class="mt-2 text-sm font-semibold text-rose-500"><?= esc(rupiah($rekapSummary['expense'])) ?></p>
            </div>
            <div>
                <p class="text-xs text-zinc-500">Laba / Surplus</p>
                <p class="mt-2 text-sm font-semibold text-zinc-950"><?= esc(rupiah($rekapSummary['surplus'])) ?></p>
            </div>
            <div>
                <p class="text-xs text-zinc-500">Saldo Total</p>
                <p class="mt-2 text-sm font-semibold text-zinc-950"><?= esc(rupiah($rekapSummary['balance'])) ?></p>
            </div>
        </div>
    </section>

    <section class="rounded-3xl bg-white p-4 shadow-sm">
        <div class="flex items-center justify-between">
            <h2 class="text-base font-semibold text-zinc-950">Saldo per Rekening / Dompet</h2>
            <p class="text-xs text-zinc-500">Ketuk kartu untuk rincian</p>
        </div>
        <p class="mt-2 text-sm text-zinc-500">Daftar di sini dibuat ringkas. Detail penuh rekening atau dompet dibuka dari masing-masing kartu.</p>
        <div class="mt-4 grid gap-3 md:grid-cols-2">
            <?php foreach ($rekapAccounts as $account): ?>
                <?= view('partials/account_card', ['account' => $account, 'cardWidthClass' => 'w-full']) ?>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="space-y-3">
        <div class="flex items-center justify-between">
            <h2 class="text-base font-semibold text-zinc-950">Ringkasan per Unit / Program</h2>
            <p class="text-xs text-zinc-500">Sesuai filter</p>
        </div>
        <div class="space-y-3 md:grid md:grid-cols-2 md:gap-3 md:space-y-0">
            <?php if ($rekapUnits === []): ?>
                <div class="rounded-3xl bg-white p-5 text-sm text-zinc-500 shadow-sm">Belum ada unit dengan transaksi pada filter ini.</div>
            <?php endif; ?>
            <?php foreach ($rekapUnits as $unit): ?>
                <?= view('partials/unit_card', ['unit' => $unit]) ?>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="rounded-3xl bg-white p-4 shadow-sm">
        <div class="flex items-center justify-between">
            <h2 class="text-base font-semibold text-zinc-950">Ringkasan per Kegiatan</h2>
            <p class="text-xs text-zinc-500">Masuk, biaya, dan surplus</p>
        </div>
        <div class="mt-3 space-y-3">
            <?php if ($rekapActivities === []): ?>
                <div class="rounded-2xl bg-zinc-50 px-4 py-4 text-sm text-zinc-500">Belum ada kegiatan dengan transaksi pada filter ini.</div>
            <?php endif; ?>
            <?php foreach ($rekapActivities as $activity): ?>
                <?= view('partials/activity_card', ['activity' => $activity]) ?>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="rounded-3xl bg-white pt-4 pb-1 shadow-sm overflow-hidden">
        <div class="flex items-center justify-between px-4">
            <h2 class="text-base font-semibold text-zinc-950">Penerima Terlibat</h2>
        </div>
        <div class="mt-4">
            <?php if (empty($rekapReceivers)): ?>
                <div class="px-4 pb-4">
                    <div class="rounded-2xl bg-zinc-50 p-6 text-center">
                        <p class="text-sm font-medium text-zinc-950">Belum ada data penerima.</p>
                        <p class="mt-1 text-xs text-zinc-500">Penerima akan muncul dari transaksi Honor & Gaji.</p>
                    </div>
                </div>
            <?php else: ?>
                <div class="flex flex-nowrap gap-3 overflow-x-auto px-4 pb-4 snap-x snap-mandatory" style="scrollbar-width: none;">
                    <!-- Sembunyikan scrollbar untuk webkit (Chrome/Safari) -->
                    <style>
                        .overflow-x-auto::-webkit-scrollbar { display: none; }
                    </style>
                    
                    <?php foreach ($rekapReceivers as $receiver): ?>
                        <div class="flex w-36 shrink-0 snap-start flex-col items-center justify-center rounded-2xl border border-zinc-100 bg-zinc-50 p-4 text-center">
                            <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-indigo-50 text-indigo-600">
                                <span class="material-symbols-rounded text-2xl" aria-hidden="true">account_circle</span>
                            </div>
                            <p class="w-full truncate text-sm font-semibold text-zinc-950"><?= esc($receiver['name']) ?></p>
                            <p class="mt-0.5 text-[10px] font-medium tracking-wider text-zinc-500 uppercase"><?= esc($receiver['type']) ?></p>
                            <div class="mt-3 w-full rounded-lg bg-white py-1.5 shadow-sm border border-zinc-100">
                                <p class="text-xs font-bold text-rose-500"><?= esc(rupiah($receiver['total_received'])) ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <section class="rounded-3xl bg-white p-4 shadow-sm">
        <div class="flex items-center justify-between">
            <h2 class="text-base font-semibold text-zinc-950">Pindah Dana</h2>
            <span class="rounded-full bg-sky-50 px-3 py-2 text-xs font-medium text-sky-700">Tidak dihitung biaya</span>
        </div>
        <div class="mt-3 space-y-3">
            <?php if ($rekapTransferItems === []): ?>
                <div class="rounded-2xl bg-zinc-50 px-4 py-4 text-sm text-zinc-500">Belum ada pindah dana pada filter ini.</div>
            <?php endif; ?>
            <?php foreach ($rekapTransferItems as $transfer): ?>
                <div class="rounded-2xl border border-sky-100 bg-sky-50 p-4">
                    <p class="text-sm font-semibold text-sky-900"><?= $transfer['headline'] ?></p>
                    <p class="mt-1 text-sm text-sky-800"><?= esc(rupiah($transfer['amount'])) ?> · <?= esc($transfer['meta']) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="rounded-3xl bg-white p-4 shadow-sm">
        <div class="flex items-center justify-between">
            <h2 class="text-base font-semibold text-zinc-950">Transaksi Terbaru</h2>
            <p class="text-xs text-zinc-500">Hasil filter saat ini</p>
        </div>
        <div class="mt-3 divide-y divide-zinc-100">
            <?php if ($rekapTransactions === []): ?>
                <div class="py-6 text-sm text-zinc-500">Belum ada transaksi dummy untuk kombinasi filter ini.</div>
            <?php endif; ?>
            <?php foreach ($rekapTransactions as $transaction): ?>
                <?= view('partials/transaction_item', ['transaction' => $transaction]) ?>
            <?php endforeach; ?>
        </div>
    </section>
</div>
<?= $this->endSection() ?>
