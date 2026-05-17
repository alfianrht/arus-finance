<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<div class="mx-auto max-w-xl space-y-3">
    <?= view('partials/top_nav_back', [
        'title' => 'Uang Masuk',
        'subtitle' => 'Form singkat, unit dan kegiatan mengikuti konteks aktif.',
        'backUrl' => $backUrl,
    ]) ?>

    <?= view('partials/capture_assist', [
        'captureKey' => 'uang_masuk',
        'title' => 'Tangkap invoice atau bukti transfer dulu',
        'description' => 'Nanti AI akan membaca bukti masuk lalu membantu mengisi nominal, kategori pemasukan, rekening, tanggal, dan keterangan.',
        'previewTitle' => 'Belum ada invoice atau bukti transfer',
        'previewDescription' => 'Arahkan kamera ke invoice, mutasi bank, atau bukti transfer agar form di bawah bisa terisi lebih cepat.',
        'autoFields' => ['Nominal', 'Kategori Pemasukan', 'Rekening', 'Tanggal', 'Keterangan'],
    ]) ?>

    <form class="space-y-4 rounded-3xl bg-white p-5 shadow-sm">
        <div class="flex items-center justify-between gap-3 rounded-2xl bg-zinc-50 px-4 py-3">
            <div>
                <p class="text-sm font-semibold text-zinc-950">Form manual tetap tersedia</p>
                <p class="mt-1 text-xs text-zinc-500">Gunakan form ini bila hasil scan perlu dicek atau dilengkapi.</p>
            </div>
            <span class="rounded-full bg-white px-3 py-2 text-xs font-medium text-zinc-700">Auto-fill siap</span>
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

        <div class="rounded-3xl bg-zinc-50 p-5 text-center">
            <p class="text-xs font-medium uppercase tracking-wide text-zinc-500">Nominal</p>
            <input type="text" value="Rp 58.000.000" class="mt-3 w-full border-0 bg-transparent text-center text-4xl font-semibold tracking-tight text-zinc-950 outline-none">
        </div>

        <div class="space-y-2">
            <label class="text-sm font-medium text-zinc-700">Kategori Pemasukan</label>
            <select class="h-12 w-full rounded-2xl border border-zinc-100 bg-white px-4 text-sm text-zinc-950 focus:ring-2 focus:ring-lime-400">
                <?php foreach ($incomeCategories as $category): ?>
                    <option <?= $category['name'] === $selectedIncomeCategory ? 'selected' : '' ?>><?= esc($category['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <p class="text-xs text-zinc-500">Kategori ini langsung terhubung ke pos laporan pendapatan yang sesuai.</p>
        </div>

        <div class="space-y-2">
            <label class="text-sm font-medium text-zinc-700">Masuk ke rekening / dompet</label>
            <select class="h-12 w-full rounded-2xl border border-zinc-100 bg-white px-4 text-sm text-zinc-950 focus:ring-2 focus:ring-lime-400">
                <?php foreach ($accounts as $account): ?>
                    <option <?= $account['name'] === $activeContext['default_income_account'] ? 'selected' : '' ?>><?= esc($account['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="space-y-2">
            <label class="text-sm font-medium text-zinc-700">Tanggal</label>
            <input type="text" value="17 Mei 2026" class="h-12 w-full rounded-2xl border border-zinc-100 bg-white px-4 text-sm text-zinc-950 focus:ring-2 focus:ring-lime-400">
        </div>

        <div class="space-y-2">
            <label class="text-sm font-medium text-zinc-700">Keterangan</label>
            <textarea rows="3" class="w-full rounded-2xl border border-zinc-100 bg-white px-4 py-3 text-sm text-zinc-950 focus:ring-2 focus:ring-lime-400">Pembayaran proyek masuk termin April</textarea>
        </div>

        <div class="rounded-2xl border border-zinc-200 bg-zinc-50 px-4 py-4">
            <p class="text-sm font-semibold text-zinc-950">Status bukti</p>
            <p class="mt-1 text-sm text-zinc-500">Belum ada file yang dibaca. Setelah kamera atau upload digunakan, AI akan mengisi beberapa field di form ini.</p>
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
