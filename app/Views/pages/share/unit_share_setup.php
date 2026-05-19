<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<?php $surfaceText = surface_label($unit['short_name'] ?? $unit['name']); ?>
<div class="space-y-3">
    <?= view('partials/top_nav_back', [
        'title' => 'Bagikan Laporan',
        'subtitle' => 'Unit / Program',
        'backUrl' => $backUrl,
    ]) ?>

    <section class="rounded-3xl border border-zinc-100 bg-white p-4 shadow-sm">
        <div class="flex items-start justify-between gap-4">
            <div class="min-w-0">
                <span class="inline-flex rounded-full bg-lime-100 px-3 py-1.5 text-xs font-medium text-zinc-950">Akses Publik dengan PIN</span>
                <p class="mt-3 text-2xl font-semibold leading-tight tracking-tight text-zinc-950"><?= esc($unit['name']) ?></p>
                <p class="mt-1.5 text-sm text-zinc-500">Siapkan satu tautan publik yang hanya bisa dibuka setelah PIN dimasukkan.</p>
            </div>
            <span class="shrink-0 rounded-full border border-zinc-200 bg-white px-3 py-1.5 text-xs font-medium text-zinc-700"><?= esc($surfaceText) ?></span>
        </div>
        <div class="mt-4 grid grid-cols-2 gap-2 sm:grid-cols-4">
            <div class="rounded-2xl border border-zinc-100 bg-zinc-50 px-3.5 py-2.5">
                <p class="text-xs text-zinc-500">Status</p>
                <p class="mt-0.5 text-sm font-semibold text-zinc-950">Draft setup</p>
            </div>
            <div class="rounded-2xl border border-zinc-100 bg-zinc-50 px-3.5 py-2.5">
                <p class="text-xs text-zinc-500">PIN Demo</p>
                <p class="mt-0.5 text-sm font-semibold tabular-nums text-zinc-950"><?= esc($demoPin) ?></p>
            </div>
            <div class="rounded-2xl border border-zinc-100 bg-zinc-50 px-3.5 py-2.5">
                <p class="text-xs text-zinc-500">Akses</p>
                <p class="mt-0.5 text-sm font-semibold text-zinc-950">Publik</p>
            </div>
            <div class="rounded-2xl border border-zinc-100 bg-zinc-50 px-3.5 py-2.5">
                <p class="text-xs text-zinc-500">Mode</p>
                <p class="mt-0.5 text-sm font-semibold text-zinc-950">View only</p>
            </div>
        </div>
    </section>

    <section class="rounded-3xl border border-zinc-100 bg-white p-4 shadow-sm">
        <div class="flex items-center justify-between gap-3">
            <h2 class="text-base font-semibold text-zinc-950">Tautan Publik</h2>
            <a href="<?= esc($shareUrl) ?>?preview=1" target="_blank" rel="noreferrer" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-zinc-200 bg-white text-zinc-950">
                <span class="material-symbols-rounded text-base" aria-hidden="true">open_in_new</span>
            </a>
        </div>
        <div class="mt-3 rounded-2xl bg-zinc-50 p-4">
            <p class="text-xs font-medium uppercase tracking-wide text-zinc-500">URL Laporan</p>
            <p class="mt-2 break-all text-sm font-semibold text-zinc-950"><?= esc($shareUrl) ?></p>
        </div>
        <div class="mt-3 grid gap-3">
            <label class="block">
                <span class="text-xs font-medium uppercase tracking-wide text-zinc-500">PIN Akses</span>
                <input type="text" value="<?= esc($demoPin) ?>" class="mt-2 h-12 w-full rounded-2xl border border-zinc-200 bg-zinc-50 px-4 text-sm font-semibold tracking-[0.2em] text-zinc-950 outline-none" readonly>
            </label>
            <div class="grid grid-cols-2 gap-2">
                <button type="button" class="inline-flex h-12 items-center justify-center gap-2 rounded-full bg-lime-400 px-5 text-sm font-semibold text-zinc-950">
                    <span class="material-symbols-rounded text-base" aria-hidden="true">content_copy</span>
                    <span>Salin Link</span>
                </button>
                <button type="button" class="inline-flex h-12 items-center justify-center gap-2 rounded-full bg-zinc-950 px-5 text-sm font-semibold text-white">
                <span class="material-symbols-rounded text-base" aria-hidden="true">autorenew</span>
                <span>Ganti PIN</span>
                </button>
            </div>
        </div>
        <p class="mt-3 text-xs text-zinc-500">Tahap ini masih fokus ke tampilan. PIN, histori share, dan validasi akses akan disambungkan di tahap berikutnya.</p>
    </section>

    <section class="rounded-3xl border border-zinc-100 bg-white p-4 shadow-sm">
        <h2 class="text-base font-semibold text-zinc-950">Yang Akan Terbuka</h2>
        <div class="mt-3 grid gap-2">
            <?php foreach ([
                ['icon' => 'account_balance_wallet', 'title' => 'Ringkasan Saldo', 'text' => 'Saldo, masuk, keluar, dan surplus unit.'],
                ['icon' => 'folder_supervised', 'title' => 'Kegiatan Unit', 'text' => 'Daftar kegiatan yang berada di bawah unit ini.'],
                ['icon' => 'receipt_long', 'title' => 'Transaksi Terbaru', 'text' => 'Tampilan ringkas yang tetap nyaman dibaca di ponsel.'],
                ['icon' => 'shield_lock', 'title' => 'Akses PIN', 'text' => 'Link publik tetap terkunci sampai PIN dimasukkan.'],
            ] as $item): ?>
                <div class="flex items-start gap-3 rounded-2xl bg-zinc-50 px-3 py-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-white text-zinc-950">
                        <span class="material-symbols-rounded text-[20px]" aria-hidden="true"><?= esc($item['icon']) ?></span>
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-zinc-950"><?= esc($item['title']) ?></p>
                        <p class="mt-0.5 text-xs text-zinc-500"><?= esc($item['text']) ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
</div>
<?= $this->endSection() ?>
