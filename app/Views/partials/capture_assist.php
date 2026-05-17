<?php
$captureKey = $captureKey ?? 'bukti';
$autoFields = $autoFields ?? [];
$title = $title ?? 'Foto bukti dulu';
$description = $description ?? 'Nanti AI akan membaca bukti transaksi lalu membantu mengisi form ini secara otomatis.';
$previewTitle = $previewTitle ?? 'Belum ada bukti dipilih';
$previewDescription = $previewDescription ?? 'Gunakan kamera HP untuk hasil paling cepat, atau unggah file yang sudah ada.';
$noteLabel = $noteLabel ?? 'Mode cepat untuk HP';
$previewId = 'proof-preview-' . preg_replace('/[^a-z0-9_-]/i', '-', (string) $captureKey);
$imageId = $previewId . '-image';
$nameId = $previewId . '-name';
$descId = $previewId . '-desc';
?>

<section class="relative rounded-3xl border border-zinc-950 bg-white p-4 shadow-sm">
    <div class="flex items-start justify-between gap-4">
        <div>
            <p class="text-xs font-medium uppercase tracking-wide text-zinc-500">Bukti Transaksi</p>
            <p class="mt-2 text-lg font-semibold tracking-tight text-zinc-950"><?= esc($title) ?></p>
            <p class="mt-1 text-sm text-zinc-500"><?= esc($description) ?></p>
        </div>
        <span class="absolute top-2 right-2 rounded-full bg-lime-100 px-3 py-2 text-xs font-medium text-lime-950"><?= esc($noteLabel) ?></span>
    </div>

    <?php if ($autoFields !== []): ?>
        <div class="mt-4 flex flex-wrap gap-2">
            <?php foreach ($autoFields as $field): ?>
                <span class="rounded-full bg-zinc-100 px-3 py-2 text-xs font-medium text-zinc-700"><?= esc($field) ?></span>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
        <label class="inline-flex h-14 cursor-pointer items-center justify-center gap-2 rounded-full bg-lime-400 px-5 text-sm font-semibold text-zinc-950">
            <span class="material-symbols-rounded text-base" aria-hidden="true">photo_camera</span>
            <span>Buka Kamera</span>
            <input type="file" accept="image/*" capture="environment" class="sr-only" name="proof_camera" onchange="window.ArusProofPreview && window.ArusProofPreview(this, '<?= esc($previewId) ?>')">
        </label>

        <label class="inline-flex h-14 cursor-pointer items-center justify-center gap-2 rounded-full border border-zinc-950 bg-white px-5 text-sm font-semibold text-zinc-950">
            <span class="material-symbols-rounded text-base" aria-hidden="true">upload_file</span>
            <span>Upload File</span>
            <input type="file" accept="image/*,.pdf" class="sr-only" name="proof_upload" onchange="window.ArusProofPreview && window.ArusProofPreview(this, '<?= esc($previewId) ?>')">
        </label>
    </div>

    <div id="<?= esc($previewId) ?>" class="mt-4 rounded-3xl border border-dashed border-zinc-300 bg-zinc-50 p-4">
        <div class="flex items-start gap-3">
            <div class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-white text-zinc-700 shadow-sm">
                <span class="material-symbols-rounded text-base" aria-hidden="true">document_scanner</span>
            </div>
            <div class="min-w-0">
                <p id="<?= esc($nameId) ?>" class="text-sm font-semibold text-zinc-950"><?= esc($previewTitle) ?></p>
                <p id="<?= esc($descId) ?>" class="mt-1 text-sm text-zinc-500"><?= esc($previewDescription) ?></p>
            </div>
        </div>

        <div class="mt-4 hidden overflow-hidden rounded-2xl border border-zinc-200 bg-white" data-proof-image-wrap>
            <img id="<?= esc($imageId) ?>" src="" alt="Preview bukti transaksi" class="h-56 w-full object-cover">
        </div>
    </div>
</section>

<script>
window.ArusProofPreview = window.ArusProofPreview || function (input, previewId) {
    const file = input && input.files ? input.files[0] : null;
    if (!file) return;

    const root = document.getElementById(previewId);
    if (!root) return;

    const nameEl = root.querySelector('#' + CSS.escape(previewId + '-name'));
    const descEl = root.querySelector('#' + CSS.escape(previewId + '-desc'));
    const imgWrap = root.querySelector('[data-proof-image-wrap]');
    const imgEl = root.querySelector('#' + CSS.escape(previewId + '-image'));

    if (nameEl) nameEl.textContent = file.name;
    if (descEl) {
        descEl.textContent = file.type.startsWith('image/')
            ? 'Preview lokal siap. File akan tersimpan saat form dikirim.'
            : 'File siap diunggah. Preview gambar tidak tersedia untuk format ini.';
    }

    if (file.type.startsWith('image/') && imgWrap && imgEl) {
        imgEl.src = URL.createObjectURL(file);
        imgWrap.classList.remove('hidden');
    } else if (imgWrap && imgEl) {
        imgEl.src = '';
        imgWrap.classList.add('hidden');
    }
};
</script>
