<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<div class="space-y-3">
    <header class="px-2 flex flex-col gap-4">
        <div class="flex items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <img src="<?= base_url('images/logo-primary-1.webp') ?>" alt="<?= esc($appName) ?>" class="h-6 w-auto">
                <span class="text-2xl font-bold tracking-tight text-zinc-950"><?= esc($appName) ?></span>
            </div>
            <div class="flex items-center gap-2">
                <span class="shrink-0 whitespace-nowrap rounded-full border border-lime-200 bg-lime-100 px-2 py-1 text-[10px] font-medium text-lime-950 shadow-sm"><?= esc($bookPeriodLabel ?? 'Tahun Buku Aktif') ?></span>
                <a href="<?= site_url('pengaturan') ?>" class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-white text-zinc-700 shadow-sm" aria-label="Buka pengaturan">
                    <span class="material-symbols-rounded text-base" style="font-size: 1.2rem;" aria-hidden="true">settings</span>
                </a>
            </div>
        </div>
        <div class="">
            <p class="text-sm text-zinc-500">Selamat Datang,</p>
            <p class="text-xl font-semibold tracking-tight text-zinc-950"><?= esc($institutionName) ?></p>
        </div>
    </header>
    
    <?= $this->include('partials/active_context') ?>

    <section class="rounded-3xl bg-white p-5 shadow-sm">
        <div class="flex items-center gap-2 text-zinc-500">
            <span class="material-symbols-rounded text-base" aria-hidden="true">account_balance_wallet</span>
            <p class="text-sm">Saldo Total</p>
        </div>
        <p class="mt-3 text-4xl font-semibold tracking-tight text-zinc-950 tabular-nums"><?= esc(rupiah($summary['balance'])) ?></p>
        <div class="mt-4 grid grid-cols-3 gap-3">
            <div>
                <p class="text-xs text-zinc-500">Masuk Bulan Ini</p>
                <p class="mt-2 text-sm font-semibold text-emerald-600"><?= esc(rupiah($summary['income'])) ?></p>
            </div>
            <div>
                <p class="text-xs text-zinc-500">Keluar Bulan Ini</p>
                <p class="mt-2 text-sm font-semibold text-rose-500"><?= esc(rupiah($summary['expense'])) ?></p>
            </div>
            <div>
                <p class="text-xs text-zinc-500">Laba Sementara</p>
                <p class="mt-2 text-sm font-semibold text-zinc-950"><?= esc(rupiah($summary['surplus'])) ?></p>
            </div>
        </div>
    </section>

    <div class="grid grid-cols-2 gap-3">
        <a href="<?= esc($activeContext['masuk_url']) ?>" class="inline-flex h-14 items-center justify-center gap-2 rounded-full bg-lime-400 px-4 text-sm font-semibold text-zinc-950">
            <span class="material-symbols-rounded text-base" aria-hidden="true">arrow_downward</span>
            <span>Uang Masuk</span>
        </a>
        <a href="<?= esc($activeContext['keluar_url']) ?>" class="inline-flex h-14 items-center justify-center gap-2 rounded-full bg-zinc-950 px-4 text-sm font-semibold text-white">
            <span class="material-symbols-rounded text-base" aria-hidden="true">arrow_outward</span>
            <span>Uang Keluar</span>
        </a>
    </div>

    <section class="space-y-3">
        <div class="flex items-center justify-between">
            <h2 class="text-base font-semibold text-zinc-950">Unit / Program</h2>
            <p class="text-xs text-zinc-500">Ringkasan bulan ini</p>
        </div>
        <?php if ($units === []): ?>
            <?= view('partials/empty_state', [
                'icon' => 'domain',
                'title' => 'Belum ada unit atau program.',
                'description' => 'Tambahkan Unit / Program dari Pengaturan agar ringkasan operasional mulai terisi.',
            ]) ?>
        <?php else: ?>
            <div class="space-y-3 md:grid md:grid-cols-2 md:gap-3 md:space-y-0">
                <?php foreach ($units as $unit): ?>
                    <?= view('partials/unit_card', ['unit' => $unit]) ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <section class="rounded-3xl border border-zinc-950 bg-white p-4">
        <div class="flex items-start justify-between gap-3">
            <div>
                <p class="text-base font-semibold text-zinc-950">Pengaturan & Master Data</p>
                <p class="mt-1 text-sm text-zinc-500">Kelola struktur dasar Arus sebelum data transaksi dibuat dinamis.</p>
            </div>
            <a href="<?= site_url('pengaturan') ?>" class="inline-flex h-10 items-center justify-center gap-2 rounded-full bg-zinc-950 px-4 text-sm font-semibold text-white">
                <span class="material-symbols-rounded text-base" aria-hidden="true">tune</span>
                <span>Buka</span>
            </a>
        </div>
        <div class="mt-4 grid grid-cols-2 gap-3 md:grid-cols-3">
            <?php foreach (array_slice($settingsShortcuts, 0, 6) as $shortcut): ?>
                <a href="<?= esc($shortcut['href']) ?>" class="rounded-2xl bg-zinc-50 p-3">
                    <span class="material-symbols-rounded text-base text-zinc-500" aria-hidden="true"><?= esc($shortcut['icon']) ?></span>
                    <p class="mt-3 text-sm font-semibold text-zinc-950"><?= esc($shortcut['title']) ?></p>
                    <p class="mt-1 text-xs text-zinc-500"><?= esc($shortcut['meta']) ?></p>
                </a>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="rounded-3xl bg-white py-4 shadow-sm">
        <div class="px-4 flex items-center justify-between">
            <h2 class="text-base font-semibold text-zinc-950">Transaksi Terakhir</h2>
            <a href="<?= site_url('rekap') ?>" class="text-xs font-medium text-zinc-500">Lihat rekap</a>
        </div>
        <?php if ($homeTransactions === []): ?>
            <div class="mt-3 px-4">
                <?= view('partials/empty_state', [
                    'icon' => 'receipt_long',
                    'title' => 'Belum ada transaksi terakhir.',
                    'description' => 'Mulai catat uang masuk atau uang keluar agar transaksi terbaru tampil di beranda.',
                    'compact' => true,
                ]) ?>
            </div>
        <?php else: ?>
            <div class="mt-3 divide-y divide-zinc-100">
                <?php foreach ($homeTransactions as $transaction): ?>
                    <?= view('partials/transaction_item', ['transaction' => $transaction]) ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</div>
<?= $this->endSection() ?>
