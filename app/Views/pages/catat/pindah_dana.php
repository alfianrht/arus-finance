<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<div class="mx-auto max-w-xl space-y-3">
    <?= view('partials/top_nav_back', [
        'title' => 'Pindah Dana',
        'subtitle' => 'Perpindahan saldo internal yang tidak masuk biaya.',
        'backUrl' => $backUrl,
    ]) ?>

    <section class="rounded-2xl border border-sky-100 bg-sky-50 p-4">
        <p class="text-sm font-semibold text-sky-900">Pindah Dana tidak dihitung sebagai biaya.</p>
        <p class="mt-1 text-sm text-sky-800">Gunakan layar ini untuk perpindahan BRI PT, BCA PT, Dana Operasional Cago, atau Kas Tunai.</p>
    </section>

    <form method="post" action="<?= site_url('catat/keluar/pindah-dana') ?>" enctype="multipart/form-data" class="space-y-4 rounded-3xl bg-white p-5 shadow-sm">
        <?= csrf_field() ?>
        <?= view('partials/capture_assist', [
            'captureKey' => 'pindah_dana',
            'title' => 'Tangkap bukti mutasi antar rekening dulu',
            'description' => 'Nanti AI akan membaca bukti mutasi lalu membantu mengisi nominal, rekening asal, rekening tujuan, tanggal, dan keterangan.',
            'previewTitle' => 'Belum ada bukti mutasi dipilih',
            'previewDescription' => 'Cocok untuk screenshot mutasi mobile banking, bukti transfer internal, atau nota perpindahan dana.',
            'autoFields' => ['Nominal', 'Dari Rekening', 'Ke Rekening', 'Tanggal', 'Keterangan'],
            'noteLabel' => 'Cepat di HP',
        ]) ?>

        <div class="relative flex items-center justify-between gap-3 rounded-2xl bg-zinc-50 px-4 py-3">
            <div>
                <p class="text-sm font-semibold text-zinc-950">Form manual tetap tersedia</p>
                <p class="mt-1 text-xs text-zinc-500">Pakai ini untuk cek ulang asal dan tujuan dana jika hasil deteksi perlu disesuaikan.</p>
            </div>
            <span class="absolute top-1 right-1 rounded-full bg-white px-3 py-2 text-xs font-medium text-zinc-700">Auto-fill siap</span>
        </div>

        <div class="rounded-3xl bg-zinc-50 p-5 text-center">
            <p class="text-xs font-medium uppercase tracking-wide text-zinc-500">Nominal Transfer</p>
            <input type="text" inputmode="numeric" name="amount" value="<?= esc(old('amount', '0')) ?>" class="mt-3 w-full border-0 bg-transparent text-center text-4xl font-semibold tracking-tight text-zinc-950 outline-none" required>
            
            <div class="mt-6 flex items-center justify-center gap-2 w-full overflow-hidden">
                <p class="shrink-0 text-sm font-medium text-zinc-700">Biaya Admin:</p>
                <select name="admin_fee" onchange="this.nextElementSibling.style.display = this.value === 'manual' ? 'block' : 'none'" class="h-10 min-w-0 flex-1 rounded-xl border border-zinc-200 bg-white px-2 text-sm font-medium text-zinc-950 focus:border-lime-400 focus:outline-none focus:ring-1 focus:ring-lime-400">
                    <option value="0">Rp 0</option>
                    <option value="2500">Rp 2.500</option>
                    <option value="6500">Rp 6.500</option>
                    <option value="manual">Lainnya</option>
                </select>
                <input type="text" inputmode="numeric" name="admin_fee_manual" placeholder="Isi nominal" style="display: none;" class="h-10 w-24 shrink-0 rounded-xl border border-zinc-200 bg-white px-3 text-center text-sm font-medium text-zinc-950 focus:border-lime-400 focus:outline-none focus:ring-1 focus:ring-lime-400">
            </div>
            <p class="mt-3 text-[10px] text-zinc-500">Otomatis dicatat sebagai beban operasional / administrasi bank.</p>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div class="space-y-2">
                <label class="text-sm font-medium text-zinc-700">Unit / Program</label>
                <select name="unit_id" class="h-12 w-full rounded-2xl border border-zinc-100 bg-white px-4 text-sm text-zinc-950 focus:ring-2 focus:ring-lime-400" required>
                    <option value="">Pilih Unit...</option>
                    <?php foreach ($units as $unit): ?>
                        <option value="<?= esc($unit['id']) ?>" <?= (string) old('unit_id', (string) ($activeContext['unit_id'] ?? '')) === (string) $unit['id'] ? 'selected' : '' ?>><?= esc($unit['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="space-y-2">
                <label class="text-sm font-medium text-zinc-700">Kegiatan</label>
                <select name="activity_id" class="h-12 w-full rounded-2xl border border-zinc-100 bg-white px-4 text-sm text-zinc-950 focus:ring-2 focus:ring-lime-400" required>
                    <option value="">Pilih Kegiatan...</option>
                    <?php foreach ($activities as $activity): ?>
                        <option value="<?= esc($activity['id']) ?>" <?= (string) old('activity_id', (string) ($activeContext['activity_id'] ?? '')) === (string) $activity['id'] ? 'selected' : '' ?>>
                            <?= esc($activity['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div class="space-y-2">
                <label class="text-sm font-medium text-zinc-700">Dari rekening / dompet</label>
                <select name="from_account_id" class="h-12 w-full rounded-2xl border border-zinc-100 bg-white px-4 text-sm text-zinc-950 focus:ring-2 focus:ring-lime-400" required>
                    <option value="">Pilih Asal...</option>
                    <?php foreach ($accounts as $account): ?>
                        <option value="<?= esc($account['id']) ?>" <?= (string) old('from_account_id', '') === (string) $account['id'] ? 'selected' : '' ?>><?= esc($account['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="space-y-2">
                <label class="text-sm font-medium text-zinc-700">Ke rekening / dompet</label>
                <select name="to_account_id" class="h-12 w-full rounded-2xl border border-zinc-100 bg-white px-4 text-sm text-zinc-950 focus:ring-2 focus:ring-lime-400" required>
                    <option value="">Pilih Tujuan...</option>
                    <?php foreach ($accounts as $account): ?>
                        <option value="<?= esc($account['id']) ?>" <?= (string) old('to_account_id', '') === (string) $account['id'] ? 'selected' : '' ?>><?= esc($account['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="space-y-2">
            <label class="text-sm font-medium text-zinc-700">Tanggal</label>
            <input type="date" name="transaction_date" value="<?= esc(old('transaction_date', date('Y-m-d'))) ?>" class="h-12 w-full rounded-2xl border border-zinc-100 bg-white px-4 text-sm text-zinc-950 focus:ring-2 focus:ring-lime-400" required>
        </div>

        <div class="space-y-2">
            <label class="text-sm font-medium text-zinc-700">Keterangan</label>
            <textarea name="notes" rows="3" class="w-full rounded-2xl border border-zinc-100 bg-white px-4 py-3 text-sm text-zinc-950 focus:ring-2 focus:ring-lime-400" required><?= esc(old('notes', '')) ?></textarea>
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
