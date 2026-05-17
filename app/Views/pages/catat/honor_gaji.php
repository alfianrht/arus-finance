<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<div class="mx-auto max-w-xl space-y-3">
    <?= view('partials/top_nav_back', [
        'title' => 'Honor & Gaji',
        'subtitle' => 'Pembayaran untuk SDM, tim, atau narasumber.',
        'backUrl' => $backUrl,
    ]) ?>

    <section class="rounded-2xl border border-orange-100 bg-orange-50 p-4">
        <p class="text-sm font-semibold text-orange-900">Form Terspesialisasi.</p>
        <p class="mt-1 text-sm text-orange-800">Kategori transaksi otomatis terkunci ke Beban Honor agar laporan tahunan tetap valid dan rapi.</p>
    </section>

    <?= view('partials/capture_assist', [
        'captureKey' => 'honor_gaji',
        'title' => 'Lampirkan slip atau tanda terima',
        'description' => 'Nanti AI akan membaca bukti dokumen lalu membantu mengisi nominal, tanggal, dan keterangan.',
        'previewTitle' => 'Belum ada dokumen dipilih',
        'previewDescription' => 'Gunakan kamera untuk memfoto bukti fisik, atau upload PDF slip gaji jika ada.',
        'autoFields' => ['Nominal', 'Tanggal', 'Keterangan'],
    ]) ?>

    <form method="post" action="<?= site_url('catat/keluar/honor-gaji') ?>" class="space-y-4 rounded-3xl bg-white p-5 shadow-sm">
        <?= csrf_field() ?>
        
        <?php if (session()->getFlashdata('error')): ?>
            <div class="rounded-2xl bg-rose-50 p-4 text-sm font-medium text-rose-600">
                <?= esc(session()->getFlashdata('error')) ?>
            </div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('success')): ?>
            <div class="rounded-2xl bg-emerald-50 p-4 text-sm font-medium text-emerald-600">
                <?= esc(session()->getFlashdata('success')) ?>
            </div>
        <?php endif; ?>

        <div class="relative flex items-center justify-between gap-3 rounded-2xl bg-zinc-50 px-4 py-3">
            <div>
                <p class="text-sm font-semibold text-zinc-950">Form manual tetap tersedia</p>
                <p class="mt-1 text-xs text-zinc-500">Isi manual tetap bisa dipakai jika Anda tidak memiliki bukti dokumen fisik.</p>
            </div>
            <span class="absolute top-2 right-2 rounded-full bg-white px-3 py-2 text-xs font-medium text-zinc-700">Auto-fill siap</span>
        </div>
        
        <div class="rounded-3xl bg-zinc-50 p-5 text-center">
            <p class="text-xs font-medium uppercase tracking-wide text-zinc-500">Total Dibayarkan (THP)</p>
            <input type="text" name="amount" value="0" class="mt-3 w-full border-0 bg-transparent text-center text-4xl font-semibold tracking-tight text-zinc-950 outline-none" required>
            
            <div class="mt-6 flex items-center justify-center gap-2 w-full overflow-hidden">
                <p class="shrink-0 text-sm font-medium text-zinc-700">Biaya Admin:</p>
                <select name="admin_fee" onchange="this.nextElementSibling.style.display = this.value === 'manual' ? 'block' : 'none'" class="h-10 min-w-0 flex-1 rounded-xl border border-zinc-200 bg-white px-2 text-sm font-medium text-zinc-950 focus:border-lime-400 focus:outline-none focus:ring-1 focus:ring-lime-400">
                    <option value="0">Rp 0</option>
                    <option value="2500">Rp 2.500</option>
                    <option value="6500">Rp 6.500</option>
                    <option value="manual">Lainnya</option>
                </select>
                <input type="text" name="admin_fee_manual" placeholder="Isi nominal" style="display: none;" class="h-10 w-24 shrink-0 rounded-xl border border-zinc-200 bg-white px-3 text-center text-sm font-medium text-zinc-950 focus:border-lime-400 focus:outline-none focus:ring-1 focus:ring-lime-400">
            </div>
            <p class="mt-3 text-[10px] text-zinc-500">Otomatis dicatat terpisah sebagai beban administrasi bank.</p>
        </div>

        <div class="space-y-2">
            <label class="text-sm font-medium text-zinc-700">Kategori</label>
            <div class="flex flex-wrap gap-2">
                <span class="bg-lime-400 text-zinc-950 rounded-full px-3 py-2 text-sm font-medium"><?= esc($honorCat['name'] ?? 'Honor') ?></span>
                <span class="bg-zinc-100 text-zinc-700 rounded-full px-3 py-2 text-sm font-medium">Beban Honor & Gaji</span>
            </div>
            <input type="hidden" name="category_id" value="<?= esc($honorCat['id'] ?? '') ?>">
            <p class="text-xs text-zinc-500">Kategori ini dikunci khusus untuk menu Honor & Gaji.</p>
        </div>

        <div class="space-y-2">
            <label class="text-sm font-medium text-zinc-700">Pilih Penerima / Karyawan</label>
            <select name="receiver_id" class="h-12 w-full rounded-2xl border border-zinc-100 bg-white px-4 text-sm text-zinc-950 focus:ring-2 focus:ring-lime-400" required>
                <option value="">-- Pilih dari Master Kontak --</option>
                <?php foreach ($receivers as $receiver): ?>
                    <option value="<?= esc($receiver['id']) ?>"><?= esc($receiver['name']) ?> (<?= esc($receiver['type'] ?? 'Lainnya') ?>)</option>
                <?php endforeach; ?>
            </select>
            <p class="text-xs text-zinc-500">Penerima belum ada? <a href="<?= site_url('pengaturan/penerima/tambah') ?>" class="text-blue-600 font-medium">Tambah baru</a>.</p>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div class="space-y-2">
                <label class="text-sm font-medium text-zinc-700">Unit / Program</label>
                <select name="unit_id" class="h-12 w-full rounded-2xl border border-zinc-100 bg-white px-4 text-sm text-zinc-950 focus:ring-2 focus:ring-lime-400" required>
                    <option value="">Pilih Unit...</option>
                    <?php foreach ($units as $unit): ?>
                        <option value="<?= esc($unit['id']) ?>"><?= esc($unit['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="space-y-2">
                <label class="text-sm font-medium text-zinc-700">Kegiatan</label>
                <select name="activity_id" class="h-12 w-full rounded-2xl border border-zinc-100 bg-white px-4 text-sm text-zinc-950 focus:ring-2 focus:ring-lime-400" required>
                    <option value="">Pilih Kegiatan...</option>
                    <?php foreach ($activities as $activity): ?>
                        <option value="<?= esc($activity['id']) ?>">
                            <?= esc($activity['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="space-y-2">
            <label class="text-sm font-medium text-zinc-700">Keluar dari rekening / dompet</label>
            <select name="from_account_id" class="h-12 w-full rounded-2xl border border-zinc-100 bg-white px-4 text-sm text-zinc-950 focus:ring-2 focus:ring-lime-400" required>
                <option value="">Pilih Rekening...</option>
                <?php foreach ($accounts as $account): ?>
                    <option value="<?= esc($account['id']) ?>"><?= esc($account['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="space-y-2">
            <label class="text-sm font-medium text-zinc-700">Tanggal Bayar</label>
            <input type="date" name="transaction_date" value="<?= date('Y-m-d') ?>" class="h-12 w-full rounded-2xl border border-zinc-100 bg-white px-4 text-sm text-zinc-950 focus:ring-2 focus:ring-lime-400" required>
        </div>

        <div class="space-y-2">
            <label class="text-sm font-medium text-zinc-700">Keterangan / Periode</label>
            <textarea name="notes" rows="3" class="w-full rounded-2xl border border-zinc-100 bg-white px-4 py-3 text-sm text-zinc-950 focus:ring-2 focus:ring-lime-400" placeholder="Misal: Gaji bulan Mei 2026 atau Honor narasumber seminar..." required></textarea>
        </div>

        <div class="rounded-2xl border border-zinc-200 bg-zinc-50 px-4 py-4">
            <p class="text-sm font-semibold text-zinc-950">Status dokumen / bukti</p>
            <p class="mt-1 text-sm text-zinc-500">Belum ada dokumen slip atau bukti tanda terima yang dibaca.</p>
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
