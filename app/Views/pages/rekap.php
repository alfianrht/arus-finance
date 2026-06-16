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

    <div class="space-y-3 xl:grid xl:grid-cols-[minmax(0,1.45fr)_minmax(0,0.92fr)] xl:items-start xl:gap-4 xl:space-y-0">
        <div class="space-y-3">
            <section class="rounded-3xl bg-white p-5 shadow-sm">
        <div class="flex items-center gap-2 text-zinc-500">
            <span class="material-symbols-rounded text-base" aria-hidden="true">account_balance_wallet</span>
            <p class="text-sm">Saldo Total</p>
        </div>
        <p class="mt-3 text-4xl font-semibold tracking-tight text-zinc-950 tabular-nums"><?= esc(rupiah($rekapSummary['balance'])) ?></p>
        <div class="mt-4 grid grid-cols-3 gap-3">
            <div>
                <p class="text-xs text-zinc-500">Uang Masuk</p>
                <p class="mt-2 text-sm font-semibold text-emerald-600 tabular-nums"><?= esc(rupiah($rekapSummary['income'])) ?></p>
            </div>
            <div>
                <p class="text-xs text-zinc-500">Uang Keluar</p>
                <p class="mt-2 text-sm font-semibold text-rose-500 tabular-nums"><?= esc(rupiah($rekapSummary['expense'])) ?></p>
            </div>
            <div>
                <p class="text-xs text-zinc-500">Laba Sementara</p>
                <p class="mt-2 text-sm font-semibold text-zinc-950 tabular-nums"><?= esc(rupiah($rekapSummary['surplus'])) ?></p>
            </div>
        </div>
            </section>

            <section class="rounded-3xl bg-white p-4 shadow-sm">
        <div class="flex items-center justify-between">
            <h2 class="text-base font-semibold text-zinc-950">Saldo per Rekening / Dompet</h2>
            <p class="text-xs text-zinc-500">Ketuk kartu untuk rincian</p>
        </div>
        <p class="mt-2 text-sm text-zinc-500">Daftar di sini dibuat ringkas. Detail penuh rekening atau dompet dibuka dari masing-masing kartu.</p>
        <?php if ($rekapAccounts === []): ?>
            <div class="mt-4">
                <?= view('partials/empty_state', [
                    'icon' => 'account_balance_wallet',
                    'title' => 'Belum ada rekening atau dompet.',
                    'description' => 'Tambahkan rekening atau dompet dari Pengaturan agar saldo per penyimpanan dana bisa direkap.',
                    'compact' => true,
                ]) ?>
            </div>
        <?php else: ?>
            <div class="mt-4 grid gap-3 md:grid-cols-2">
                <?php foreach ($rekapAccounts as $account): ?>
                    <?= view('partials/account_card', ['account' => $account, 'cardWidthClass' => 'w-full']) ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
            </section>

            <section class="space-y-3 rounded-3xl bg-white p-4 shadow-sm">
        <div class="flex items-center justify-between">
            <h2 class="text-base font-semibold text-zinc-950">Ringkasan per Unit / Program</h2>
            <p class="text-xs text-zinc-500">Sesuai filter</p>
        </div>
        <?php if ($rekapUnits === []): ?>
            <?= view('partials/empty_state', [
                'icon' => 'domain',
                'title' => 'Belum ada ringkasan unit atau program.',
                'description' => 'Ringkasan unit akan muncul setelah ada unit aktif dan transaksi yang masuk pada filter saat ini.',
                'compact' => true,
            ]) ?>
        <?php else: ?>
            <div class="space-y-3 md:grid md:grid-cols-2 md:gap-3 md:space-y-0">
                <?php foreach ($rekapUnits as $unit): ?>
                    <?= view('partials/unit_card', ['unit' => $unit]) ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
            </section>

            <section class="rounded-3xl bg-white p-4 shadow-sm">
        <div class="flex items-center justify-between">
            <h2 class="text-base font-semibold text-zinc-950">Ringkasan per Kegiatan</h2>
            <p class="text-xs text-zinc-500">Masuk, biaya, dan surplus</p>
        </div>
        <?php if ($rekapActivities === []): ?>
            <div class="mt-3">
                <?= view('partials/empty_state', [
                    'icon' => 'folder_supervised',
                    'title' => 'Belum ada ringkasan kegiatan.',
                    'description' => 'Kegiatan akan muncul di rekap setelah ada aktivitas dan transaksi yang sesuai filter.',
                    'compact' => true,
                ]) ?>
            </div>
        <?php else: ?>
            <div class="mt-3 space-y-3 md:grid md:grid-cols-2 md:gap-3 md:space-y-0">
                <?php foreach ($rekapActivities as $activity): ?>
                    <?= view('partials/activity_card', ['activity' => $activity]) ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
            </section>

            <section class="rounded-3xl bg-white pt-4 pb-1 shadow-sm">
        <div class="flex items-center justify-between px-4">
            <h2 class="text-base font-semibold text-zinc-950">Penerima Terlibat</h2>
        </div>
        <div class="mt-4">
            <?php if (empty($rekapReceivers)): ?>
                <div class="px-4 pb-4">
                    <?= view('partials/empty_state', [
                        'icon' => 'groups',
                        'title' => 'Belum ada penerima terlibat.',
                        'description' => 'Penerima akan muncul dari transaksi honor atau pengeluaran yang terhubung ke penerima.',
                        'compact' => true,
                    ]) ?>
                </div>
            <?php else: ?>
                <div class="flex flex-nowrap gap-3 overflow-x-auto px-4 pb-4 pt-2 snap-x snap-mandatory scroll-pl-4" style="scrollbar-width: none;">
                    <!-- Sembunyikan scrollbar untuk webkit (Chrome/Safari) -->
                    <style>
                        .overflow-x-auto::-webkit-scrollbar { display: none; }
                    </style>
                    
                    <?php foreach ($rekapReceivers as $receiver): ?>
                        <?= view('partials/receiver_card', ['receiver' => $receiver, 'widthClass' => 'w-48 sm:w-52']) ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
            </section>
        </div>

        <div class="space-y-3">
            <section class="rounded-3xl bg-white py-4 shadow-sm">
        <div class="px-4 flex items-center justify-between">
            <h2 class="text-base font-semibold text-zinc-950">Pindah Dana</h2>
            <span class="rounded-full bg-sky-50 px-3 py-2 text-xs font-medium text-sky-700">Tidak dihitung biaya</span>
        </div>
        <div class="mt-3 divide-y divide-zinc-100">
            <?php if ($rekapTransferItems === []): ?>
                <div class="px-4 pb-1">
                    <?= view('partials/empty_state', [
                        'icon' => 'swap_horiz',
                        'title' => 'Belum ada pindah dana.',
                        'description' => 'Pindah dana akan tampil di sini saat ada perpindahan antar rekening atau dompet pada filter ini.',
                        'compact' => true,
                    ]) ?>
                </div>
            <?php endif; ?>
            <?php foreach ($rekapTransferItems as $transaction): ?>
                <?= view('partials/transaction_item', ['transaction' => $transaction]) ?>
            <?php endforeach; ?>
        </div>
        <?= view('partials/pagination_controls', [
            'pagination' => $rekapTransferPagination,
            'prevUrl' => route_query('rekap', ['periode' => $selectedPeriodSlug, 'unit' => $selectedUnitSlug, 'kegiatan' => $selectedActivitySlug, 'mutasi_page' => $rekapTransferPagination['prevPage'], 'transaksi_page' => $rekapTransactionPagination['page']]),
            'nextUrl' => route_query('rekap', ['periode' => $selectedPeriodSlug, 'unit' => $selectedUnitSlug, 'kegiatan' => $selectedActivitySlug, 'mutasi_page' => $rekapTransferPagination['nextPage'], 'transaksi_page' => $rekapTransactionPagination['page']]),
        ]) ?>
            </section>

            <section class="rounded-3xl bg-white py-4 shadow-sm">
        <div class="px-4 flex items-center justify-between">
            <h2 class="text-base font-semibold text-zinc-950">Transaksi Terbaru</h2>
            <p class="text-xs text-zinc-500">Hasil filter saat ini</p>
        </div>
        <div class="mt-3 divide-y divide-zinc-100">
            <?php if ($rekapTransactions === []): ?>
                <div class="px-4 pb-1">
                    <?= view('partials/empty_state', [
                        'icon' => 'receipt_long',
                        'title' => 'Belum ada transaksi pada rekap ini.',
                        'description' => 'Coba ubah filter atau mulai catat transaksi agar daftar terbaru tampil di sini.',
                        'compact' => true,
                    ]) ?>
                </div>
            <?php endif; ?>
            <?php foreach ($rekapTransactions as $transaction): ?>
                <?= view('partials/transaction_item', ['transaction' => $transaction]) ?>
            <?php endforeach; ?>
        </div>
        <?= view('partials/pagination_controls', [
            'pagination' => $rekapTransactionPagination,
            'prevUrl' => route_query('rekap', ['periode' => $selectedPeriodSlug, 'unit' => $selectedUnitSlug, 'kegiatan' => $selectedActivitySlug, 'transaksi_page' => $rekapTransactionPagination['prevPage'], 'mutasi_page' => $rekapTransferPagination['page']]),
            'nextUrl' => route_query('rekap', ['periode' => $selectedPeriodSlug, 'unit' => $selectedUnitSlug, 'kegiatan' => $selectedActivitySlug, 'transaksi_page' => $rekapTransactionPagination['nextPage'], 'mutasi_page' => $rekapTransferPagination['page']]),
        ]) ?>
            </section>
        </div>
    </div>
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
