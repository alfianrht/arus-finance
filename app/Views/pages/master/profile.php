<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<div class="space-y-3">
    <?= view('partials/top_nav_back', [
        'title' => 'Profil Lembaga',
        'subtitle' => 'Master Data',
        'backUrl' => $backUrl,
        'breadcrumbs' => [
            ['label' => 'Pengaturan', 'url' => site_url('pengaturan')],
            ['label' => 'Profil Lembaga'],
        ],
    ]) ?>

    <div class="flex justify-end">
        <a href="<?= esc($editUrl) ?>" class="inline-flex h-11 items-center justify-center gap-2 rounded-full bg-zinc-950 px-5 text-sm font-semibold text-white">
            <span class="material-symbols-rounded text-base" aria-hidden="true">edit</span>
            <span>Edit Profil</span>
        </a>
    </div>

    <section class="rounded-3xl border border-zinc-950 bg-white p-5">
        <div class="flex items-start gap-4">
            <div class="flex h-20 w-20 shrink-0 items-center justify-center overflow-hidden rounded-2xl border border-zinc-200 bg-zinc-50">
                <?php if (! empty($institutionLogo)): ?>
                    <img src="<?= esc(base_url($institutionLogo)) ?>" alt="<?= esc($institutionName) ?>" data-image-preview data-image-preview-alt="<?= esc($institutionName) ?>" class="h-full w-full cursor-zoom-in object-contain">
                <?php else: ?>
                    <span class="material-symbols-rounded text-3xl text-zinc-400" aria-hidden="true">domain</span>
                <?php endif; ?>
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-xs font-medium uppercase tracking-wide text-zinc-500">Identitas Utama</p>
                <p class="mt-3 text-lg font-semibold text-zinc-950"><?= esc($institutionName) ?></p>
                <p class="mt-1 text-sm text-zinc-500">Dipakai sebagai identitas global aplikasi, header, dan dokumen ringkas yang keluar dari Arus.</p>
            </div>
        </div>
        <div class="mt-4 grid grid-cols-2 gap-3">
            <?php foreach ($profileSections as $item): ?>
                <div class="rounded-2xl bg-zinc-50 p-3">
                    <p class="text-xs text-zinc-500"><?= esc($item['label']) ?></p>
                    <p class="mt-2 text-sm font-semibold text-zinc-950"><?= esc($item['value']) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="rounded-3xl border border-zinc-200 bg-white p-4 shadow-sm">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="flex min-w-0 items-start gap-3">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-2xl border border-zinc-200 bg-zinc-50">
                    <?php if (! empty($googleAuth['avatarUrl'])): ?>
                        <img src="<?= esc($googleAuth['avatarUrl']) ?>" alt="Avatar Google" class="h-full w-full object-cover">
                    <?php else: ?>
                        <img src="https://www.svgrepo.com/show/475656/google-color.svg" alt="Google" class="h-6 w-6">
                    <?php endif; ?>
                </div>
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <h2 class="text-base font-semibold text-zinc-950">Akses Login Google</h2>
                        <?php if (! empty($googleAuth['isLinked'])): ?>
                            <span class="inline-flex items-center rounded-full bg-lime-100 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.16em] text-lime-700">Tertaut</span>
                        <?php else: ?>
                            <span class="inline-flex items-center rounded-full bg-zinc-100 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.16em] text-zinc-600">Belum tertaut</span>
                        <?php endif; ?>
                    </div>
                    <p class="mt-2 text-sm text-zinc-500">
                        <?php if (! empty($googleAuth['isLinked'])): ?>
                            Akun ini sudah bisa dipakai masuk dengan Google melalui <?= esc($googleAuth['email']) ?>.
                        <?php else: ?>
                            Tautkan akun Google agar nanti login cukup satu klik tanpa OTP atau sandi tambahan.
                        <?php endif; ?>
                    </p>
                    <div class="mt-3 flex flex-wrap gap-2 text-xs text-zinc-500">
                        <span class="rounded-full border border-zinc-200 bg-zinc-50 px-3 py-2">Provider: <?= esc($googleAuth['providerLabel']) ?></span>
                        <span class="rounded-full border border-zinc-200 bg-zinc-50 px-3 py-2">Email: <?= esc($googleAuth['email']) ?></span>
                    </div>
                </div>
            </div>
            <div class="flex shrink-0">
                <a href="<?= esc($googleAuth['linkUrl']) ?>" class="inline-flex h-11 items-center justify-center gap-2 rounded-full bg-zinc-950 px-5 text-sm font-semibold text-white">
                    <img src="https://www.svgrepo.com/show/475656/google-color.svg" alt="Google" class="h-4 w-4">
                    <span><?= ! empty($googleAuth['isLinked']) ? 'Tautkan Ulang Google' : 'Tautkan Google' ?></span>
                </a>
            </div>
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
