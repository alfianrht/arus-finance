<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<div class="mx-auto max-w-xl space-y-3">
    <?= view('partials/top_nav_back', [
        'title' => 'Uang Keluar',
        'subtitle' => 'Pilih dulu jenis pengeluaran agar form tetap pendek.',
        'backUrl' => $backUrl,
    ]) ?>

    <div class="space-y-3">
        <a href="<?= esc($activeContext['pindah_dana_url']) ?>" class="relative block rounded-3xl bg-zinc-950 p-5 text-white shadow-sm">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-lg font-semibold">Pindah Dana</p>
                    <p class="mt-2 text-sm text-zinc-300">Untuk BRI PT ke Dana Operasional Cago, BRI PT ke BCA PT, dan perpindahan saldo internal lainnya.</p>
                </div>
                <span class="absolute top-2 right-2 rounded-full bg-white px-3 py-2 text-xs font-medium text-zinc-950">Pindah Dana</span>
            </div>
        </a>
        <a href="<?= esc($activeContext['biaya_url']) ?>" class="relative block rounded-3xl bg-white p-5 shadow-sm">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-lg font-semibold text-zinc-950">Biaya Operasional</p>
                    <p class="mt-2 text-sm text-zinc-500">Untuk transport, konsumsi, cetak, ATK, dan belanja barang/jasa habis pakai lainnya.</p>
                </div>
                <span class="absolute top-2 right-2 rounded-full bg-rose-50 px-3 py-2 text-xs font-medium text-rose-600">Biaya</span>
            </div>
        </a>

        <a href="<?= esc(site_url('catat/keluar/honor-gaji')) ?>" class="relative block rounded-3xl bg-white p-5 shadow-sm">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-lg font-semibold text-zinc-950">Honor & Gaji</p>
                    <p class="mt-2 text-sm text-zinc-500">Untuk pembayaran honorarium, gaji tim, fee narasumber, atau insentif proyek.</p>
                </div>
                <span class="absolute top-2 right-2 rounded-full bg-orange-50 px-3 py-2 text-xs font-medium text-orange-600">Honor</span>
            </div>
        </a>

    </div>
</div>
<?= $this->endSection() ?>
