<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<div class="space-y-3">
    <header class="flex items-center gap-3">
        <a href="<?= esc($backUrl) ?>" class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-white text-zinc-700 shadow-sm">
            <span class="material-symbols-rounded text-base" aria-hidden="true">arrow_back</span>
        </a>
        <div>
            <p class="text-sm text-zinc-500">Master Data</p>
            <p class="text-2xl font-semibold tracking-tight text-zinc-950">Rekening / Dompet</p>
        </div>
    </header>

    <div class="flex justify-end">
        <a href="<?= site_url('pengaturan/rekening-dompet/tambah') ?>" class="inline-flex h-11 items-center justify-center gap-2 rounded-full bg-zinc-950 px-5 text-sm font-semibold text-white">
            <span class="material-symbols-rounded text-base" aria-hidden="true">add</span>
            <span>Tambah Rekening</span>
        </a>
    </div>

    <section class="relative rounded-3xl border border-zinc-950 bg-white p-5">
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-zinc-500">Arus Uang</p>
                <p class="mt-3 text-lg font-semibold text-zinc-950"><?= esc(count($accountSummaries)) ?> sumber dan tujuan dana disiapkan</p>
                <p class="mt-1 text-sm text-zinc-500">Master ini dipakai untuk uang masuk, biaya, pindah dana, dan tiap rekening langsung menyimpan pos laporan terkaitnya.</p>
            </div>
            <span class="absolute top-2 right-2 rounded-full bg-zinc-100 px-3 py-2 text-xs font-medium text-zinc-700">Tanpa mapping terpisah</span>
        </div>
    </section>

    <section class="space-y-3">
        <?php foreach ($accountSummaries as $account): ?>
            <div class="space-y-2">
                <?php
                $masterAccount = $account;
                $masterAccount['detail_url'] = site_url('pengaturan/rekening-dompet/' . $account['slug'] . '/edit');
                ?>
                <?= view('partials/account_card', ['account' => $masterAccount, 'cardWidthClass' => 'w-full']) ?>
                <div class="flex items-center justify-between px-1">
                    <p class="text-xs text-zinc-500"><?= esc($account['report_position_name'] ?? 'Belum dipetakan') ?></p>
                    <a href="<?= esc(site_url('rekening/' . $account['slug'])) ?>" class="text-sm font-medium text-zinc-700">Lihat Mutasi</a>
                </div>
            </div>
        <?php endforeach; ?>
    </section>

    <section class="rounded-3xl bg-white p-4 shadow-sm">
        <div class="flex items-center justify-between gap-3">
            <h2 class="text-base font-semibold text-zinc-950">Field yang Disiapkan</h2>
            <a href="<?= site_url('pengaturan/rekening-dompet/tambah') ?>" class="text-sm font-medium text-zinc-700">Buka Form</a>
        </div>
        <div class="mt-4 grid gap-3 sm:grid-cols-2">
            <?php foreach (['Nama rekening / dompet', 'Jenis penyimpanan dana', 'Singkatan / logo', 'Pos laporan terkait', 'Saldo awal / dummy balance', 'Catatan penggunaan'] as $field): ?>
                <a href="<?= site_url('pengaturan/rekening-dompet/tambah') ?>" class="block rounded-2xl border border-zinc-200 px-4 py-3 text-sm text-zinc-700"><?= esc($field) ?></a>
            <?php endforeach; ?>
        </div>
    </section>
</div>
<?= $this->endSection() ?>
