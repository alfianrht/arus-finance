<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<div class="mx-auto max-w-xl space-y-3">
    <header class="flex items-center gap-3">
        <a href="<?= esc($backUrl) ?>" class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-white text-zinc-700 shadow-sm">
            <span class="material-symbols-rounded text-base" aria-hidden="true">arrow_back</span>
        </a>
        <div>
            <p class="text-2xl font-semibold tracking-tight text-zinc-950">Biaya / Belanja</p>
            <p class="text-sm text-zinc-500">Untuk pengeluaran operasional yang mengurangi surplus.</p>
        </div>
    </header>

    <?= view('partials/capture_assist', [
        'captureKey' => 'biaya_belanja',
        'title' => 'Foto struk atau nota dulu',
        'description' => 'Nanti AI akan membaca bukti pengeluaran lalu membantu mengisi nominal, kategori, rekening, tanggal, dan keterangan.',
        'previewTitle' => 'Belum ada struk atau nota',
        'previewDescription' => 'Gunakan kamera untuk foto struk fisik, atau upload PDF / gambar jika bukti sudah tersimpan.',
        'autoFields' => ['Nominal', 'Kategori', 'Rekening', 'Tanggal', 'Keterangan'],
    ]) ?>

    <form class="space-y-4 rounded-3xl bg-white p-5 shadow-sm">
        <div class="relative flex items-center justify-between gap-3 rounded-2xl bg-zinc-50 px-4 py-3">
            <div>
                <p class="text-sm font-semibold text-zinc-950">Form manual tetap tersedia</p>
                <p class="mt-1 text-xs text-zinc-500">Isi manual tetap bisa dipakai jika bukti kurang jelas atau hasil deteksi perlu diperbaiki.</p>
            </div>
            <span class="absolute top-2 right-2 rounded-full bg-white px-3 py-2 text-xs font-medium text-zinc-700">Auto-fill siap</span>
        </div>

        <div class="rounded-3xl bg-zinc-50 p-5 text-center">
            <p class="text-xs font-medium uppercase tracking-wide text-zinc-500">Nominal</p>
            <input type="text" value="Rp 250.000" class="mt-3 w-full border-0 bg-transparent text-center text-4xl font-semibold tracking-tight text-zinc-950 outline-none">
        </div>

        <div class="space-y-2">
            <label class="text-sm font-medium text-zinc-700">Kategori</label>
            <div class="flex flex-wrap gap-2">
                <?php foreach ($expenseCategories as $category): ?>
                    <span class="<?= $selectedCategory === $category['name'] ? 'bg-lime-400 text-zinc-950' : 'bg-zinc-100 text-zinc-700' ?> rounded-full px-3 py-2 text-sm font-medium">
                        <?= esc($category['name']) ?>
                    </span>
                <?php endforeach; ?>
            </div>
            <p class="text-xs text-zinc-500">Kategori ini nanti otomatis dipetakan ke pos beban di laporan tahunan.</p>
        </div>

        <div class="space-y-2">
            <label class="text-sm font-medium text-zinc-700">Keluar dari rekening / dompet</label>
            <select class="h-12 w-full rounded-2xl border border-zinc-100 bg-white px-4 text-sm text-zinc-950 focus:ring-2 focus:ring-lime-400">
                <?php foreach ($accounts as $account): ?>
                    <option <?= $account['name'] === $activeContext['default_expense_account'] ? 'selected' : '' ?>><?= esc($account['name']) ?></option>
                <?php endforeach; ?>
            </select>
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

        <div class="space-y-2">
            <label class="text-sm font-medium text-zinc-700">Tanggal</label>
            <input type="text" value="15 Mei 2026" class="h-12 w-full rounded-2xl border border-zinc-100 bg-white px-4 text-sm text-zinc-950 focus:ring-2 focus:ring-lime-400">
        </div>

        <div class="space-y-2">
            <label class="text-sm font-medium text-zinc-700">Keterangan</label>
            <textarea rows="3" class="w-full rounded-2xl border border-zinc-100 bg-white px-4 py-3 text-sm text-zinc-950 focus:ring-2 focus:ring-lime-400">Transport koordinasi dan pengantaran dokumen</textarea>
        </div>

        <div class="rounded-2xl border border-zinc-200 bg-zinc-50 px-4 py-4">
            <p class="text-sm font-semibold text-zinc-950">Status bukti</p>
            <p class="mt-1 text-sm text-zinc-500">Belum ada bukti yang dibaca. Hasil scan nanti akan mengisi kategori dan nominal terlebih dahulu.</p>
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
