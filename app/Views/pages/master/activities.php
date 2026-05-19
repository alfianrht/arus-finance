<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<div class="space-y-3">
    <?php
    $totalIncome = array_sum(array_map(static fn(array $activity): float => (float) ($activity['income'] ?? 0), $activitySummaries));
    $totalExpense = array_sum(array_map(static fn(array $activity): float => (float) ($activity['expense'] ?? 0), $activitySummaries));
    $totalBalance = array_sum(array_map(static fn(array $activity): float => (float) ($activity['related_balance'] ?? 0), $activitySummaries));
    ?>
    <?= view('partials/top_nav_back', [
        'title' => 'Kegiatan',
        'subtitle' => 'Master Data',
        'backUrl' => $backUrl,
    ]) ?>

    <div class="flex justify-end">
        <a href="<?= site_url('pengaturan/kegiatan/tambah') ?>" class="inline-flex h-11 items-center justify-center gap-2 rounded-full bg-zinc-950 px-5 text-sm font-semibold text-white">
            <span class="material-symbols-rounded text-base" aria-hidden="true">add</span>
            <span>Tambah Kegiatan</span>
        </a>
    </div>

    <section class="rounded-3xl border border-zinc-100 bg-white p-5 shadow-sm">
        <div class="relative flex items-start justify-between gap-4">
            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-zinc-500">Konteks Aktif</p>
                <p class="mt-3 text-lg font-semibold text-zinc-950"><?= esc(count($activitySummaries)) ?> kegiatan siap dipilih saat mencatat</p>
                <p class="mt-1 text-sm text-zinc-500">Setiap kegiatan terhubung ke satu unit dan sekarang langsung membaca ringkasan transaksi yang sudah tercatat.</p>
            </div>
            <span class="absolute top-0 right-0 rounded-full bg-zinc-950 px-3 py-2 text-xs font-medium text-white">Konteks level 2</span>
        </div>
        <div class="mt-4 grid grid-cols-2 gap-2 sm:grid-cols-4">
            <div class="rounded-2xl border border-zinc-100 bg-zinc-50 px-4 py-3">
                <p class="text-[11px] font-bold uppercase tracking-wider text-zinc-500">Kegiatan</p>
                <p class="mt-1 text-base font-black text-zinc-950"><?= esc((string) count($activitySummaries)) ?></p>
            </div>
            <div class="rounded-2xl border border-zinc-100 bg-zinc-50 px-4 py-3">
                <p class="text-[11px] font-bold uppercase tracking-wider text-zinc-500">Masuk</p>
                <p class="mt-1 text-base font-black text-zinc-950"><?= esc(rupiah($totalIncome)) ?></p>
            </div>
            <div class="rounded-2xl border border-zinc-100 bg-zinc-50 px-4 py-3">
                <p class="text-[11px] font-bold uppercase tracking-wider text-zinc-500">Keluar</p>
                <p class="mt-1 text-base font-black text-zinc-950"><?= esc(rupiah($totalExpense)) ?></p>
            </div>
            <div class="rounded-2xl border border-zinc-100 bg-zinc-50 px-4 py-3">
                <p class="text-[11px] font-bold uppercase tracking-wider text-zinc-500">Saldo</p>
                <p class="mt-1 text-base font-black text-zinc-950"><?= esc(rupiah($totalBalance)) ?></p>
            </div>
        </div>
    </section>

    <section class="space-y-3">
        <?php if (empty($activitySummaries)): ?>
            <?= view('partials/empty_state', [
                'icon'        => 'event_note',
                'title'       => 'Belum Ada Kegiatan',
                'message'     => 'Kegiatan adalah konteks pencatatan transaksi. Tambahkan kegiatan untuk mulai mencatat uang masuk dan keluar.',
                'actionUrl'   => site_url('pengaturan/kegiatan/tambah'),
                'actionLabel' => 'Tambah Kegiatan',
            ]) ?>
        <?php else: ?>
            <?php foreach ($activitySummaries as $activity): ?>
                <?php $isInactive = (($activity['status_label'] ?? '') === 'Nonaktif'); ?>
                <article class="relative pb-2 <?= $isInactive ? 'opacity-75' : '' ?>">
                    <div class="relative z-10">
                        <?= view('partials/activity_card', ['activity' => array_merge($activity, ['name' => $isInactive ? $activity['name'] . ' · Nonaktif' : $activity['name']])]) ?>
                    </div>
                    <div class="relative -mt-5 pt-[26px] flex items-center justify-between gap-3 rounded-[1.4rem] border <?= $isInactive ? 'border-zinc-200 bg-zinc-100/90' : 'border-zinc-100 bg-white' ?> px-4 py-3 shadow-sm">
                        <div class="min-w-0">
                            <p class="text-sm font-semibold <?= $isInactive ? 'text-rose-600' : 'text-zinc-950' ?>"><?= esc($activity['status_label']) ?></p>
                            <p class="mt-1 text-xs text-zinc-500"><?= esc($activity['unit_name']) ?> · klik kartu untuk buka form kegiatan</p>
                        </div>
                        <div class="flex shrink-0 items-center gap-2">
                            <a href="<?= site_url('kegiatan/' . $activity['slug']) ?>" class="rounded-full border border-zinc-200 bg-white px-3 py-2 text-xs font-semibold text-zinc-950">Lihat Kegiatan</a>
                            <?php if ((int) ($activity['transaction_count'] ?? 0) === 0): ?>
                                <button
                                    type="button"
                                    onclick="openDeleteModal('<?= site_url('pengaturan/kegiatan/' . $activity['slug'] . '/hapus') ?>', '<?= esc($activity['name'], 'js') ?>', '<?= csrf_hash() ?>')"
                                    class="rounded-full bg-rose-500 px-3 py-2 text-xs font-semibold text-white"
                                >Hapus</button>
                            <?php else: ?>
                                <span class="rounded-full border border-zinc-200 bg-white px-3 py-2 text-xs font-medium text-zinc-400 cursor-not-allowed" title="Kegiatan memiliki <?= esc((string) ($activity['transaction_count'] ?? 0)) ?> transaksi. Hapus transaksi terlebih dahulu.">Hapus</span>
                            <?php endif; ?>
                            <a href="<?= site_url('pengaturan/kegiatan/' . $activity['slug'] . '/edit') ?>" class="rounded-full border border-zinc-200 bg-white px-3 py-2 text-xs font-semibold text-zinc-950">Edit</a>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>

    <section class="rounded-3xl bg-white p-4 shadow-sm">
        <div class="flex items-center justify-between gap-3">
            <h2 class="text-base font-semibold text-zinc-950">Field yang Disiapkan</h2>
            <a href="<?= site_url('pengaturan/kegiatan/tambah') ?>" class="text-sm font-medium text-zinc-700">Buka Form</a>
        </div>
        <div class="mt-4 grid gap-3 sm:grid-cols-2">
            <?php foreach (['Nama kegiatan', 'Singkatan kegiatan', 'Unit induk', 'Status aktif', 'Urutan tampil'] as $field): ?>
                <a href="<?= site_url('pengaturan/kegiatan/tambah') ?>" class="block rounded-2xl border border-zinc-200 px-4 py-3 text-sm text-zinc-700"><?= esc($field) ?></a>
            <?php endforeach; ?>
        </div>
    </section>
</div>

<?= view('partials/confirm_delete_modal') ?>
<?= $this->endSection() ?>
