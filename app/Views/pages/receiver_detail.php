<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<div class="space-y-3">
    <?= view('partials/top_nav_back', [
        'title' => $receiver['name'],
        'subtitle' => 'Penerima',
        'backUrl' => $backUrl,
    ]) ?>

    <?php $surfaceText = surface_label($receiver['name']); ?>
    <section class="relative overflow-hidden rounded-3xl bg-white p-5 text-zinc-950 shadow-sm">
        <div class="absolute inset-0 bg-white/40" aria-hidden="true"></div>
        <p class="pointer-events-none absolute -bottom-3 left-3 text-7xl font-black uppercase tracking-tight text-zinc-100" aria-hidden="true"><?= esc($surfaceText) ?></p>

        <div class="relative flex items-start justify-between gap-4">
            <div class="min-w-0">
                <div class="inline-flex items-center gap-2 rounded-full bg-zinc-100 px-3 py-2 text-[10px] font-bold uppercase tracking-wider text-zinc-600">
                    <span class="h-1.5 w-1.5 rounded-full bg-lime-400"></span>
                    <span><?= esc($receiver['type']) ?></span>
                </div>
                <p class="mt-3 text-2xl font-semibold leading-tight text-zinc-950"><?= esc($receiver['name']) ?></p>
                <?php if ($receiver['notes'] !== ''): ?>
                    <p class="mt-2 max-w-xl text-sm text-zinc-500"><?= esc($receiver['notes']) ?></p>
                <?php endif; ?>
            </div>
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-zinc-900 text-white">
                <span class="material-symbols-rounded text-2xl" aria-hidden="true">account_circle</span>
            </div>
        </div>

        <div class="relative mt-3 border-t border-zinc-100 pt-4">
            <p class="text-[10px] font-bold uppercase tracking-wider text-zinc-500">Total Nominal Terkait</p>
            <p class="mt-1 text-4xl font-black tracking-tight text-zinc-950"><?= esc(rupiah($receiver['total_amount'])) ?></p>
            <p class="mt-2 text-sm text-zinc-500"><?= esc((string) $receiver['transaction_count']) ?> transaksi terkait</p>
        </div>
    </section>

    <div class="grid grid-cols-2 gap-2 sm:gap-3">
        <div class="rounded-2xl bg-white p-3 shadow-sm sm:rounded-3xl sm:p-4">
            <div class="flex items-center gap-1.5 sm:gap-2">
                <div class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-lime-100 text-zinc-950 sm:h-8 sm:w-8">
                    <span class="material-symbols-rounded text-[11px] sm:text-sm">payments</span>
                </div>
                <p class="truncate text-[9px] font-bold uppercase tracking-wider text-zinc-500 sm:text-[11px]">Total Nominal</p>
            </div>
            <p class="mt-1.5 truncate text-sm font-black text-zinc-950 sm:mt-3 sm:text-base"><?= esc(rupiah($receiver['total_amount'])) ?></p>
        </div>
        <div class="rounded-2xl bg-white p-3 shadow-sm sm:rounded-3xl sm:p-4">
            <div class="flex items-center gap-1.5 sm:gap-2">
                <div class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-zinc-100 text-zinc-700 sm:h-8 sm:w-8">
                    <span class="material-symbols-rounded text-[11px] sm:text-sm">receipt_long</span>
                </div>
                <p class="truncate text-[9px] font-bold uppercase tracking-wider text-zinc-500 sm:text-[11px]">Jumlah Transaksi</p>
            </div>
            <p class="mt-1.5 truncate text-sm font-black text-zinc-950 sm:mt-3 sm:text-base"><?= esc((string) $receiver['transaction_count']) ?></p>
        </div>
    </div>

    <section class="rounded-3xl bg-white p-4 shadow-sm">
        <div class="flex items-center justify-between">
            <h2 class="text-base font-semibold text-zinc-950">Kegiatan Terkait</h2>
            <p class="text-xs text-zinc-500">Melibatkan penerima ini</p>
        </div>
        <div class="mt-3 space-y-3">
            <?php if ($receiverActivities === []): ?>
                <div class="rounded-2xl bg-zinc-50 px-4 py-4 text-sm text-zinc-500">Belum ada kegiatan yang melibatkan penerima ini.</div>
            <?php endif; ?>
            <?php foreach ($receiverActivities as $activity): ?>
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
                    <div class="mt-3 grid grid-cols-3 gap-2 text-xs">
                        <div>
                            <p class="text-zinc-500">Masuk</p>
                            <p class="mt-1 font-semibold text-emerald-600"><?= esc(rupiah($activity['income'])) ?></p>
                        </div>
                        <div>
                            <p class="text-zinc-500">Biaya</p>
                            <p class="mt-1 font-semibold text-rose-500"><?= esc(rupiah($activity['expense'])) ?></p>
                        </div>
                        <div>
                            <p class="text-zinc-500">Transaksi</p>
                            <p class="mt-1 font-semibold text-zinc-950"><?= esc((string) $activity['transaction_count']) ?></p>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="rounded-3xl bg-white p-4 shadow-sm">
        <div class="flex items-center justify-between">
            <h2 class="text-base font-semibold text-zinc-950">Rekening Terkait</h2>
            <p class="text-xs text-zinc-500">Dipakai transaksi penerima ini</p>
        </div>
        <div class="mt-3 space-y-3">
            <?php if ($receiverAccounts === []): ?>
                <div class="rounded-2xl bg-zinc-50 px-4 py-4 text-sm text-zinc-500">Belum ada rekening terkait untuk penerima ini.</div>
            <?php endif; ?>
            <?php foreach ($receiverAccounts as $account): ?>
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
            <h2 class="text-base font-semibold text-zinc-950">Transaksi Terkait</h2>
            <p class="text-xs text-zinc-500"><?= esc($receiver['name']) ?></p>
        </div>
        <div class="mt-3 divide-y divide-zinc-100">
            <?php if ($receiverTransactions === []): ?>
                <div class="py-6 text-sm text-zinc-500">Belum ada transaksi untuk penerima ini.</div>
            <?php endif; ?>
            <?php foreach ($receiverTransactions as $transaction): ?>
                <?= view('partials/transaction_item', ['transaction' => $transaction]) ?>
            <?php endforeach; ?>
        </div>
    </section>
</div>
<?= $this->endSection() ?>
