<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<div class="mx-auto max-w-xl space-y-3">
    <?= view('partials/top_nav_back', [
        'title' => $transaction['badge_label'],
        'subtitle' => $isEditMode ? 'Edit Transaksi' : 'Detail Transaksi',
        'backUrl' => $backUrl,
    ]) ?>

    <?php if ($transaction['type'] === 'pindah'): ?>
        <section class="rounded-2xl border border-sky-100 bg-sky-50 p-4">
            <p class="text-sm font-semibold text-sky-900">Pindah Dana tidak dihitung sebagai biaya.</p>
            <p class="mt-1 text-sm text-sky-800">Gunakan form ini untuk meninjau atau mengubah perpindahan saldo internal.</p>
        </section>
    <?php endif; ?>

    <form class="space-y-4 rounded-3xl bg-white p-5 shadow-sm">
        <div class="relative flex items-center justify-between gap-3 rounded-2xl bg-zinc-50 px-4 py-3">
            <div>
                <p class="text-sm font-semibold text-zinc-950"><?= $isEditMode ? 'Mode edit aktif' : 'Mode lihat aktif' ?></p>
                <p class="mt-1 pr-30 text-xs text-zinc-500">
                    <?= $isEditMode ? 'Field di bawah disiapkan seperti form catat, tanpa bukti transaksi karena ini bukan transaksi baru.' : 'Tampilan ini memakai form yang sama agar detail transaksi mudah ditinjau sebelum diedit.' ?>
                </p>
            </div>
            <?php if ($isEditMode): ?>
                <span class="absolute top-2 right-2 rounded-full bg-lime-400 px-3 py-2 text-xs font-medium text-zinc-950">Sedang diedit</span>
            <?php else: ?>
                <a href="<?= esc($editUrl) ?>" class="inline-flex h-10 items-center justify-center rounded-full bg-zinc-950 px-4 text-sm font-semibold text-white">Edit</a>
            <?php endif; ?>
        </div>

        <div class="rounded-3xl bg-zinc-50 p-5 text-center">
            <p class="text-xs font-medium uppercase tracking-wide text-zinc-500">Nominal</p>
            <input type="text" value="<?= esc($transactionForm['nominal_value']) ?>" <?= $isEditMode ? '' : 'readonly' ?> class="mt-3 w-full border-0 bg-transparent text-center text-4xl font-semibold tracking-tight text-zinc-950 outline-none">
        </div>

        <div class="space-y-2">
            <label class="text-sm font-medium text-zinc-700">Unit / Program</label>
            <select <?= $isEditMode ? '' : 'disabled' ?> class="h-12 w-full rounded-2xl border border-zinc-100 bg-white px-4 text-sm text-zinc-950 focus:ring-2 focus:ring-lime-400 disabled:text-zinc-950 disabled:opacity-100">
                <?php foreach ($transactionForm['unit_options'] as $option): ?>
                    <option <?= $option === $transactionForm['unit_value'] ? 'selected' : '' ?>><?= esc($option) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="space-y-2">
            <label class="text-sm font-medium text-zinc-700">Kegiatan</label>
            <select <?= $isEditMode ? '' : 'disabled' ?> class="h-12 w-full rounded-2xl border border-zinc-100 bg-white px-4 text-sm text-zinc-950 focus:ring-2 focus:ring-lime-400 disabled:text-zinc-950 disabled:opacity-100">
                <?php foreach ($transactionForm['activity_options'] as $option): ?>
                    <option <?= $option === $transactionForm['activity_value'] ? 'selected' : '' ?>><?= esc($option) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <?php if ($transaction['type'] === 'masuk'): ?>
            <div class="space-y-2">
                <label class="text-sm font-medium text-zinc-700">Kategori Pemasukan</label>
                <select <?= $isEditMode ? '' : 'disabled' ?> class="h-12 w-full rounded-2xl border border-zinc-100 bg-white px-4 text-sm text-zinc-950 focus:ring-2 focus:ring-lime-400 disabled:text-zinc-950 disabled:opacity-100">
                    <?php foreach ($transactionForm['income_category_options'] as $option): ?>
                        <option <?= $option === $transaction['category'] ? 'selected' : '' ?>><?= esc($option) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="space-y-2">
                <label class="text-sm font-medium text-zinc-700">Masuk ke rekening / dompet</label>
                <select <?= $isEditMode ? '' : 'disabled' ?> class="h-12 w-full rounded-2xl border border-zinc-100 bg-white px-4 text-sm text-zinc-950 focus:ring-2 focus:ring-lime-400 disabled:text-zinc-950 disabled:opacity-100">
                    <?php foreach ($transactionForm['account_options'] as $option): ?>
                        <option <?= $option === $transaction['to_account'] ? 'selected' : '' ?>><?= esc($option) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        <?php endif; ?>

        <?php if ($transaction['type'] === 'biaya'): ?>
            <div class="space-y-2">
                <label class="text-sm font-medium text-zinc-700">Kategori Pengeluaran</label>
                <select <?= $isEditMode ? '' : 'disabled' ?> class="h-12 w-full rounded-2xl border border-zinc-100 bg-white px-4 text-sm text-zinc-950 focus:ring-2 focus:ring-lime-400 disabled:text-zinc-950 disabled:opacity-100">
                    <?php foreach ($transactionForm['expense_category_options'] as $option): ?>
                        <option <?= $option === $transaction['category'] ? 'selected' : '' ?>><?= esc($option) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="space-y-2">
                <label class="text-sm font-medium text-zinc-700">Keluar dari rekening / dompet</label>
                <select <?= $isEditMode ? '' : 'disabled' ?> class="h-12 w-full rounded-2xl border border-zinc-100 bg-white px-4 text-sm text-zinc-950 focus:ring-2 focus:ring-lime-400 disabled:text-zinc-950 disabled:opacity-100">
                    <?php foreach ($transactionForm['account_options'] as $option): ?>
                        <option <?= $option === $transaction['from_account'] ? 'selected' : '' ?>><?= esc($option) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        <?php endif; ?>

        <?php if ($transaction['type'] === 'pindah'): ?>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="space-y-2">
                    <label class="text-sm font-medium text-zinc-700">Dari rekening / dompet</label>
                    <select <?= $isEditMode ? '' : 'disabled' ?> class="h-12 w-full rounded-2xl border border-zinc-100 bg-white px-4 text-sm text-zinc-950 focus:ring-2 focus:ring-lime-400 disabled:text-zinc-950 disabled:opacity-100">
                        <?php foreach ($transactionForm['account_options'] as $option): ?>
                            <option <?= $option === $transaction['from_account'] ? 'selected' : '' ?>><?= esc($option) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium text-zinc-700">Ke rekening / dompet</label>
                    <select <?= $isEditMode ? '' : 'disabled' ?> class="h-12 w-full rounded-2xl border border-zinc-100 bg-white px-4 text-sm text-zinc-950 focus:ring-2 focus:ring-lime-400 disabled:text-zinc-950 disabled:opacity-100">
                        <?php foreach ($transactionForm['account_options'] as $option): ?>
                            <option <?= $option === $transaction['to_account'] ? 'selected' : '' ?>><?= esc($option) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        <?php endif; ?>

        <div class="space-y-2">
            <label class="text-sm font-medium text-zinc-700">Tanggal</label>
            <input type="text" value="<?= esc($transactionForm['date_value']) ?>" <?= $isEditMode ? '' : 'readonly' ?> class="h-12 w-full rounded-2xl border border-zinc-100 bg-white px-4 text-sm text-zinc-950 focus:ring-2 focus:ring-lime-400">
        </div>

        <div class="space-y-2">
            <label class="text-sm font-medium text-zinc-700">Keterangan</label>
            <textarea rows="3" <?= $isEditMode ? '' : 'readonly' ?> class="w-full rounded-2xl border border-zinc-100 bg-white px-4 py-3 text-sm text-zinc-950 focus:ring-2 focus:ring-lime-400"><?= esc($transactionForm['description_value']) ?></textarea>
        </div>

        <div class="space-y-2">
            <label class="text-sm font-medium text-zinc-700">Bukti Transaksi</label>
            <div class="rounded-3xl border border-zinc-200 bg-zinc-50 p-4">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-sm font-semibold text-zinc-950">Preview Bukti</p>
                        <p class="mt-1 text-xs text-zinc-500">Placeholder sementara untuk slip transfer, struk, invoice, atau screenshot mutasi.</p>
                    </div>
                    <span class="rounded-full bg-white px-3 py-2 text-xs font-medium text-zinc-700">Belum ada file</span>
                </div>

                <div class="mt-4 flex min-h-40 items-center justify-center rounded-2xl border border-dashed border-zinc-300 bg-white px-4 py-6 text-center">
                    <div>
                        <span class="material-symbols-rounded text-3xl text-zinc-400" aria-hidden="true">image</span>
                        <p class="mt-3 text-sm font-medium text-zinc-700">Preview bukti transaksi akan tampil di sini</p>
                        <p class="mt-1 text-xs text-zinc-500">Saat ini masih placeholder untuk validasi tampilan detail dan edit.</p>
                    </div>
                </div>

                <?php if ($isEditMode): ?>
                    <div class="mt-4">
                        <label class="inline-flex h-11 cursor-pointer items-center justify-center gap-2 rounded-full border border-zinc-950 bg-white px-5 text-sm font-semibold text-zinc-950">
                            <span class="material-symbols-rounded text-base" aria-hidden="true">upload</span>
                            <span>Upload Bukti</span>
                            <input type="file" class="hidden">
                        </label>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-3 pt-2 sm:grid-cols-2">
            <?php if ($isEditMode): ?>
                <button type="button" class="inline-flex h-14 items-center justify-center gap-2 rounded-full bg-zinc-950 px-6 text-sm font-semibold text-white">
                    <span class="material-symbols-rounded text-base" aria-hidden="true">save</span>
                    Simpan Perubahan
                </button>
                <a href="<?= esc(site_url('transaksi/' . $transaction['id']) . '?from=' . rawurlencode($backUrl)) ?>" class="inline-flex h-14 items-center justify-center rounded-full border border-zinc-100 bg-white px-6 text-sm font-semibold text-zinc-900">
                    Batal
                </a>
            <?php else: ?>
                <a href="<?= esc($editUrl) ?>" class="inline-flex h-14 items-center justify-center gap-2 rounded-full bg-zinc-950 px-6 text-sm font-semibold text-white">
                    <span class="material-symbols-rounded text-base" aria-hidden="true">edit_square</span>
                    Edit Transaksi
                </a>
                <a href="<?= esc($backUrl) ?>" class="inline-flex h-14 items-center justify-center rounded-full border border-zinc-100 bg-white px-6 text-sm font-semibold text-zinc-900">
                    Kembali
                </a>
            <?php endif; ?>
        </div>
    </form>
</div>
<?= $this->endSection() ?>
