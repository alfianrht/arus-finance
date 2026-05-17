<?php
$captureKey = $captureKey ?? 'bukti';
$autoFields = $autoFields ?? [];
$title = $title ?? 'Foto bukti dulu';
$description = $description ?? 'Nanti AI akan membaca bukti transaksi lalu membantu mengisi form ini secara otomatis.';
$previewTitle = $previewTitle ?? 'Belum ada bukti dipilih';
$previewDescription = $previewDescription ?? 'Gunakan kamera HP untuk hasil paling cepat, atau unggah file yang sudah ada.';
$noteLabel = $noteLabel ?? 'Mode cepat untuk HP';
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
            <input type="file" accept="image/*" capture="environment" class="sr-only" name="<?= esc($captureKey) ?>_camera">
        </label>

        <label class="inline-flex h-14 cursor-pointer items-center justify-center gap-2 rounded-full border border-zinc-950 bg-white px-5 text-sm font-semibold text-zinc-950">
            <span class="material-symbols-rounded text-base" aria-hidden="true">upload_file</span>
            <span>Upload File</span>
            <input type="file" accept="image/*,.pdf" class="sr-only" name="<?= esc($captureKey) ?>_upload">
        </label>
    </div>

    <div class="mt-4 rounded-3xl border border-dashed border-zinc-300 bg-zinc-50 p-4">
        <div class="flex items-start gap-3">
            <div class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-white text-zinc-700 shadow-sm">
                <span class="material-symbols-rounded text-base" aria-hidden="true">document_scanner</span>
            </div>
            <div class="min-w-0">
                <p class="text-sm font-semibold text-zinc-950"><?= esc($previewTitle) ?></p>
                <p class="mt-1 text-sm text-zinc-500"><?= esc($previewDescription) ?></p>
            </div>
        </div>
    </div>
</section>
