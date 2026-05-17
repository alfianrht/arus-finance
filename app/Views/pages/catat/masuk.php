<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<div class="mx-auto max-w-xl space-y-3">
    <?= view('partials/top_nav_back', [
        'title' => 'Uang Masuk',
        'subtitle' => 'Form singkat, unit dan kegiatan mengikuti konteks aktif.',
        'backUrl' => $backUrl,
    ]) ?>

    <form action="<?= esc(site_url('catat/masuk')) ?>" method="post" enctype="multipart/form-data" class="space-y-4 rounded-3xl bg-white p-5 shadow-sm">
        <?= csrf_field() ?>
        <?= view('partials/capture_assist', [
            'captureKey' => 'uang_masuk',
            'title' => 'Tangkap invoice atau bukti transfer dulu',
            'description' => 'Nanti AI akan membaca bukti masuk lalu membantu mengisi nominal, kategori pemasukan, rekening, tanggal, dan keterangan.',
            'previewTitle' => 'Belum ada invoice atau bukti transfer',
            'previewDescription' => 'Arahkan kamera ke invoice, mutasi bank, atau bukti transfer agar form di bawah bisa terisi lebih cepat.',
            'autoFields' => ['Nominal', 'Kategori Pemasukan', 'Rekening', 'Tanggal', 'Keterangan'],
        ]) ?>
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
                <select name="unit_id" class="h-12 w-full rounded-2xl border border-zinc-100 bg-white px-4 text-sm text-zinc-950 focus:ring-2 focus:ring-lime-400">
                    <?php foreach ($units as $unit): ?>
                        <option value="<?= esc($unit['id']) ?>" <?= (string) old('unit_id', (string) $activeContext['unit_id']) === (string) $unit['id'] ? 'selected' : '' ?>><?= esc($unit['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="space-y-2">
                <label class="text-sm font-medium text-zinc-700">Kegiatan</label>
                <select name="activity_id" class="h-12 w-full rounded-2xl border border-zinc-100 bg-white px-4 text-sm text-zinc-950 focus:ring-2 focus:ring-lime-400">
                    <?php foreach ($activitySummaries as $activity): ?>
                        <option value="<?= esc($activity['id']) ?>" <?= (string) old('activity_id', (string) $activeContext['activity_id']) === (string) $activity['id'] ? 'selected' : '' ?>>
                            <?= esc($activity['name']) ?> · <?= esc($activity['unit_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="rounded-3xl bg-zinc-50 p-5 text-center">
            <p class="text-xs font-medium uppercase tracking-wide text-zinc-500">Nominal</p>
            <input type="text" inputmode="numeric" name="amount" value="<?= esc(old('amount', '')) ?>" placeholder="Rp 0" class="mt-3 w-full border-0 bg-transparent text-center text-4xl font-semibold tracking-tight text-zinc-950 outline-none">
        </div>

        <div class="space-y-2">
            <label class="text-sm font-medium text-zinc-700">Kategori Pemasukan</label>
            <select name="category_id" class="h-12 w-full rounded-2xl border border-zinc-100 bg-white px-4 text-sm text-zinc-950 focus:ring-2 focus:ring-lime-400">
                <?php foreach ($incomeCategories as $category): ?>
                    <option value="<?= esc($category['id']) ?>" <?= (string) old('category_id', '') === (string) $category['id'] || (old('category_id') === null && $category['name'] === $selectedIncomeCategory) ? 'selected' : '' ?>><?= esc($category['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <p class="text-xs text-zinc-500">Kategori ini langsung terhubung ke pos laporan pendapatan yang sesuai.</p>
        </div>

        <div class="space-y-2">
            <label class="text-sm font-medium text-zinc-700">Masuk ke rekening / dompet</label>
            <select name="to_account_id" class="h-12 w-full rounded-2xl border border-zinc-100 bg-white px-4 text-sm text-zinc-950 focus:ring-2 focus:ring-lime-400">
                <?php foreach ($accounts as $account): ?>
                    <option value="<?= esc($account['id']) ?>" <?= (string) old('to_account_id', '') === (string) $account['id'] || (old('to_account_id') === null && $account['name'] === $activeContext['default_income_account']) ? 'selected' : '' ?>><?= esc($account['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="space-y-2">
            <label class="text-sm font-medium text-zinc-700">Tanggal</label>
            <input type="date" name="transaction_date" value="<?= esc(old('transaction_date', date('Y-m-d'))) ?>" class="h-12 w-full rounded-2xl border border-zinc-100 bg-white px-4 text-sm text-zinc-950 focus:ring-2 focus:ring-lime-400">
        </div>

        <div class="space-y-2">
            <label class="text-sm font-medium text-zinc-700">Keterangan</label>
            <textarea name="notes" rows="3" placeholder="Contoh: Pembayaran SPP" class="w-full rounded-2xl border border-zinc-100 bg-white px-4 py-3 text-sm text-zinc-950 focus:ring-2 focus:ring-lime-400"><?= esc(old('notes', '')) ?></textarea>
        </div>

        <div class="grid grid-cols-1 gap-3 pt-2 sm:grid-cols-2">
            <button type="submit" name="action" value="save" class="inline-flex h-14 items-center justify-center gap-2 rounded-full bg-zinc-950 px-6 text-sm font-semibold text-white">
                <span class="material-symbols-rounded text-base" aria-hidden="true">edit_square</span>
                Simpan
            </button>
            <button type="submit" name="action" value="save_add" class="inline-flex h-14 items-center justify-center rounded-full border border-zinc-100 bg-white px-6 text-sm font-semibold text-zinc-900">
                Simpan &amp; Tambah Lagi
            </button>
        </div>
    </form>
</div>
<?= $this->endSection() ?>
