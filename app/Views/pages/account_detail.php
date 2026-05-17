<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<div class="space-y-3">
    <?= view('partials/top_nav_back', [
        'title' => $account['name'],
        'subtitle' => 'Rekening / Dompet',
        'backUrl' => $backUrl,
    ]) ?>

    <section class="rounded-2xl bg-lime-50 p-4">
        <p class="text-xs font-medium uppercase tracking-wide text-zinc-500">Filter Rekap yang Sedang Aktif</p>
        <p class="mt-2 text-sm font-semibold text-zinc-950"><?= esc($rekapFilterSummary['period_label']) ?></p>
        <p class="mt-1 text-sm text-zinc-700"><?= esc($rekapFilterSummary['unit_label']) ?> · <?= esc($rekapFilterSummary['activity_label']) ?></p>
        <p class="mt-2 text-sm text-zinc-600">Saldo utama tetap angka dummy rekening. Rincian mutasi di bawah mengikuti filter rekap saat ini.</p>
    </section>

    <section class="relative block">
        <div class="relative rounded-3xl border border-zinc-950 bg-white p-5 text-zinc-950">
            <div class="flex items-start justify-between gap-4">
                <div class="min-w-0">
                    <p class="text-xs font-medium uppercase tracking-wide text-zinc-950/55"><?= esc($account['kind']) ?></p>
                    <p class="mt-3 text-4xl font-black tracking-tight text-zinc-950"><?= esc(rupiah($account['balance'])) ?></p>
                </div>
                <?php if (isset($account['logo_asset'])): ?>
                    <img src="<?= esc(base_url($account['logo_asset'])) ?>" alt="<?= esc($account['mark']) ?>" class="mt-1 h-8 w-auto object-contain">
                <?php else: ?>
                    <p class="text-2xl font-black uppercase tracking-tight text-zinc-950"><?= esc($account['mark']) ?></p>
                <?php endif; ?>
            </div>

            <div class="mt-4 flex items-center gap-2 text-zinc-950">
                <span class="text-sm font-black tracking-widest text-zinc-950"><?= esc(surface_tail($account['slug'])) ?></span>
                <span class="h-1.5 w-1.5 rounded-full bg-zinc-950"></span>
                <span class="h-1.5 w-1.5 rounded-full bg-zinc-950"></span>
                <span class="h-1.5 w-1.5 rounded-full bg-zinc-950"></span>
                <span class="h-1.5 w-1.5 rounded-full bg-zinc-950"></span>
            </div>

            <div class="mt-6 flex items-end justify-between gap-4">
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-zinc-950"><?= esc($account['name']) ?></p>
                    <p class="mt-1 text-sm text-zinc-950/70"><?= esc($account['note']) ?></p>
                </div>
                <p class="shrink-0 text-sm font-medium text-zinc-950/70"><?= esc($account['movement_count']) ?> mutasi</p>
            </div>


            <span class="absolute bottom-0 left-1/2 h-2 w-20 -translate-x-1/2 rounded-t-2xl bg-zinc-950"></span>

            <span class="absolute top-0 left-1/2 h-1 w-20 -translate-x-1/2 rounded-b-2xl bg-zinc-950"></span>

        </div>
    </section>

    <section class="rounded-3xl bg-white p-4 shadow-sm">
        <div class="flex items-center justify-between">
            <h2 class="text-base font-semibold text-zinc-950">Arus pada Rekening / Dompet</h2>
            <p class="text-xs text-zinc-500">Sesuai filter</p>
        </div>
        <div class="mt-4 grid grid-cols-2 gap-3">
            <div class="rounded-2xl bg-zinc-50 p-3">
                <p class="text-xs text-zinc-500">Uang Masuk</p>
                <p class="mt-2 text-sm font-semibold text-emerald-600"><?= esc(rupiah($account['income'])) ?></p>
            </div>
            <div class="rounded-2xl bg-zinc-50 p-3">
                <p class="text-xs text-zinc-500">Biaya / Belanja</p>
                <p class="mt-2 text-sm font-semibold text-rose-500"><?= esc(rupiah($account['expense'])) ?></p>
            </div>
            <div class="rounded-2xl bg-zinc-50 p-3">
                <p class="text-xs text-zinc-500">Pindah Masuk</p>
                <p class="mt-2 text-sm font-semibold text-sky-700"><?= esc(rupiah($account['transfer_in'])) ?></p>
            </div>
            <div class="rounded-2xl bg-zinc-50 p-3">
                <p class="text-xs text-zinc-500">Pindah Keluar</p>
                <p class="mt-2 text-sm font-semibold text-zinc-950"><?= esc(rupiah($account['transfer_out'])) ?></p>
            </div>
        </div>
    </section>

    <section class="rounded-3xl bg-white p-4 shadow-sm">
        <div class="flex items-center justify-between">
            <h2 class="text-base font-semibold text-zinc-950">Dipakai oleh Kegiatan</h2>
            <p class="text-xs text-zinc-500">Jejak mutasi per kegiatan</p>
        </div>
        <div class="mt-3 space-y-3">
            <?php if ($accountActivities === []): ?>
                <div class="rounded-2xl bg-zinc-50 px-4 py-4 text-sm text-zinc-500">Belum ada mutasi untuk rekening atau dompet ini pada filter saat ini.</div>
            <?php endif; ?>
            <?php foreach ($accountActivities as $activity): ?>
                <a href="<?= esc($activity['detail_url']) ?>" class="block rounded-2xl bg-zinc-50 p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-zinc-950"><?= esc($activity['name']) ?></p>
                            <p class="mt-1 text-xs text-zinc-500"><?= esc($activity['unit_name']) ?></p>
                        </div>
                        <span class="inline-flex items-center gap-1 rounded-full bg-white px-3 py-2 text-xs font-medium text-zinc-700">
                            <span class="material-symbols-rounded text-sm" aria-hidden="true">arrow_outward</span>
                            <span>Detail</span>
                        </span>
                    </div>

                    <div class="mt-3 grid grid-cols-2 gap-2 text-xs">
                        <div>
                            <p class="text-zinc-500">Masuk</p>
                            <p class="mt-1 font-semibold text-emerald-600"><?= esc(rupiah($activity['income'])) ?></p>
                        </div>
                        <div>
                            <p class="text-zinc-500">Biaya</p>
                            <p class="mt-1 font-semibold text-rose-500"><?= esc(rupiah($activity['expense'])) ?></p>
                        </div>
                        <div>
                            <p class="text-zinc-500">Pindah Masuk</p>
                            <p class="mt-1 font-semibold text-sky-700"><?= esc(rupiah($activity['transfer_in'])) ?></p>
                        </div>
                        <div>
                            <p class="text-zinc-500">Pindah Keluar</p>
                            <p class="mt-1 font-semibold text-zinc-950"><?= esc(rupiah($activity['transfer_out'])) ?></p>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="rounded-3xl bg-white pt-4 pb-1 shadow-sm overflow-hidden">
        <div class="flex items-center justify-between px-4">
            <h2 class="text-base font-semibold text-zinc-950">Penerima Terlibat</h2>
        </div>
        <div class="mt-4">
            <?php if (empty($involvedReceivers)): ?>
                <div class="px-4 pb-4">
                    <div class="rounded-2xl bg-zinc-50 p-6 text-center">
                        <p class="text-sm font-medium text-zinc-950">Belum ada data penerima.</p>
                        <p class="mt-1 text-xs text-zinc-500">Penerima akan muncul dari transaksi Honor & Gaji.</p>
                    </div>
                </div>
            <?php else: ?>
                <div class="flex flex-nowrap gap-3 overflow-x-auto px-4 pb-4 snap-x snap-mandatory" style="scrollbar-width: none;">
                    <style>
                        .overflow-x-auto::-webkit-scrollbar { display: none; }
                    </style>
                    
                    <?php foreach ($involvedReceivers as $receiver): ?>
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
            <h2 class="text-base font-semibold text-zinc-950">Mutasi Rekening / Dompet</h2>
            <p class="text-xs text-zinc-500"><?= esc($account['name']) ?></p>
        </div>
        <div class="mt-3 divide-y divide-zinc-100">
            <?php if ($accountTransactions === []): ?>
                <div class="py-6 text-sm text-zinc-500">Belum ada mutasi dummy untuk rekening atau dompet ini pada filter saat ini.</div>
            <?php endif; ?>
            <?php foreach ($accountTransactions as $transaction): ?>
                <?= view('partials/transaction_item', ['transaction' => $transaction]) ?>
            <?php endforeach; ?>
        </div>
    </section>
</div>
<?= $this->endSection() ?>
