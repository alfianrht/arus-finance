<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<div class="space-y-3">
    <?= view('partials/top_nav_back', [
        'title' => 'Profil Lembaga',
        'subtitle' => 'Master Data',
        'backUrl' => $backUrl,
    ]) ?>

    <div class="flex justify-end">
        <a href="<?= esc($editUrl) ?>" class="inline-flex h-11 items-center justify-center gap-2 rounded-full bg-zinc-950 px-5 text-sm font-semibold text-white">
            <span class="material-symbols-rounded text-base" aria-hidden="true">edit</span>
            <span>Edit Profil</span>
        </a>
    </div>

    <section class="rounded-3xl border border-zinc-950 bg-white p-5">
        <p class="text-xs font-medium uppercase tracking-wide text-zinc-500">Identitas Utama</p>
        <p class="mt-3 text-lg font-semibold text-zinc-950"><?= esc($institutionName) ?></p>
        <p class="mt-1 text-sm text-zinc-500">Dipakai sebagai identitas global aplikasi, header, dan dokumen ringkas yang keluar dari Arus.</p>
        <div class="mt-4 grid grid-cols-2 gap-3">
            <?php foreach ($profileSections as $item): ?>
                <div class="rounded-2xl bg-zinc-50 p-3">
                    <p class="text-xs text-zinc-500"><?= esc($item['label']) ?></p>
                    <p class="mt-2 text-sm font-semibold text-zinc-950"><?= esc($item['value']) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="rounded-3xl bg-white p-4 shadow-sm">
        <div class="flex items-center justify-between">
            <h2 class="text-base font-semibold text-zinc-950">Field yang Disiapkan</h2>
            <span class="rounded-full bg-zinc-100 px-3 py-2 text-xs font-medium text-zinc-700">Static prototype</span>
        </div>
        <div class="mt-4 space-y-3">
            <?php foreach (['Nama lembaga', 'Jenis lembaga', 'Alamat singkat', 'Email operasional', 'Nomor WhatsApp', 'Logo lembaga'] as $field): ?>
                <a href="<?= esc($editUrl) ?>" class="block rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-700"><?= esc($field) ?></a>
            <?php endforeach; ?>
        </div>
    </section>
</div>
<?= $this->endSection() ?>
