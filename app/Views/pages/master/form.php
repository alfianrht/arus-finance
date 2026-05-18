<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<div class="space-y-3">
    <?= view('partials/top_nav_back', [
        'title' => $formTitle ?? $pageTitle,
        'subtitle' => $formMode ?? 'Form Dummy',
        'backUrl' => $backUrl,
    ]) ?>

    <section class="rounded-3xl border border-zinc-950 bg-white p-5">
        <p class="text-xs font-medium uppercase tracking-wide text-zinc-500">Prototype Input</p>
        <p class="mt-3 text-lg font-semibold tracking-tight text-zinc-950"><?= esc($formDescription ?? 'Form ini dipakai untuk memvalidasi struktur input sebelum backend disiapkan.') ?></p>
    </section>

    <section class="rounded-3xl bg-white p-4 shadow-sm">
        <?php
        $hasFileField = false;
        foreach ($formFields as $f) { if (($f['type'] ?? 'text') === 'file') { $hasFileField = true; break; } }
        ?>
        <form class="space-y-4" action="<?= esc($formAction ?? '#') ?>" method="<?= esc($formMethod ?? 'get') ?>"<?= $hasFileField ? ' enctype="multipart/form-data"' : '' ?>>
            <?php if (($formMethod ?? 'get') === 'post'): ?>
                <?= csrf_field() ?>
            <?php endif; ?>
            <?php foreach ($formFields as $field): ?>
                <div class="space-y-2">
                    <label class="text-sm font-medium text-zinc-700"><?= esc($field['label']) ?></label>

                    <?php if (($field['type'] ?? 'text') === 'select'): ?>
                        <select name="<?= esc($field['name'] ?? '') ?>" class="h-12 w-full rounded-2xl border border-zinc-200 bg-white px-4 text-sm text-zinc-950 focus:border-zinc-950 focus:outline-none">
                            <?php foreach (($field['options'] ?? []) as $option): ?>
                                <?php
                                $optionValue = is_array($option) ? (string) ($option['value'] ?? '') : (string) $option;
                                $optionLabel = is_array($option) ? (string) ($option['label'] ?? $optionValue) : (string) $option;
                                $selectedValue = (string) ($field['value'] ?? '');
                                ?>
                                <option value="<?= esc($optionValue) ?>" <?= $selectedValue === $optionValue ? 'selected' : '' ?>><?= esc($optionLabel) ?></option>
                            <?php endforeach; ?>
                        </select>
                    <?php elseif (($field['type'] ?? 'text') === 'textarea'): ?>
                        <textarea name="<?= esc($field['name'] ?? '') ?>" rows="4" class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-950 focus:border-zinc-950 focus:outline-none"><?= esc($field['value'] ?? '') ?></textarea>
                    <?php elseif (($field['type'] ?? 'text') === 'file'): ?>
                        <?php $previewSrc = !empty($field['value']) ? base_url($field['value']) : ''; ?>
                        <label class="group flex min-h-28 cursor-pointer flex-col items-center justify-center gap-3 rounded-2xl border border-dashed border-zinc-300 bg-zinc-50 px-4 py-5 text-center transition hover:border-zinc-400 hover:bg-zinc-100">
                            <img
                                id="preview_<?= esc($field['name'] ?? 'file') ?>"
                                src="<?= esc($previewSrc) ?>"
                                alt="Preview"
                                data-image-preview
                                data-image-preview-alt="Preview gambar"
                                class="h-16 w-16 rounded-xl object-contain <?= $previewSrc ? 'cursor-zoom-in' : 'hidden' ?>"
                            >
                            <span class="text-sm text-zinc-500">
                                <?= $previewSrc ? 'Klik untuk ganti gambar' : 'Klik untuk pilih gambar logo' ?>
                            </span>
                            <input
                                type="file"
                                name="<?= esc($field['name'] ?? '') ?>"
                                accept="image/*"
                                class="hidden"
                                onchange="previewImage(this, 'preview_<?= esc($field['name'] ?? 'file') ?>')"
                            >
                        </label>
                        <?php if ($previewSrc): ?>
                            <p class="px-1 text-xs text-zinc-400"><?= esc($field['value']) ?></p>
                        <?php endif; ?>
                    <?php else: ?>
                        <input
                            type="<?= esc($field['type'] ?? 'text') ?>"
                            name="<?= esc($field['name'] ?? '') ?>"
                            value="<?= esc($field['value'] ?? '') ?>"
                            class="h-12 w-full rounded-2xl border border-zinc-200 bg-white px-4 text-sm text-zinc-950 focus:border-zinc-950 focus:outline-none"
                        >
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>

            <div class="flex items-center gap-3 pt-2">
                <a href="<?= esc($backUrl) ?>" class="inline-flex h-12 items-center justify-center rounded-full border border-zinc-950 px-5 text-sm font-semibold text-zinc-950">
                    Batal
                </a>
                <button type="submit" class="inline-flex h-12 items-center justify-center rounded-full bg-zinc-950 px-5 text-sm font-semibold text-white">
                    <?= esc($saveLabel ?? 'Simpan Dummy') ?>
                </button>
            </div>
        </form>
    </section>
</div>

<?php if ($hasFileField): ?>
<script>
function previewImage(input, previewId) {
    const preview = document.getElementById(previewId);
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.classList.remove('hidden');
            preview.classList.add('cursor-zoom-in');
            preview.setAttribute('data-image-preview', '');
            preview.setAttribute('data-image-preview-src', e.target.result);
            input.closest('label').querySelector('span').textContent = input.files[0].name;
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
<?php endif; ?>
<?= $this->endSection() ?>
