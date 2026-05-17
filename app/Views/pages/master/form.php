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
        <form class="space-y-4" action="#" method="get">
            <?php foreach ($formFields as $field): ?>
                <div class="space-y-2">
                    <label class="text-sm font-medium text-zinc-700"><?= esc($field['label']) ?></label>

                    <?php if (($field['type'] ?? 'text') === 'select'): ?>
                        <select class="h-12 w-full rounded-2xl border border-zinc-200 bg-white px-4 text-sm text-zinc-950 focus:border-zinc-950 focus:outline-none">
                            <?php foreach (($field['options'] ?? []) as $option): ?>
                                <option value="<?= esc($option) ?>" <?= ($field['value'] ?? null) === $option ? 'selected' : '' ?>><?= esc($option) ?></option>
                            <?php endforeach; ?>
                        </select>
                    <?php elseif (($field['type'] ?? 'text') === 'textarea'): ?>
                        <textarea rows="4" class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-950 focus:border-zinc-950 focus:outline-none"><?= esc($field['value'] ?? '') ?></textarea>
                    <?php elseif (($field['type'] ?? 'text') === 'file'): ?>
                        <label class="flex min-h-28 cursor-pointer items-center justify-center rounded-2xl border border-dashed border-zinc-300 bg-zinc-50 px-4 py-5 text-center">
                            <span class="text-sm text-zinc-500"><?= esc($field['value'] ?? 'Pilih file') ?></span>
                            <input type="file" class="hidden">
                        </label>
                    <?php else: ?>
                        <input
                            type="<?= esc($field['type'] ?? 'text') ?>"
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
                <button type="button" class="inline-flex h-12 items-center justify-center rounded-full bg-zinc-950 px-5 text-sm font-semibold text-white">
                    <?= esc($saveLabel ?? 'Simpan Dummy') ?>
                </button>
            </div>
        </form>
    </section>
</div>
<?= $this->endSection() ?>
