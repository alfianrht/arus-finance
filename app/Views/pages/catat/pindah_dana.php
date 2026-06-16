<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<div class="space-y-3">
    <?= view('partials/top_nav_back', [
        'title' => 'Pindah Dana',
        'subtitle' => 'Perpindahan saldo internal yang tidak masuk biaya.',
        'backUrl' => $backUrl,
    ]) ?>

    <section class="rounded-2xl border border-sky-100 bg-sky-50 p-4">
        <p class="text-sm font-semibold text-sky-900">Pindah Dana tidak dihitung sebagai biaya.</p>
        <p class="mt-1 text-sm text-sky-800">Gunakan layar ini untuk perpindahan antar rekening, dompet, atau kas yang Anda miliki.</p>
    </section>

    <form method="post" action="<?= site_url('catat/keluar/pindah-dana') ?>" enctype="multipart/form-data" class="space-y-4">
        <?= csrf_field() ?>
        <input type="hidden" name="return_to" value="<?= esc(old('return_to', $returnTo ?? '')) ?>">
        <?= view('partials/capture_assist', [
            'captureKey' => 'pindah_dana',
            'title' => 'Bukti Transaksi',
            'description' => 'Nanti AI akan membaca bukti mutasi lalu membantu mengisi nominal, rekening asal, rekening tujuan, tanggal, dan keterangan.',
            'previewTitle' => 'Belum ada bukti mutasi dipilih',
            'previewDescription' => 'Cocok untuk screenshot mutasi mobile banking, bukti transfer internal, atau nota perpindahan dana.',
            'autoFields' => ['Nominal', 'Biaya Admin', 'Dari Rekening', 'Ke Rekening', 'Tanggal', 'Keterangan'],
            'noteLabel' => 'Auto-fill AI',
            'scanEndpoint' => site_url('ai/scan-bill'),
            'scanMode' => 'transfer',
            'cameraAccept' => 'image/jpeg,image/png,image/webp',
            'uploadAccept' => 'image/jpeg,image/png,image/webp',
        ]) ?>

        <div class="rounded-3xl bg-white border border-zinc-100 p-6 text-center shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-zinc-400">Nominal Transfer</p>
            <input type="text" inputmode="numeric" name="amount" value="<?= esc(old('amount', '')) ?>" placeholder="Rp 0" class="mt-3 w-full border-0 bg-transparent text-center text-4xl font-semibold tracking-tight text-zinc-950 outline-none tabular-nums placeholder-zinc-300 focus:placeholder-transparent transition-all" required>
            
            <div class="mt-6 flex items-center justify-center gap-2 w-full overflow-hidden">
                <p class="shrink-0 text-sm font-semibold text-zinc-900">Biaya Admin:</p>
                <div class="relative flex-1">
                    <select name="admin_fee_preset" onchange="this.nextElementSibling.style.display = this.value === 'manual' ? 'block' : 'none'" class="h-10 w-full appearance-none rounded-xl border border-zinc-200 bg-white pl-3 pr-8 text-sm font-medium text-zinc-950 outline-none transition focus:border-zinc-300 focus:ring-1 focus:ring-lime-400">
                        <option value="0">Rp 0</option>
                        <option value="2500">Rp 2.500</option>
                        <option value="6500">Rp 6.500</option>
                        <option value="manual">Lainnya</option>
                    </select>
                    <span class="pointer-events-none absolute right-2.5 top-1/2 -translate-y-1/2 material-symbols-rounded text-[18px] text-zinc-400">expand_more</span>
                </div>
                <input type="text" inputmode="numeric" name="admin_fee_custom" placeholder="Isi nominal" style="display: none;" class="h-10 w-24 shrink-0 rounded-xl border border-zinc-200 bg-white px-3 text-center text-sm font-medium text-zinc-950 focus:border-lime-400 focus:outline-none focus:ring-1 focus:ring-lime-400">
            </div>
            <p class="mt-3 text-[10px] text-zinc-500">Otomatis dicatat sebagai beban operasional / administrasi bank.</p>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div class="space-y-2">
                <label class="text-sm font-semibold text-zinc-900">Dari rekening / dompet</label>
                <div class="relative">
                    <select name="from_account_id" class="h-12 w-full appearance-none rounded-2xl border border-zinc-100 bg-white pl-4 pr-10 text-sm text-zinc-950 outline-none transition focus:border-zinc-300 focus:ring-2 focus:ring-lime-400" required>
                        <?php foreach ($accounts as $account): ?>
                            <option value="<?= esc($account['id']) ?>" <?= (string) old('from_account_id', '') === (string) $account['id'] || (old('from_account_id') === null && (string) ($activeContext['default_expense_account_id'] ?? 0) === (string) $account['id']) ? 'selected' : '' ?>><?= esc($account['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <span class="pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 material-symbols-rounded text-[20px] text-zinc-400">expand_more</span>
                </div>
            </div>
            <div class="space-y-2">
                <label class="text-sm font-semibold text-zinc-900">Ke rekening / dompet</label>
                <div class="relative">
                    <select name="to_account_id" class="h-12 w-full appearance-none rounded-2xl border border-zinc-100 bg-white pl-4 pr-10 text-sm text-zinc-950 outline-none transition focus:border-zinc-300 focus:ring-2 focus:ring-lime-400" required>
                        <option value="">Pilih Tujuan...</option>
                        <?php foreach ($accounts as $account): ?>
                            <option value="<?= esc($account['id']) ?>" <?= (string) old('to_account_id', '') === (string) $account['id'] ? 'selected' : '' ?>><?= esc($account['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <span class="pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 material-symbols-rounded text-[20px] text-zinc-400">expand_more</span>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div class="space-y-2">
                <label class="text-sm font-semibold text-zinc-900">Unit / Program</label>
                <div class="relative">
                    <select name="unit_id" class="h-12 w-full appearance-none rounded-2xl border border-zinc-100 bg-white pl-4 pr-10 text-sm text-zinc-950 outline-none transition focus:border-zinc-300 focus:ring-2 focus:ring-lime-400" required>
                        <?php foreach ($units as $unit): ?>
                            <option value="<?= esc($unit['id']) ?>" <?= (string) old('unit_id', (string) ($activeContext['unit_id'] ?? '')) === (string) $unit['id'] ? 'selected' : '' ?>><?= esc($unit['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <span class="pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 material-symbols-rounded text-[20px] text-zinc-400">expand_more</span>
                </div>
            </div>
            <div class="space-y-2">
                <label class="text-sm font-semibold text-zinc-900">Kegiatan</label>
                <div class="relative">
                    <select name="activity_id" class="h-12 w-full appearance-none rounded-2xl border border-zinc-100 bg-white pl-4 pr-10 text-sm text-zinc-950 outline-none transition focus:border-zinc-300 focus:ring-2 focus:ring-lime-400" required>
                        <?php foreach ($activities as $activity): ?>
                            <option value="<?= esc($activity['id']) ?>" <?= (string) old('activity_id', (string) ($activeContext['activity_id'] ?? '')) === (string) $activity['id'] ? 'selected' : '' ?>>
                                <?= esc($activity['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <span class="pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 material-symbols-rounded text-[20px] text-zinc-400">expand_more</span>
                </div>
            </div>
        </div>

        <?= view('partials/project_pocket_field', [
            'projectPocketField' => $projectPocketField,
            'fieldName' => 'project_pocket_id',
            'label' => 'Dari Kantong',
            'placeholder' => 'Pilih kantong asal',
        ]) ?>

        <?= view('partials/project_pocket_field', [
            'projectPocketField' => $projectPocketField,
            'fieldName' => 'counter_project_pocket_id',
            'label' => 'Ke Kantong',
            'placeholder' => 'Pilih kantong tujuan',
            'mode' => 'execution',
        ]) ?>

        <div class="space-y-2">
            <label class="text-sm font-semibold text-zinc-900">Tanggal</label>
            <input type="date" name="transaction_date" value="<?= esc(old('transaction_date', date('Y-m-d'))) ?>" class="h-12 w-full rounded-2xl border border-zinc-100 bg-white px-4 text-sm text-zinc-950 outline-none transition focus:border-zinc-300 focus:ring-2 focus:ring-lime-400" required>
        </div>

        <div class="space-y-2">
            <label class="text-sm font-semibold text-zinc-900">Keterangan</label>
            <textarea name="notes" rows="3" class="w-full rounded-2xl border border-zinc-100 bg-white px-4 py-3 text-sm text-zinc-950 outline-none transition focus:border-zinc-300 focus:ring-2 focus:ring-lime-400" required><?= esc(old('notes', '')) ?></textarea>
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

    const preset = form.querySelector('select[name="admin_fee_preset"]');
    const custom = form.querySelector('input[name="admin_fee_custom"]');
    if (preset && custom) {
        const fee = Number(data.admin_fee || 0);
        if (fee > 0) {
            const presetValues = Array.from(preset.options).map(function (option) { return option.value; });
            if (presetValues.includes(String(fee))) {
                preset.value = String(fee);
                custom.style.display = 'none';
                custom.value = '';
            } else {
                preset.value = 'manual';
                custom.style.display = 'block';
                custom.value = formatRupiah(fee);
            }
        } else {
            preset.value = '0';
            custom.style.display = 'none';
            custom.value = '';
        }
    }

    const dateInput = form.querySelector('input[name="transaction_date"]');
    if (dateInput && data.transaction_date) {
        dateInput.value = data.transaction_date;
    }

    selectOptionByMatch('select[name="from_account_id"]', data.from_account_suggestion || data.account_suggestion);
    selectOptionByMatch('select[name="to_account_id"]', data.to_account_suggestion);

    const notesInput = form.querySelector('textarea[name="notes"]');
    if (notesInput && data.description) {
        notesInput.value = data.description;
    }
};
</script>
<?= $this->endSection() ?>
