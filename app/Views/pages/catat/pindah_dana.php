<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<div class="mx-auto max-w-xl space-y-3">
    <header class="flex items-center gap-3">
        <a href="<?= esc($backUrl) ?>" class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-white text-zinc-700 shadow-sm">
            <span class="material-symbols-rounded text-base" aria-hidden="true">arrow_back</span>
        </a>
        <div>
            <p class="text-2xl font-semibold tracking-tight text-zinc-950">Pindah Dana</p>
            <p class="text-sm text-zinc-500">Perpindahan saldo internal yang tidak masuk biaya.</p>
        </div>
    </header>

    <section class="rounded-2xl border border-sky-100 bg-sky-50 p-4">
        <p class="text-sm font-semibold text-sky-900">Pindah Dana tidak dihitung sebagai biaya.</p>
        <p class="mt-1 text-sm text-sky-800">Gunakan layar ini untuk perpindahan BRI PT, BCA PT, Dana Operasional Cago, atau Kas Tunai.</p>
    </section>

    <?= view('partials/capture_assist', [
        'captureKey' => 'pindah_dana',
        'title' => 'Tangkap bukti mutasi antar rekening dulu',
        'description' => 'Nanti AI akan membaca bukti mutasi lalu membantu mengisi nominal, rekening asal, rekening tujuan, tanggal, dan keterangan.',
        'previewTitle' => 'Belum ada bukti mutasi dipilih',
        'previewDescription' => 'Cocok untuk screenshot mutasi mobile banking, bukti transfer internal, atau nota perpindahan dana.',
        'autoFields' => ['Nominal', 'Dari Rekening', 'Ke Rekening', 'Tanggal', 'Keterangan'],
        'noteLabel' => 'Cepat di HP',
    ]) ?>

    <form class="space-y-4 rounded-3xl bg-white p-5 shadow-sm">
        <div class="flex items-center justify-between gap-3 rounded-2xl bg-zinc-50 px-4 py-3">
            <div>
                <p class="text-sm font-semibold text-zinc-950">Form manual tetap tersedia</p>
                <p class="mt-1 text-xs text-zinc-500">Pakai ini untuk cek ulang asal dan tujuan dana jika hasil deteksi perlu disesuaikan.</p>
            </div>
            <span class="rounded-full bg-white px-3 py-2 text-xs font-medium text-zinc-700">Auto-fill siap</span>
        </div>

        <div class="rounded-3xl bg-zinc-50 p-5 text-center">
            <p class="text-xs font-medium uppercase tracking-wide text-zinc-500">Nominal</p>
            <input type="text" value="Rp 10.000.000" class="mt-3 w-full border-0 bg-transparent text-center text-4xl font-semibold tracking-tight text-zinc-950 outline-none">
        </div>


        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div class="space-y-2">
                <label class="text-sm font-medium text-zinc-700">Unit / Program</label>
                <select class="h-12 w-full rounded-2xl border border-zinc-100 bg-white px-4 text-sm text-zinc-950 focus:ring-2 focus:ring-lime-400">
                    <?php foreach ($units as $unit): ?>
                        <option <?= $unit['slug'] === $activeContext['unit_slug'] ? 'selected' : '' ?>><?= esc($unit['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="space-y-2">
                <label class="text-sm font-medium text-zinc-700">Kegiatan</label>
                <select class="h-12 w-full rounded-2xl border border-zinc-100 bg-white px-4 text-sm text-zinc-950 focus:ring-2 focus:ring-lime-400">
                    <?php foreach ($activitySummaries as $activity): ?>
                        <option <?= $activity['slug'] === $activeContext['activity_slug'] ? 'selected' : '' ?>>
                            <?= esc($activity['name']) ?> · <?= esc($activity['unit_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div class="space-y-2">
                <label class="text-sm font-medium text-zinc-700">Dari rekening / dompet</label>
                <select class="h-12 w-full rounded-2xl border border-zinc-100 bg-white px-4 text-sm text-zinc-950 focus:ring-2 focus:ring-lime-400">
                    <?php foreach ($accounts as $account): ?>
                        <option <?= $account['name'] === $activeContext['default_transfer_from'] ? 'selected' : '' ?>><?= esc($account['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="space-y-2">
                <label class="text-sm font-medium text-zinc-700">Ke rekening / dompet</label>
                <select class="h-12 w-full rounded-2xl border border-zinc-100 bg-white px-4 text-sm text-zinc-950 focus:ring-2 focus:ring-lime-400">
                    <?php foreach ($accounts as $account): ?>
                        <option <?= $account['name'] === $activeContext['default_transfer_to'] ? 'selected' : '' ?>><?= esc($account['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="space-y-2">
            <label class="text-sm font-medium text-zinc-700">Tanggal</label>
            <input type="text" value="16 Mei 2026" class="h-12 w-full rounded-2xl border border-zinc-100 bg-white px-4 text-sm text-zinc-950 focus:ring-2 focus:ring-lime-400">
        </div>

        <div class="space-y-2">
            <label class="text-sm font-medium text-zinc-700">Keterangan</label>
            <textarea rows="3" class="w-full rounded-2xl border border-zinc-100 bg-white px-4 py-3 text-sm text-zinc-950 focus:ring-2 focus:ring-lime-400">Alokasi dana lapangan dan pengeluaran cepat</textarea>
        </div>

        <div class="rounded-2xl border border-zinc-200 bg-zinc-50 px-4 py-4">
            <p class="text-sm font-semibold text-zinc-950">Status bukti</p>
            <p class="mt-1 text-sm text-zinc-500">Belum ada bukti mutasi yang dibaca. Perpindahan dana tetap tidak akan dihitung sebagai biaya walau diisi dari AI.</p>
        </div>

        <div class="grid grid-cols-1 gap-3 pt-2 sm:grid-cols-2">
            <button type="button" class="inline-flex h-14 items-center justify-center gap-2 rounded-full bg-zinc-950 px-6 text-sm font-semibold text-white">
                <span class="material-symbols-rounded text-base" aria-hidden="true">edit_square</span>
                Simpan
            </button>
            <button type="button" class="inline-flex h-14 items-center justify-center rounded-full border border-zinc-100 bg-white px-6 text-sm font-semibold text-zinc-900">
                Simpan &amp; Tambah Lagi
            </button>
        </div>
    </form>
</div>
<?= $this->endSection() ?>
