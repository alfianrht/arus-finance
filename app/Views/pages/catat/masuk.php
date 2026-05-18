<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<div class="mx-auto max-w-xl space-y-3">
    <?= view('partials/top_nav_back', [
        'title' => 'Uang Masuk',
        'subtitle' => 'Form singkat, unit dan kegiatan mengikuti konteks aktif.',
        'backUrl' => $backUrl,
    ]) ?>

    <form action="<?= esc(site_url('catat/masuk')) ?>" method="post" enctype="multipart/form-data" class="space-y-4">
        <?= csrf_field() ?>
        <?= view('partials/capture_assist', [
            'captureKey' => 'uang_masuk',
            'title' => 'Bukti Transaksi',
            'description' => 'Nanti AI akan membaca bukti masuk lalu membantu mengisi nominal, kategori pemasukan, rekening, tanggal, dan keterangan.',
            'previewTitle' => 'Belum ada invoice atau bukti transfer',
            'previewDescription' => 'Arahkan kamera ke invoice, mutasi bank, atau bukti transfer agar form di bawah bisa terisi lebih cepat.',
            'autoFields' => ['Nominal', 'Kategori Pemasukan', 'Rekening', 'Tanggal', 'Keterangan'],
            'noteLabel' => 'Auto-fill AI',
            'scanEndpoint' => site_url('ai/scan-bill'),
            'scanMode' => 'income',
            'cameraAccept' => 'image/jpeg,image/png,image/webp',
            'uploadAccept' => 'image/jpeg,image/png,image/webp',
        ]) ?>

        <div class="rounded-3xl bg-white border border-zinc-100 p-6 text-center shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-zinc-400">Nominal</p>
            <input type="text" inputmode="numeric" name="amount" value="<?= esc(old('amount', '')) ?>" placeholder="Rp 0" class="mt-3 w-full border-0 bg-transparent text-center text-4xl font-semibold tracking-tight text-zinc-950 outline-none tabular-nums placeholder-zinc-300 focus:placeholder-transparent transition-all" required>
        </div>

        <div class="space-y-2">
            <label class="text-sm font-semibold text-zinc-900">Kategori Pemasukan</label>
            <div class="relative">
                <select name="category_id" class="h-12 w-full appearance-none rounded-2xl border border-zinc-100 bg-white pl-4 pr-10 text-sm text-zinc-950 outline-none transition focus:border-zinc-300 focus:ring-2 focus:ring-lime-400" required>
                    <?php foreach ($incomeCategories as $category): ?>
                        <option value="<?= esc($category['id']) ?>" <?= (string) old('category_id', '') === (string) $category['id'] || (old('category_id') === null && $category['name'] === $selectedIncomeCategory) ? 'selected' : '' ?>><?= esc($category['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <span class="pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 material-symbols-rounded text-[20px] text-zinc-400">expand_more</span>
            </div>
            <p class="text-xs text-zinc-500">Kategori ini langsung terhubung ke pos laporan pendapatan yang sesuai.</p>
        </div>

        <div class="space-y-2">
            <label class="text-sm font-semibold text-zinc-900">Masuk ke rekening / dompet</label>
            <div class="relative">
                <select name="to_account_id" class="h-12 w-full appearance-none rounded-2xl border border-zinc-100 bg-white pl-4 pr-10 text-sm text-zinc-950 outline-none transition focus:border-zinc-300 focus:ring-2 focus:ring-lime-400" required>
                    <?php foreach ($accounts as $account): ?>
                        <option value="<?= esc($account['id']) ?>" <?= (string) old('to_account_id', '') === (string) $account['id'] || (old('to_account_id') === null && $account['name'] === $activeContext['default_income_account']) ? 'selected' : '' ?>><?= esc($account['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <span class="pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 material-symbols-rounded text-[20px] text-zinc-400">expand_more</span>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div class="space-y-2">
                <label class="text-sm font-semibold text-zinc-900">Unit / Program</label>
                <div class="relative">
                    <select name="unit_id" class="h-12 w-full appearance-none rounded-2xl border border-zinc-100 bg-white pl-4 pr-10 text-sm text-zinc-950 outline-none transition focus:border-zinc-300 focus:ring-2 focus:ring-lime-400" required>
                        <?php foreach ($units as $unit): ?>
                            <option value="<?= esc($unit['id']) ?>" <?= (string) old('unit_id', (string) $activeContext['unit_id']) === (string) $unit['id'] ? 'selected' : '' ?>><?= esc($unit['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <span class="pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 material-symbols-rounded text-[20px] text-zinc-400">expand_more</span>
                </div>
            </div>
            <div class="space-y-2">
                <label class="text-sm font-semibold text-zinc-900">Kegiatan</label>
                <div class="relative">
                    <select name="activity_id" class="h-12 w-full appearance-none rounded-2xl border border-zinc-100 bg-white pl-4 pr-10 text-sm text-zinc-950 outline-none transition focus:border-zinc-300 focus:ring-2 focus:ring-lime-400" required>
                        <?php foreach ($activitySummaries as $activity): ?>
                            <option value="<?= esc($activity['id']) ?>" <?= (string) old('activity_id', (string) $activeContext['activity_id']) === (string) $activity['id'] ? 'selected' : '' ?>>
                                <?= esc($activity['name']) ?> · <?= esc($activity['unit_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <span class="pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 material-symbols-rounded text-[20px] text-zinc-400">expand_more</span>
                </div>
            </div>
        </div>

        <div class="space-y-2">
            <label class="text-sm font-semibold text-zinc-900">Tanggal</label>
            <input type="date" name="transaction_date" value="<?= esc(old('transaction_date', date('Y-m-d'))) ?>" class="h-12 w-full rounded-2xl border border-zinc-100 bg-white px-4 text-sm text-zinc-950 outline-none transition focus:border-zinc-300 focus:ring-2 focus:ring-lime-400" required>
        </div>

        <div class="space-y-2">
            <label class="text-sm font-semibold text-zinc-900">Keterangan</label>
            <textarea name="notes" rows="3" placeholder="Contoh: Pembayaran SPP" class="w-full rounded-2xl border border-zinc-100 bg-white px-4 py-3 text-sm text-zinc-950 outline-none transition focus:border-zinc-300 focus:ring-2 focus:ring-lime-400"><?= esc(old('notes', '')) ?></textarea>
        </div>

        <div class="grid grid-cols-1 gap-3 pt-2 sm:grid-cols-2">
            <button type="submit" name="action" value="save" class="inline-flex h-14 items-center justify-center gap-2 rounded-full bg-zinc-950 px-6 text-sm font-semibold text-white transition-all hover:bg-zinc-800 active:scale-[0.98] duration-150 shadow-sm">
                <span class="material-symbols-rounded text-base" aria-hidden="true">edit_square</span>
                Simpan
            </button>
            <button type="submit" name="action" value="save_add" class="inline-flex h-14 items-center justify-center rounded-full border border-zinc-100 bg-white px-6 text-sm font-semibold text-zinc-900 transition-all hover:bg-zinc-50 hover:border-zinc-200 active:scale-[0.98] duration-150 shadow-sm">
                Simpan &amp; Tambah Lagi
            </button>
        </div>
    </form>
</div>
<script>
window.ArusApplyBillScan = window.ArusApplyBillScan || function (form, data) {
    const formatRupiah = function (value) {
        const number = Number(value || 0);
        if (!Number.isFinite(number) || number <= 0) return '';
        return String(Math.round(number)).replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    };

    const selectOptionByMatch = function (selector, suggestion) {
        if (!suggestion) return false;
        const select = form.querySelector(selector);
        if (!select) return false;
        const target = String(suggestion).trim().toLowerCase();
        for (const option of Array.from(select.options)) {
            const text = option.textContent.trim().toLowerCase();
            if (text === target || text.includes(target) || target.includes(text)) {
                select.value = option.value;
                return true;
            }
        }
        return false;
    };

    const amountInput = form.querySelector('input[name="amount"]');
    if (amountInput && data.amount !== null && data.amount !== undefined) {
        amountInput.value = formatRupiah(data.amount);
    }

    const dateInput = form.querySelector('input[name="transaction_date"]');
    if (dateInput && data.transaction_date) {
        dateInput.value = data.transaction_date;
    }

    selectOptionByMatch('select[name="category_id"]', data.category_suggestion);
    selectOptionByMatch('select[name="to_account_id"]', data.to_account_suggestion || data.account_suggestion);

    const notesInput = form.querySelector('textarea[name="notes"]');
    if (notesInput && data.description) {
        notesInput.value = data.description;
    }
};
</script>
<?= $this->endSection() ?>
