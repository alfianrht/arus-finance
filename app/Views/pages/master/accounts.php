<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<div class="space-y-3">
    <?php
    $totalIncome = array_sum(array_map(static fn(array $account): float => (float) ($account['income'] ?? 0), $accountSummaries));
    $totalExpense = array_sum(array_map(static fn(array $account): float => (float) ($account['expense'] ?? 0), $accountSummaries));
    $totalBalance = array_sum(array_map(static fn(array $account): float => (float) ($account['balance'] ?? 0), $accountSummaries));
    ?>
    <?= view('partials/top_nav_back', [
        'title' => 'Rekening / Dompet',
        'subtitle' => 'Master Data',
        'backUrl' => $backUrl,
    ]) ?>

    <div class="flex justify-end">
        <a href="<?= site_url('pengaturan/rekening-dompet/tambah') ?>" class="inline-flex h-11 items-center justify-center gap-2 rounded-full bg-zinc-950 px-5 text-sm font-semibold text-white">
            <span class="material-symbols-rounded text-base" aria-hidden="true">add</span>
            <span>Tambah Rekening</span>
        </a>
    </div>

    <section class="rounded-3xl border border-zinc-100 bg-white p-5 shadow-sm">
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-zinc-500">Arus Uang</p>
                <p class="mt-3 text-lg font-semibold text-zinc-950"><?= esc(count($accountSummaries)) ?> sumber dan tujuan dana</p>
                <p class="mt-1 text-sm text-zinc-500">Master ini dipakai untuk uang masuk, biaya, pindah dana, dan sekarang langsung membaca mutasi transaksi yang sudah tercatat.</p>
            </div>
            <span class="rounded-full bg-zinc-100 px-3 py-2 text-xs font-medium text-zinc-700">Tanpa mapping terpisah</span>
        </div>
        <div class="mt-4 grid grid-cols-2 gap-2 sm:grid-cols-4">
            <div class="rounded-2xl border border-zinc-100 bg-zinc-50 px-4 py-3">
                <p class="text-[11px] font-bold uppercase tracking-wider text-zinc-500">Rekening</p>
                <p class="mt-1 text-base font-black text-zinc-950"><?= esc((string) count($accountSummaries)) ?></p>
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
        <?php if (empty($accountSummaries)): ?>
            <?= view('partials/empty_state', [
                'icon'        => 'account_balance',
                'title'       => 'Belum Ada Rekening',
                'message'     => 'Rekening / Dompet menyimpan sumber dan tujuan dana. Tambahkan rekening pertama agar transaksi bisa dicatat.',
                'actionUrl'   => site_url('pengaturan/rekening-dompet/tambah'),
                'actionLabel' => 'Tambah Rekening',
            ]) ?>
        <?php else: ?>
            <?php foreach ($accountSummaries as $account): ?>
                <?php $isInactive = (($account['status_label'] ?? '') === 'Nonaktif'); ?>
                <article class="relative pb-2 <?= $isInactive ? 'opacity-75' : '' ?>">
                    <?php
                    $masterAccount = $account;
                    $masterAccount['detail_url'] = site_url('pengaturan/rekening-dompet/' . $account['slug'] . '/edit');
                    ?>
                    <div class="relative z-10">
                        <?= view('partials/account_card', ['account' => array_merge($masterAccount, ['name' => $isInactive ? $account['name'] . ' · Nonaktif' : $account['name']]), 'cardWidthClass' => 'w-full']) ?>
                    </div>
                    <div class="relative -mt-5 pt-[26px] flex items-center justify-between gap-3 rounded-[1.4rem] border <?= $isInactive ? 'border-zinc-200 bg-zinc-100/90' : 'border-zinc-100 bg-white' ?> px-4 py-3 shadow-sm">
                        <div class="min-w-0">
                            <p class="text-sm font-semibold <?= $isInactive ? 'text-rose-600' : 'text-zinc-950' ?>"><?= esc($account['status_label']) ?></p>
                            <p class="mt-1 text-xs text-zinc-500"><?= esc($account['report_position_name'] ?? 'Belum dipetakan') ?></p>
                        </div>
                        <div class="flex shrink-0 items-center gap-2">
                            <a href="<?= esc(site_url('rekening/' . $account['slug'])) ?>" class="rounded-full border border-zinc-200 bg-white px-3 py-2 text-xs font-semibold text-zinc-950">Lihat Mutasi</a>
                            <?php if ((int) ($account['transaction_count'] ?? 0) === 0): ?>
                                <button
                                    type="button"
                                    onclick="openDeleteModal('<?= site_url('pengaturan/rekening-dompet/' . $account['slug'] . '/hapus') ?>', '<?= esc($account['name'], 'js') ?>', '<?= csrf_hash() ?>')"
                                    class="rounded-full bg-rose-500 px-3 py-2 text-xs font-semibold text-white"
                                >Hapus</button>
                            <?php else: ?>
                                <span class="rounded-full border border-zinc-200 bg-white px-3 py-2 text-xs font-medium text-zinc-400 cursor-not-allowed" title="Rekening memiliki <?= esc((string) ($account['transaction_count'] ?? 0)) ?> transaksi. Hapus transaksi terlebih dahulu.">Hapus</span>
                            <?php endif; ?>
                            <a href="<?= site_url('pengaturan/rekening-dompet/' . $account['slug'] . '/edit') ?>" class="rounded-full border border-zinc-200 bg-white px-3 py-2 text-xs font-semibold text-zinc-950">Edit</a>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>

    <section class="rounded-3xl bg-white p-4 shadow-sm">
        <div class="flex items-center justify-between gap-3">
            <h2 class="text-base font-semibold text-zinc-950">Field yang Disiapkan</h2>
            <a href="<?= site_url('pengaturan/rekening-dompet/tambah') ?>" class="text-sm font-medium text-zinc-700">Buka Form</a>
        </div>
        <div class="mt-4 grid gap-3 sm:grid-cols-2">
            <?php foreach (['Nama rekening / dompet', 'Jenis penyimpanan dana', 'Singkatan / logo', 'Pos laporan terkait', 'Catatan penggunaan', 'Status aktif'] as $field): ?>
                <a href="<?= site_url('pengaturan/rekening-dompet/tambah') ?>" class="block rounded-2xl border border-zinc-200 px-4 py-3 text-sm text-zinc-700"><?= esc($field) ?></a>
            <?php endforeach; ?>
        </div>
    </section>
</div>

<?= view('partials/confirm_delete_modal') ?>
<?= $this->endSection() ?>
