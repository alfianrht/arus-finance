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
$emptyId = $previewId . '-empty';
$nameId = $previewId . '-name';
$descId = $previewId . '-desc';
$statusId = $previewId . '-status';
$messageId = $previewId . '-message';
$summaryId = $previewId . '-summary';
$loadingId = $previewId . '-loading';
$scanEndpoint = $scanEndpoint ?? null;
$scanEnabled = is_string($scanEndpoint) && trim($scanEndpoint) !== '';
$scanMode = $scanMode ?? 'expense';
$cameraAccept = $cameraAccept ?? 'image/*';
$uploadAccept = $uploadAccept ?? 'image/*,.pdf';
$scanOptionsJson = $scanEnabled
    ? htmlspecialchars((string) json_encode(['scanEndpoint' => $scanEndpoint, 'scanMode' => $scanMode]), ENT_QUOTES, 'UTF-8')
    : 'null';
?>

<section class="rounded-3xl border border-zinc-200 bg-white p-3 shadow-sm">
    <div class="flex items-center justify-end">
        <span id="<?= esc($statusId) ?>" class="shrink-0 rounded-full bg-lime-100 px-2.5 py-1 text-xs font-medium text-lime-950"><?= esc($noteLabel) ?></span>
    </div>

    <div class="mt-2 grid grid-cols-1 gap-2 sm:grid-cols-2">
        <label data-scan-trigger class="inline-flex h-12 cursor-pointer items-center justify-center gap-2 rounded-full border border-zinc-950 bg-white px-4 text-sm font-semibold text-zinc-950">
            <span class="material-symbols-rounded text-base" aria-hidden="true">photo_camera</span>
            <span>Buka Kamera</span>
            <input
                type="file"
                accept="<?= esc($cameraAccept) ?>"
                capture="environment"
                class="sr-only"
                name="proof_camera"
                onchange="window.ArusProofPreview && window.ArusProofPreview(this, '<?= esc($previewId) ?>', <?= $scanOptionsJson ?>)"
            >
        </label>

        <label data-scan-trigger class="inline-flex h-12 cursor-pointer items-center justify-center gap-2 rounded-full bg-lime-400 px-4 text-sm font-semibold text-zinc-950">
            <span class="material-symbols-rounded text-base" aria-hidden="true">upload_file</span>
            <span>Upload File</span>
            <input
                type="file"
                accept="<?= esc($uploadAccept) ?>"
                class="sr-only"
                name="proof_upload"
                onchange="window.ArusProofPreview && window.ArusProofPreview(this, '<?= esc($previewId) ?>', <?= $scanOptionsJson ?>)"
            >
        </label>
    </div>

    <div id="<?= esc($previewId) ?>" class="mt-2 rounded-2xl border border-dashed border-zinc-300 bg-zinc-50 p-2">
        <div id="<?= esc($emptyId) ?>" class="flex h-28 flex-col items-center justify-center rounded-xl bg-white text-zinc-400">
            <span class="material-symbols-rounded text-2xl" aria-hidden="true">receipt_long</span>
            <p class="mt-1 text-xs">Belum ada struk</p>
        </div>

        <p id="<?= esc($nameId) ?>" class="mt-2 hidden px-1 text-xs font-medium text-zinc-700"><?= esc($previewTitle) ?></p>
        <p id="<?= esc($descId) ?>" class="hidden px-1 pt-0.5 text-[11px] text-zinc-500"><?= esc($previewDescription) ?></p>

        <div class="relative mt-2 hidden overflow-hidden rounded-xl border border-zinc-200 bg-white" data-proof-image-wrap>
            <img id="<?= esc($imageId) ?>" src="" alt="Preview bukti transaksi" data-image-preview data-image-preview-alt="Preview bukti transaksi" class="h-40 w-full cursor-zoom-in object-cover">
            <div id="<?= esc($loadingId) ?>" class="absolute inset-0 hidden items-center justify-center bg-black/45">
                <div class="inline-flex items-center gap-2 rounded-full bg-white/95 px-3 py-1.5 text-xs font-medium text-zinc-800">
                    <span class="material-symbols-rounded animate-spin text-base" aria-hidden="true">progress_activity</span>
                    Membaca nota...
                </div>
            </div>
        </div>
    </div>

    <?php if ($scanEnabled): ?>
        <div class="mt-2 rounded-2xl border border-zinc-200 bg-zinc-50 px-3 py-2">
            <div class="flex items-center gap-2">
                <span class="material-symbols-rounded text-base text-zinc-500" aria-hidden="true">auto_awesome</span>
                <p id="<?= esc($messageId) ?>" class="text-sm font-medium text-zinc-700">Status AI siap.</p>
            </div>
            <p id="<?= esc($summaryId) ?>" class="mt-1 text-xs text-zinc-500">Tetap periksa kembali sebelum menyimpan.</p>
        </div>
    <?php endif; ?>
</section>

<script>
window.ArusBillScan = window.ArusBillScan || async function (input, previewId, scanOptions) {
    if (!scanOptions || !scanOptions.scanEndpoint) return;

    const file = input && input.files ? input.files[0] : null;
    const form = input ? input.closest('form') : null;
    if (!file || !form) return;

    const root = document.getElementById(previewId);
    const statusEl = document.getElementById(previewId + '-status');
    const messageEl = document.getElementById(previewId + '-message');
    const summaryEl = document.getElementById(previewId + '-summary');
    const loadingEl = document.getElementById(previewId + '-loading');
    const triggers = root ? root.parentElement.querySelectorAll('[data-scan-trigger]') : [];

    const csrfInput = form.querySelector('input[name="<?= esc(csrf_token()) ?>"]');
    const body = new FormData();
    body.append('bill_image', file);
    body.append('mode', scanOptions.scanMode || 'expense');
    if (csrfInput) {
        body.append(csrfInput.name, csrfInput.value);
    }

    triggers.forEach(function (trigger) {
        trigger.classList.add('pointer-events-none', 'opacity-60');
    });

    if (statusEl) {
        statusEl.textContent = 'Membaca nota...';
        statusEl.className = 'shrink-0 rounded-full bg-zinc-100 px-2.5 py-1 text-xs font-medium text-zinc-700';
    }
    if (messageEl) {
        messageEl.textContent = 'AI sedang membaca nota...';
    }
    if (summaryEl) {
        summaryEl.textContent = 'Mohon tunggu sebentar.';
    }
    if (loadingEl) {
        loadingEl.classList.remove('hidden');
        loadingEl.classList.add('flex');
    }

    try {
        const response = await fetch(scanOptions.scanEndpoint, {
            method: 'POST',
            body,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        const payload = await response.json();

        if (payload && payload.csrf && csrfInput && payload.csrf.token === csrfInput.name) {
            csrfInput.value = payload.csrf.hash;
        }

        if (!response.ok || !payload.success || !payload.data) {
            throw new Error((payload && payload.message) ? payload.message : 'Scan gagal');
        }

        window.ArusApplyBillScan && window.ArusApplyBillScan(form, payload.data);

        const needsReview = !!payload.data.needs_review || (payload.data.confidence || 0) < 0.85;
        if (statusEl) {
            statusEl.textContent = needsReview ? 'Perlu dicek' : 'Auto-fill AI';
            statusEl.className = needsReview
                ? 'shrink-0 rounded-full bg-amber-100 px-2.5 py-1 text-xs font-medium text-amber-900'
                : 'shrink-0 rounded-full bg-lime-100 px-2.5 py-1 text-xs font-medium text-lime-950';
        }
        if (messageEl) {
            messageEl.textContent = 'Scan berhasil.';
        }
        if (summaryEl) {
            summaryEl.textContent = payload.data.raw_summary || 'Form berhasil dibantu isi oleh AI.';
        }
    } catch (error) {
        if (statusEl) {
            statusEl.textContent = 'Perlu dicek';
            statusEl.className = 'shrink-0 rounded-full bg-amber-100 px-2.5 py-1 text-xs font-medium text-amber-900';
        }
        if (messageEl) {
            messageEl.textContent = 'Bukti belum bisa dibaca.';
        }
        if (summaryEl) {
            summaryEl.textContent = 'Silakan unggah ulang dengan foto lebih jelas.';
        }
    } finally {
        if (loadingEl) {
            loadingEl.classList.add('hidden');
            loadingEl.classList.remove('flex');
        }
        triggers.forEach(function (trigger) {
            trigger.classList.remove('pointer-events-none', 'opacity-60');
        });
    }
};

window.ArusProofPreview = window.ArusProofPreview || function (input, previewId, scanOptions) {
    const file = input && input.files ? input.files[0] : null;
    if (!file) return;

    const root = document.getElementById(previewId);
    if (!root) return;

    const nameEl = root.querySelector('#' + CSS.escape(previewId + '-name'));
    const descEl = root.querySelector('#' + CSS.escape(previewId + '-desc'));
    const emptyEl = root.querySelector('#' + CSS.escape(previewId + '-empty'));
    const imgWrap = root.querySelector('[data-proof-image-wrap]');
    const imgEl = root.querySelector('#' + CSS.escape(previewId + '-image'));

    if (nameEl) {
        nameEl.textContent = file.name;
        nameEl.classList.remove('hidden');
    }
    if (descEl) {
        descEl.textContent = file.type.startsWith('image/')
            ? 'Preview siap.'
            : 'File siap diunggah.';
        descEl.classList.remove('hidden');
    }
    if (emptyEl) {
        emptyEl.classList.add('hidden');
    }

    if (file.type.startsWith('image/') && imgWrap && imgEl) {
        imgEl.src = URL.createObjectURL(file);
        imgWrap.classList.remove('hidden');
    } else if (imgWrap && imgEl) {
        imgEl.src = '';
        imgWrap.classList.add('hidden');
    }

    if (scanOptions && scanOptions.scanEndpoint) {
        window.ArusBillScan && window.ArusBillScan(input, previewId, scanOptions);
    }
};
</script>
