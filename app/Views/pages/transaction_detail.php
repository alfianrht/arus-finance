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

    <form action="<?= esc($formAction) ?>" method="post" enctype="multipart/form-data" class="space-y-4">
        <?= csrf_field() ?>
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
                <a href="<?= esc($editUrl) ?>" class="inline-flex h-10 items-center justify-center rounded-full bg-zinc-950 px-4 text-sm font-semibold text-white transition-all active:scale-[0.98] duration-150">Edit</a>
            <?php endif; ?>
        </div>

        <div class="rounded-3xl bg-white border border-zinc-100 p-6 text-center shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-zinc-400">Nominal</p>
            <input type="text" inputmode="numeric" name="amount" value="<?= esc($transactionForm['nominal_value']) ?>" <?= $isEditMode ? '' : 'readonly' ?> class="mt-3 w-full border-0 bg-transparent text-center text-4xl font-semibold tracking-tight text-zinc-950 outline-none tabular-nums placeholder-zinc-300 focus:placeholder-transparent transition-all">
            
            <?php if (in_array($transaction['type_key'], ['keluar', 'honor', 'pindah'], true)): ?>
                <div class="mt-6 flex items-center justify-center gap-2 w-full overflow-hidden">
                    <p class="shrink-0 text-sm font-semibold text-zinc-900">Biaya Admin:</p>
                    <input type="text" inputmode="numeric" name="admin_fee_custom" value="<?= esc($transactionForm['admin_fee_value']) ?>" <?= $isEditMode ? '' : 'readonly' ?> class="h-10 w-28 shrink-0 rounded-xl border border-zinc-200 bg-white px-3 text-center text-sm font-medium text-zinc-950 focus:border-lime-400 focus:outline-none focus:ring-1 focus:ring-lime-400">
                    <input type="hidden" name="admin_fee_preset" value="manual">
                </div>
            <?php endif; ?>
        </div>

        <div class="space-y-2">
            <label class="text-sm font-semibold text-zinc-900">Unit / Program</label>
            <div class="relative">
                <select name="unit_id" <?= $isEditMode ? '' : 'disabled' ?> class="h-12 w-full appearance-none rounded-2xl border border-zinc-100 bg-white pl-4 pr-10 text-sm text-zinc-950 outline-none transition focus:border-zinc-300 focus:ring-2 focus:ring-lime-400 disabled:text-zinc-950 disabled:opacity-100">
                    <?php foreach ($transactionForm['unit_options'] as $option): ?>
                        <option value="<?= esc($option['value']) ?>" <?= $option['selected'] ? 'selected' : '' ?>><?= esc($option['label']) ?></option>
                    <?php endforeach; ?>
                </select>
                <span class="pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 material-symbols-rounded text-[20px] text-zinc-400">expand_more</span>
            </div>
        </div>

        <div class="space-y-2">
            <label class="text-sm font-semibold text-zinc-900">Kegiatan</label>
            <div class="relative">
                <select name="activity_id" <?= $isEditMode ? '' : 'disabled' ?> class="h-12 w-full appearance-none rounded-2xl border border-zinc-100 bg-white pl-4 pr-10 text-sm text-zinc-950 outline-none transition focus:border-zinc-300 focus:ring-2 focus:ring-lime-400 disabled:text-zinc-950 disabled:opacity-100">
                    <?php foreach ($transactionForm['activity_options'] as $option): ?>
                        <option value="<?= esc($option['value']) ?>" <?= $option['selected'] ? 'selected' : '' ?>><?= esc($option['label']) ?></option>
                    <?php endforeach; ?>
                </select>
                <span class="pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 material-symbols-rounded text-[20px] text-zinc-400">expand_more</span>
            </div>
        </div>

        <?= view('partials/project_pocket_field', [
            'projectPocketField' => $transactionForm['project_pocket_field'],
            'fieldName' => 'project_pocket_id',
            'label' => 'Kantong Proyek',
            'placeholder' => 'Pilih kantong proyek',
            'readonly' => ! $isEditMode,
        ]) ?>

        <?php if ($transaction['type_key'] === 'masuk'): ?>
            <div class="space-y-2">
                <label class="text-sm font-semibold text-zinc-900">Kategori Pemasukan</label>
                <div class="relative">
                    <select name="category_id" <?= $isEditMode ? '' : 'disabled' ?> class="h-12 w-full appearance-none rounded-2xl border border-zinc-100 bg-white pl-4 pr-10 text-sm text-zinc-950 outline-none transition focus:border-zinc-300 focus:ring-2 focus:ring-lime-400 disabled:text-zinc-950 disabled:opacity-100">
                        <?php foreach ($transactionForm['income_category_options'] as $option): ?>
                            <option value="<?= esc($option['value']) ?>" <?= $option['selected'] ? 'selected' : '' ?>><?= esc($option['label']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <span class="pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 material-symbols-rounded text-[20px] text-zinc-400">expand_more</span>
                </div>
            </div>

            <div class="space-y-2">
                <label class="text-sm font-semibold text-zinc-900">Masuk ke rekening / dompet</label>
                <div class="relative">
                    <select name="to_account_id" <?= $isEditMode ? '' : 'disabled' ?> class="h-12 w-full appearance-none rounded-2xl border border-zinc-100 bg-white pl-4 pr-10 text-sm text-zinc-950 outline-none transition focus:border-zinc-300 focus:ring-2 focus:ring-lime-400 disabled:text-zinc-950 disabled:opacity-100">
                        <?php foreach ($transactionForm['to_account_options'] as $option): ?>
                            <option value="<?= esc($option['value']) ?>" <?= $option['selected'] ? 'selected' : '' ?>><?= esc($option['label']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <span class="pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 material-symbols-rounded text-[20px] text-zinc-400">expand_more</span>
                </div>
            </div>
        <?php endif; ?>

        <?php if (in_array($transaction['type_key'], ['keluar', 'honor'], true)): ?>
            <div class="space-y-2">
                <label class="text-sm font-semibold text-zinc-900"><?= $transaction['type_key'] === 'honor' ? 'Kategori Honor' : 'Kategori Pengeluaran' ?></label>
                <div class="relative">
                    <select name="category_id" <?= $isEditMode ? '' : 'disabled' ?> class="h-12 w-full appearance-none rounded-2xl border border-zinc-100 bg-white pl-4 pr-10 text-sm text-zinc-950 outline-none transition focus:border-zinc-300 focus:ring-2 focus:ring-lime-400 disabled:text-zinc-950 disabled:opacity-100">
                        <?php foreach ($transactionForm['expense_category_options'] as $option): ?>
                            <option value="<?= esc($option['value']) ?>" <?= $option['selected'] ? 'selected' : '' ?>><?= esc($option['label']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <span class="pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 material-symbols-rounded text-[20px] text-zinc-400">expand_more</span>
                </div>
            </div>

            <div class="space-y-2">
                <label class="text-sm font-semibold text-zinc-900">Keluar dari rekening / dompet</label>
                <div class="relative">
                    <select name="from_account_id" <?= $isEditMode ? '' : 'disabled' ?> class="h-12 w-full appearance-none rounded-2xl border border-zinc-100 bg-white pl-4 pr-10 text-sm text-zinc-950 outline-none transition focus:border-zinc-300 focus:ring-2 focus:ring-lime-400 disabled:text-zinc-950 disabled:opacity-100">
                        <?php foreach ($transactionForm['account_options'] as $option): ?>
                            <option value="<?= esc($option['value']) ?>" <?= $option['selected'] ? 'selected' : '' ?>><?= esc($option['label']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <span class="pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 material-symbols-rounded text-[20px] text-zinc-400">expand_more</span>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($transaction['type_key'] === 'honor'): ?>
            <div class="space-y-2">
                <label class="text-sm font-semibold text-zinc-900">Penerima</label>
                <div class="relative">
                    <select name="receiver_id" <?= $isEditMode ? '' : 'disabled' ?> class="h-12 w-full appearance-none rounded-2xl border border-zinc-100 bg-white pl-4 pr-10 text-sm text-zinc-950 outline-none transition focus:border-zinc-300 focus:ring-2 focus:ring-lime-400 disabled:text-zinc-950 disabled:opacity-100">
                        <?php foreach ($transactionForm['receiver_options'] as $option): ?>
                            <option value="<?= esc($option['value']) ?>" <?= $option['selected'] ? 'selected' : '' ?>><?= esc($option['label']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <span class="pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 material-symbols-rounded text-[20px] text-zinc-400">expand_more</span>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($transaction['type_key'] === 'pindah'): ?>
            <?= view('partials/project_pocket_field', [
                'projectPocketField' => $transactionForm['project_pocket_field'],
                'fieldName' => 'counter_project_pocket_id',
                'label' => 'Ke Kantong',
                'placeholder' => 'Pilih kantong tujuan',
                'readonly' => ! $isEditMode,
                'mode' => 'execution',
            ]) ?>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="space-y-2">
                    <label class="text-sm font-semibold text-zinc-900">Dari rekening / dompet</label>
                    <div class="relative">
                        <select name="from_account_id" <?= $isEditMode ? '' : 'disabled' ?> class="h-12 w-full appearance-none rounded-2xl border border-zinc-100 bg-white pl-4 pr-10 text-sm text-zinc-950 outline-none transition focus:border-zinc-300 focus:ring-2 focus:ring-lime-400 disabled:text-zinc-950 disabled:opacity-100">
                            <?php foreach ($transactionForm['account_options'] as $option): ?>
                                <option value="<?= esc($option['value']) ?>" <?= $option['selected'] ? 'selected' : '' ?>><?= esc($option['label']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <span class="pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 material-symbols-rounded text-[20px] text-zinc-400">expand_more</span>
                    </div>
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-semibold text-zinc-900">Ke rekening / dompet</label>
                    <div class="relative">
                        <select name="to_account_id" <?= $isEditMode ? '' : 'disabled' ?> class="h-12 w-full appearance-none rounded-2xl border border-zinc-100 bg-white pl-4 pr-10 text-sm text-zinc-950 outline-none transition focus:border-zinc-300 focus:ring-2 focus:ring-lime-400 disabled:text-zinc-950 disabled:opacity-100">
                            <?php foreach ($transactionForm['to_account_options'] as $option): ?>
                                <option value="<?= esc($option['value']) ?>" <?= $option['selected'] ? 'selected' : '' ?>><?= esc($option['label']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <span class="pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 material-symbols-rounded text-[20px] text-zinc-400">expand_more</span>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="space-y-2">
            <label class="text-sm font-semibold text-zinc-900">Tanggal</label>
            <input type="<?= $isEditMode ? 'date' : 'text' ?>" name="transaction_date" value="<?= esc($transactionForm['date_value']) ?>" <?= $isEditMode ? '' : 'readonly' ?> class="h-12 w-full rounded-2xl border border-zinc-100 bg-white px-4 text-sm text-zinc-950 outline-none transition focus:border-zinc-300 focus:ring-2 focus:ring-lime-400">
        </div>

        <div class="space-y-2">
            <label class="text-sm font-semibold text-zinc-900">Keterangan</label>
            <textarea name="notes" rows="3" <?= $isEditMode ? '' : 'readonly' ?> class="w-full rounded-2xl border border-zinc-100 bg-white px-4 py-3 text-sm text-zinc-950 outline-none transition focus:border-zinc-300 focus:ring-2 focus:ring-lime-400"><?= esc($transactionForm['description_value']) ?></textarea>
        </div>

        <div class="space-y-2">
            <label class="text-sm font-semibold text-zinc-900">Bukti Transaksi</label>
            <div class="relative rounded-3xl border border-zinc-200 bg-zinc-50 p-4">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-sm font-semibold text-zinc-950">Preview Bukti</p>
                        <p class="mt-1 text-xs text-zinc-500">Slip transfer, struk, invoice, atau screenshot mutasi akan ditampilkan di sini.</p>
                    </div>
                    <span class="absolute right-2 top-2 rounded-full bg-white px-3 py-2 text-xs font-medium text-zinc-700"><?= !empty($transaction['proof_image']) ? 'File tersedia' : 'Belum ada file' ?></span>
                </div>
                <?php $proofPath = trim((string) ($transaction['proof_image'] ?? '')); ?>
                <?php $proofUrl = $proofPath !== '' ? base_url($proofPath) : ''; ?>
                <?php $proofExt = $proofPath !== '' ? strtolower((string) pathinfo($proofPath, PATHINFO_EXTENSION)) : ''; ?>
                <?php $isImageProof = in_array($proofExt, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true); ?>
                <?php if ($proofPath !== '' && $isImageProof): ?>
                    <div class="mt-4 overflow-hidden rounded-2xl border border-zinc-200 bg-white">
                        <img src="<?= esc($proofUrl) ?>" alt="Bukti transaksi" data-image-preview data-image-preview-alt="Bukti transaksi" class="h-64 w-full cursor-zoom-in object-cover">
                    </div>
                <?php elseif ($proofPath !== ''): ?>
                    <div class="mt-4 rounded-2xl border border-dashed border-zinc-300 bg-white px-4 py-6 text-center">
                        <span class="material-symbols-rounded text-3xl text-zinc-400" aria-hidden="true">description</span>
                        <p class="mt-3 text-sm font-medium text-zinc-700">File bukti tersimpan</p>
                        <a href="<?= esc($proofUrl) ?>" target="_blank" rel="noopener noreferrer" class="mt-2 inline-flex h-10 items-center justify-center rounded-full border border-zinc-950 px-4 text-sm font-semibold text-zinc-950 transition-all active:scale-[0.98] duration-150 shadow-sm">Buka File</a>
                    </div>
                <?php else: ?>
                    <div class="mt-4 flex min-h-40 items-center justify-center rounded-2xl border border-dashed border-zinc-300 bg-white px-4 py-6 text-center">
                        <div>
                            <span class="material-symbols-rounded text-3xl text-zinc-400" aria-hidden="true">image</span>
                            <p class="mt-3 text-sm font-medium text-zinc-700">Belum ada bukti transaksi</p>
                            <p class="mt-1 text-xs text-zinc-500">Upload akan tersedia saat mode edit aktif.</p>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($isEditMode): ?>
                    <div class="mt-4">
                        <label class="inline-flex h-11 cursor-pointer items-center justify-center gap-2 rounded-full border border-zinc-950 bg-white px-5 text-sm font-semibold text-zinc-950 transition-all active:scale-[0.98] duration-150 shadow-sm">
                            <span class="material-symbols-rounded text-base" aria-hidden="true">upload</span>
                            <span>Upload Bukti</span>
                            <input id="transactionProofFileInput" type="file" name="proof_file" accept="image/*,.pdf" class="hidden">
                        </label>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-3 pt-2 sm:grid-cols-2">
            <?php if ($isEditMode): ?>
                <button type="submit" class="inline-flex h-14 items-center justify-center gap-2 rounded-full bg-zinc-950 px-6 text-sm font-semibold text-white transition-all active:scale-[0.98] duration-150 shadow-sm">
                    <span class="material-symbols-rounded text-base" aria-hidden="true">save</span>
                    Simpan Perubahan
                </button>
                <a href="<?= esc(site_url('transaksi/' . $transaction['id']) . '?from=' . rawurlencode($backUrl)) ?>" class="inline-flex h-14 items-center justify-center rounded-full border border-zinc-100 bg-white px-6 text-sm font-semibold text-zinc-900 transition-all active:scale-[0.98] duration-150 shadow-sm">
                    Batal
                </a>
            <?php else: ?>
                <a href="<?= esc($editUrl) ?>" class="inline-flex h-14 items-center justify-center gap-2 rounded-full bg-zinc-950 px-6 text-sm font-semibold text-white transition-all active:scale-[0.98] duration-150 shadow-sm">
                    <span class="material-symbols-rounded text-base" aria-hidden="true">edit_square</span>
                    Edit Transaksi
                </a>
                <a href="<?= esc($backUrl) ?>" class="inline-flex h-14 items-center justify-center rounded-full border border-zinc-100 bg-white px-6 text-sm font-semibold text-zinc-900 transition-all active:scale-[0.98] duration-150 shadow-sm">
                    Kembali
                </a>
            <?php endif; ?>
        </div>
    </form>
</div>
<?php if ($isEditMode): ?>
<script>
    (function () {
        var input = document.getElementById('transactionProofFileInput');
        if (!input) {
            return;
        }

        input.addEventListener('change', function () {
            var file = input.files && input.files[0] ? input.files[0] : null;
            if (!file || !file.type.startsWith('image/')) {
                return;
            }

            var preview = document.querySelector('img[data-image-preview][alt="Bukti transaksi"]');
            if (!preview) {
                var emptyState = input.closest('.space-y-2').querySelector('.mt-4.flex.min-h-40');
                if (emptyState) {
                    emptyState.outerHTML = '<div class="mt-4 overflow-hidden rounded-2xl border border-zinc-200 bg-white"><img alt="Bukti transaksi" data-image-preview data-image-preview-alt="Bukti transaksi" class="h-64 w-full cursor-zoom-in object-cover"></div>';
                    preview = document.querySelector('img[data-image-preview][alt="Bukti transaksi"]');
                }
            }

            if (!preview) {
                return;
            }

            preview.src = URL.createObjectURL(file);
            preview.setAttribute('data-image-preview-src', preview.src);
        });
    })();
</script>
<?php endif; ?>
<?= $this->endSection() ?>
