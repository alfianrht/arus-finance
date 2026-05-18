<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<?php $surfaceText = surface_label($unit['short_name'] ?? $unit['name']); ?>
<div class="space-y-3">
    <?= view('partials/top_nav_back', [
        'title' => $unit['name'],
        'subtitle' => 'Unit / Program',
        'backUrl' => $backUrl ?? site_url('rekap'),
    ]) ?>

    <?php if (isset($activeContext)): ?>
    <section class="rounded-2xl bg-lime-50 p-4">
        <p class="text-xs font-medium uppercase tracking-wide text-zinc-500">Konteks cepat untuk pencatatan</p>
        <p class="mt-2 text-sm font-semibold text-zinc-950"><?= esc($activeContext['display']) ?></p>
        <p class="mt-1 text-sm text-zinc-600">Tombol aksi di halaman ini akan langsung memakai kegiatan tersebut.</p>
    </section>
    <?php endif; ?>

    <section class="relative overflow-hidden rounded-3xl bg-lime-400 p-5 text-zinc-950 shadow-sm">
        <div class="absolute inset-0 bg-white/10" aria-hidden="true"></div>
        <div class="relative flex items-start justify-between gap-4">
            <div class="min-w-0">
                <p class="text-xs font-medium uppercase tracking-wide text-zinc-900/60">Unit / Program</p>
                <p class="mt-2 text-2xl font-semibold leading-tight text-zinc-950"><?= esc($unit['name']) ?></p>
            </div>
            <p class="text-lg font-black uppercase tracking-tight text-zinc-950"><?= esc($surfaceText) ?></p>
        </div>

        <div class="relative mt-6">
            <p class="text-xs font-medium uppercase tracking-wide text-zinc-900/60">Laba Sementara</p>
            <p class="mt-2 text-4xl font-black tracking-tight text-zinc-950"><?= esc(rupiah($unit['surplus'])) ?></p>
            <?php if (isset($activeContext)): ?>
                <p class="mt-3 text-sm text-zinc-900/75">Kegiatan utama: <?= esc($activeContext['activity_name']) ?></p>
            <?php endif; ?>
        </div>

        <p class="pointer-events-none absolute -bottom-3 left-4 text-7xl font-black uppercase tracking-tight text-white/30" aria-hidden="true"><?= esc($surfaceText) ?></p>

        <div class="relative mt-4 grid grid-cols-2 gap-3 border-t border-white/50 pt-4 sm:grid-cols-4">
            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-zinc-900/60">Masuk</p>
                <p class="mt-1 text-sm font-semibold text-zinc-950"><?= esc(rupiah($unit['income'])) ?></p>
            </div>
            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-zinc-900/60">Biaya</p>
                <p class="mt-1 text-sm font-semibold text-zinc-950"><?= esc(rupiah($unit['expense'])) ?></p>
            </div>
            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-zinc-900/60">Surplus</p>
                <p class="mt-1 text-sm font-semibold text-zinc-950"><?= esc(rupiah($unit['surplus'])) ?></p>
            </div>
            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-zinc-900/60">Saldo Terkait</p>
                <p class="mt-1 text-sm font-semibold text-zinc-950"><?= esc(rupiah($unit['related_balance'] ?? 0)) ?></p>
            </div>
        </div>

        <?php if (isset($activeContext)): ?>
        <div class="relative mt-4 flex items-center justify-between gap-3">
            <p class="text-sm font-semibold tracking-wide text-zinc-950">•••• <?= esc(surface_tail($unit['slug'])) ?></p>
            <span class="inline-flex items-center gap-1 rounded-full bg-zinc-950 px-3 py-2 text-xs font-semibold text-white">
                <span class="material-symbols-rounded text-sm" aria-hidden="true">stacks</span>
                <span>Pilih dari kegiatan</span>
            </span>
        </div>
        <?php endif; ?>
    </section>

    <section class="space-y-3">
        <div class="flex items-center justify-between">
            <h2 class="text-base font-semibold text-zinc-950">Daftar Kegiatan</h2>
            <p class="text-xs text-zinc-500">Turunan dari unit ini</p>
        </div>
        <div class="space-y-3 md:grid md:grid-cols-2 md:gap-3 md:space-y-0">
            <?php foreach ($unit['activities'] as $activity): ?>
                <?= view('partials/activity_card', ['activity' => $activity]) ?>
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
                        <?= view('partials/receiver_card', ['receiver' => $receiver, 'widthClass' => 'w-48 sm:w-52']) ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <section class="rounded-3xl bg-white p-4 shadow-sm">
        <div class="flex items-center justify-between">
            <h2 class="text-base font-semibold text-zinc-950">Rekening Terlibat</h2>
            <p class="text-xs text-zinc-500">Terkait transaksi unit ini</p>
        </div>
        <div class="mt-3 space-y-3">
            <?php if ($involvedAccounts === []): ?>
                <div class="rounded-2xl bg-zinc-50 px-4 py-4 text-sm text-zinc-500">Belum ada rekening yang terlibat pada unit ini.</div>
            <?php endif; ?>
            <?php foreach ($involvedAccounts as $account): ?>
                <a href="<?= esc($account['detail_url']) ?>" class="block rounded-2xl bg-zinc-50 p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-zinc-950"><?= esc($account['name']) ?></p>
                            <p class="mt-1 text-xs text-zinc-500"><?= esc($account['kind']) ?><?= !empty($account['mark']) ? ' · ' . esc($account['mark']) : '' ?></p>
                        </div>
                        <span class="inline-flex items-center gap-1 rounded-full bg-white px-3 py-2 text-xs font-medium text-zinc-700">
                            <span class="material-symbols-rounded text-sm" aria-hidden="true">arrow_outward</span>
                            <span>Detail</span>
                        </span>
                    </div>
                    <div class="mt-3 grid grid-cols-3 gap-2 text-xs">
                        <div>
                            <p class="text-zinc-500">Masuk</p>
                            <p class="mt-1 font-semibold text-emerald-600"><?= esc(rupiah($account['income'])) ?></p>
                        </div>
                        <div>
                            <p class="text-zinc-500">Biaya</p>
                            <p class="mt-1 font-semibold text-rose-500"><?= esc(rupiah($account['expense'])) ?></p>
                        </div>
                        <div>
                            <p class="text-zinc-500">Transaksi</p>
                            <p class="mt-1 font-semibold text-zinc-950"><?= esc((string) $account['transaction_count']) ?></p>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="rounded-3xl bg-white py-4 shadow-sm">
        <div class="px-4 flex items-center justify-between">
            <h2 class="text-base font-semibold text-zinc-950">Transaksi Terakhir Unit</h2>
            <p class="text-xs text-zinc-500"><?= esc($unit['name']) ?></p>
        </div>
        <div class="mt-3 divide-y divide-zinc-100">
            <?php if ($unitTransactions === []): ?>
                <div class="py-6 text-sm text-zinc-500">Belum ada transaksi untuk unit ini.</div>
            <?php endif; ?>
            <?php foreach ($unitTransactions as $transaction): ?>
                <?= view('partials/transaction_item', ['transaction' => $transaction]) ?>
            <?php endforeach; ?>
        </div>
    </section>
</div>
<?= $this->endSection() ?>
