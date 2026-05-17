<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<div class="space-y-3">
    <?= view('partials/top_nav_back', [
        'title' => 'Rekap',
        'subtitle' => 'Satu halaman ringkas validasi alur laporan.',
        'showBackButton' => false,
    ]) ?>

    <section class="rounded-2xl bg-white p-3 sm:p-4 sm:rounded-3xl shadow-sm">
        <div class="flex items-center justify-between cursor-pointer group" onclick="document.getElementById('filterRekapForm').classList.toggle('hidden'); document.getElementById('filterRekapIcon').innerText = document.getElementById('filterRekapForm').classList.contains('hidden') ? 'expand_more' : 'expand_less'">
            <div class="flex items-center gap-1.5 sm:gap-2">
                <span class="material-symbols-rounded text-sm sm:text-base text-zinc-500" aria-hidden="true">tune</span>
                <h2 class="text-sm sm:text-base font-semibold text-zinc-950">Filter Rekap</h2>
            </div>
            <div class="flex items-center gap-3">
                <a href="<?= site_url('rekap') ?>" class="text-[10px] sm:text-xs font-medium text-zinc-500 hover:text-zinc-950" onclick="event.stopPropagation()">Reset</a>
                <span id="filterRekapIcon" class="material-symbols-rounded text-sm sm:text-base text-zinc-400 group-hover:text-zinc-600 transition-colors">expand_more</span>
            </div>
        </div>

        <form id="filterRekapForm" method="get" action="<?= site_url('rekap') ?>" class="mt-3 sm:mt-4 hidden">
            <div class="grid gap-2.5 sm:gap-3 sm:grid-cols-3">
                <div class="space-y-1 sm:space-y-1.5">
                    <label class="text-[9px] sm:text-[11px] font-bold uppercase tracking-wider text-zinc-500">Periode</label>
                    <div class="relative">
                        <select name="periode" class="h-10 sm:h-11 w-full appearance-none rounded-lg sm:rounded-xl border border-zinc-200 bg-zinc-50 pl-3 sm:pl-4 pr-8 sm:pr-10 text-xs sm:text-sm font-medium text-zinc-900 outline-none transition focus:border-zinc-950 focus:ring-1 focus:ring-zinc-950">
                            <?php foreach ($periods as $period): ?>
                                <option value="<?= esc($period['slug']) ?>" <?= $selectedPeriodSlug === $period['slug'] ? 'selected' : '' ?>><?= esc($period['label']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <span class="pointer-events-none absolute right-2.5 sm:right-3 top-1/2 -translate-y-1/2 material-symbols-rounded text-[18px] text-zinc-400">expand_more</span>
                    </div>
                </div>

                <div class="space-y-1 sm:space-y-1.5">
                    <label class="text-[9px] sm:text-[11px] font-bold uppercase tracking-wider text-zinc-500">Unit / Program</label>
                    <div class="relative">
                        <select id="unitFilter" name="unit" class="h-10 sm:h-11 w-full appearance-none rounded-lg sm:rounded-xl border border-zinc-200 bg-zinc-50 pl-3 sm:pl-4 pr-8 sm:pr-10 text-xs sm:text-sm font-medium text-zinc-900 outline-none transition focus:border-zinc-950 focus:ring-1 focus:ring-zinc-950">
                            <option value="semua" <?= $selectedUnitSlug === 'semua' ? 'selected' : '' ?>>Semua Unit / Program</option>
                            <?php foreach ($units as $unit): ?>
                                <option value="<?= esc($unit['slug']) ?>" <?= $selectedUnitSlug === $unit['slug'] ? 'selected' : '' ?>><?= esc($unit['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <span class="pointer-events-none absolute right-2.5 sm:right-3 top-1/2 -translate-y-1/2 material-symbols-rounded text-[18px] text-zinc-400">expand_more</span>
                    </div>
                </div>

                <div class="space-y-1 sm:space-y-1.5">
                    <label class="text-[9px] sm:text-[11px] font-bold uppercase tracking-wider text-zinc-500">Kegiatan</label>
                    <div class="relative">
                        <select id="kegiatanFilter" name="kegiatan" class="h-10 sm:h-11 w-full appearance-none rounded-lg sm:rounded-xl border border-zinc-200 bg-zinc-50 pl-3 sm:pl-4 pr-8 sm:pr-10 text-xs sm:text-sm font-medium text-zinc-900 outline-none transition focus:border-zinc-950 focus:ring-1 focus:ring-zinc-950">
                            <option value="semua" <?= $selectedActivitySlug === 'semua' ? 'selected' : '' ?>>Semua Kegiatan</option>
                            <?php foreach ($dropdownActivities as $activity): ?>
                                <option value="<?= esc($activity['slug']) ?>" data-unit="<?= esc($activity['unit_slug']) ?>" <?= $selectedActivitySlug === $activity['slug'] ? 'selected' : '' ?>>
                                    <?= esc($activity['name']) ?> · <?= esc($activity['unit_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <span class="pointer-events-none absolute right-2.5 sm:right-3 top-1/2 -translate-y-1/2 material-symbols-rounded text-[18px] text-zinc-400">expand_more</span>
                    </div>
                </div>
            </div>

            <button type="submit" class="mt-3 sm:mt-4 inline-flex h-10 sm:h-11 w-full items-center justify-center gap-1.5 sm:gap-2 rounded-lg sm:rounded-xl bg-zinc-950 px-4 sm:px-5 text-xs sm:text-sm font-semibold text-white transition hover:bg-zinc-800 sm:w-auto">
                <span class="material-symbols-rounded text-[18px] sm:text-base" aria-hidden="true">tune</span>
                <span>Terapkan Filter</span>
            </button>
        </form>
    </section>

    <div class="grid grid-cols-2 gap-2 sm:gap-3 sm:grid-cols-4">
        <div class="rounded-2xl bg-white p-3 sm:p-4 sm:rounded-3xl shadow-sm">
            <div class="flex items-center gap-1.5 sm:gap-2">
                <div class="flex h-6 w-6 sm:h-8 sm:w-8 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
                    <span class="material-symbols-rounded text-[11px] sm:text-sm">south_west</span>
                </div>
                <p class="truncate text-[9px] sm:text-[11px] font-bold uppercase tracking-wider text-zinc-500">Masuk</p>
            </div>
            <p class="mt-1.5 sm:mt-3 truncate text-sm sm:text-base font-black text-zinc-950"><?= esc(rupiah($rekapSummary['income'])) ?></p>
        </div>
        
        <div class="rounded-2xl bg-white p-3 sm:p-4 sm:rounded-3xl shadow-sm">
            <div class="flex items-center gap-1.5 sm:gap-2">
                <div class="flex h-6 w-6 sm:h-8 sm:w-8 shrink-0 items-center justify-center rounded-full bg-rose-100 text-rose-700">
                    <span class="material-symbols-rounded text-[11px] sm:text-sm">north_east</span>
                </div>
                <p class="truncate text-[9px] sm:text-[11px] font-bold uppercase tracking-wider text-zinc-500">Biaya</p>
            </div>
            <p class="mt-1.5 sm:mt-3 truncate text-sm sm:text-base font-black text-zinc-950"><?= esc(rupiah($rekapSummary['expense'])) ?></p>
        </div>
        
        <div class="rounded-2xl bg-white p-3 sm:p-4 sm:rounded-3xl shadow-sm">
            <div class="flex items-center gap-1.5 sm:gap-2">
                <div class="flex h-6 w-6 sm:h-8 sm:w-8 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-indigo-700">
                    <span class="material-symbols-rounded text-[11px] sm:text-sm">waterfall_chart</span>
                </div>
                <p class="truncate text-[9px] sm:text-[11px] font-bold uppercase tracking-wider text-zinc-500">Surplus</p>
            </div>
            <p class="mt-1.5 sm:mt-3 truncate text-sm sm:text-base font-black text-zinc-950"><?= esc(rupiah($rekapSummary['surplus'])) ?></p>
        </div>
        
        <div class="rounded-2xl bg-white p-3 sm:p-4 sm:rounded-3xl shadow-sm">
            <div class="flex items-center gap-1.5 sm:gap-2">
                <div class="flex h-6 w-6 sm:h-8 sm:w-8 shrink-0 items-center justify-center rounded-full bg-zinc-100 text-zinc-700">
                    <span class="material-symbols-rounded text-[11px] sm:text-sm">account_balance_wallet</span>
                </div>
                <p class="truncate text-[9px] sm:text-[11px] font-bold uppercase tracking-wider text-zinc-500">Saldo Total</p>
            </div>
            <p class="mt-1.5 sm:mt-3 truncate text-sm sm:text-base font-black text-zinc-950"><?= esc(rupiah($rekapSummary['balance'])) ?></p>
        </div>
    </div>

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

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const unitFilter = document.getElementById('unitFilter');
        const kegiatanFilter = document.getElementById('kegiatanFilter');
        const allOptions = Array.from(kegiatanFilter.options);

        function filterKegiatan() {
            const selectedUnit = unitFilter.value;
            let firstVisibleIndex = 0;

            kegiatanFilter.innerHTML = '';
            
            allOptions.forEach(option => {
                const optionUnit = option.getAttribute('data-unit');
                if (selectedUnit === 'semua' || !optionUnit || optionUnit === selectedUnit) {
                    kegiatanFilter.appendChild(option);
                }
            });

            // If the currently selected option is no longer visible, reset to "semua"
            if (!Array.from(kegiatanFilter.options).some(opt => opt.selected)) {
                kegiatanFilter.value = 'semua';
            }
        }

        unitFilter.addEventListener('change', filterKegiatan);
        filterKegiatan(); // Run on load to ensure initial state matches if opened with GET params
    });
</script>

<?= $this->endSection() ?>
