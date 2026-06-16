<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<div class="space-y-3">
    <?php
    $totalReceived = array_sum(array_map(static fn(array $receiver): float => (float) ($receiver['total_received'] ?? 0), $receivers));
    $totalTransactions = array_sum(array_map(static fn(array $receiver): int => (int) ($receiver['transaction_count'] ?? 0), $receivers));
    ?>
    <?= view('partials/top_nav_back', [
        'title' => 'Penerima',
        'subtitle' => 'Master Data',
        'backUrl' => $backUrl,
        'breadcrumbs' => [
            ['label' => 'Pengaturan', 'url' => site_url('pengaturan')],
            ['label' => 'Penerima'],
        ],
    ]) ?>

    <div class="flex justify-end">
        <a href="<?= site_url('pengaturan/penerima/tambah') ?>" class="inline-flex h-11 items-center justify-center gap-2 rounded-full bg-zinc-950 px-5 text-sm font-semibold text-white">
            <span class="material-symbols-rounded text-base" aria-hidden="true">add</span>
            <span>Tambah Penerima</span>
        </a>
    </div>

    <section class="rounded-3xl border border-zinc-100 bg-white p-4 shadow-[0_8px_30px_rgba(0,0,0,0.04)]">
        <div class="flex items-start justify-between gap-4">
            <div class="min-w-0">
                <span class="inline-flex rounded-full border border-zinc-200 bg-white px-3 py-1.5 text-xs font-medium text-zinc-700">Kontak, Vendor & Tim</span>
                <p class="mt-3 text-sm font-medium text-zinc-700">Penerima aktif</p>
                <p class="mt-0.5 text-3xl font-semibold tracking-tight tabular-nums text-zinc-950"><?= esc((string) count($receivers)) ?></p>
                <p class="mt-1.5 max-w-xs text-xs text-zinc-500">Master kontak untuk honor, vendor, dan pembayaran pihak terkait.</p>
            </div>
            <span class="shrink-0 rounded-full bg-lime-100 px-3 py-1.5 text-[11px] font-semibold text-zinc-950">Master Kontak</span>
        </div>
        <div class="mt-4 grid grid-cols-2 gap-2 sm:grid-cols-4">
            <div class="rounded-2xl border border-zinc-100 bg-zinc-50 px-3.5 py-2.5">
                <p class="text-xs text-zinc-500">Penerima</p>
                <p class="mt-0.5 text-base font-semibold tabular-nums text-zinc-950"><?= esc((string) count($receivers)) ?></p>
            </div>
            <div class="rounded-2xl border border-zinc-100 bg-zinc-50 px-3.5 py-2.5">
                <p class="text-xs text-zinc-500">Transaksi</p>
                <p class="mt-0.5 text-base font-semibold tabular-nums text-zinc-950"><?= esc((string) $totalTransactions) ?></p>
            </div>
            <div class="rounded-2xl border border-zinc-100 bg-zinc-50 px-3.5 py-2.5 sm:col-span-2">
                <p class="text-xs text-zinc-500">Total Terkait</p>
                <p class="mt-0.5 text-base font-semibold tabular-nums text-zinc-950"><?= esc(rupiah($totalReceived)) ?></p>
            </div>
        </div>
    </section>

    <section class="space-y-3">
        <?php if (empty($receivers)): ?>
            <?= view('partials/empty_state', [
                'icon' => 'contacts', 'title' => 'Belum Ada Penerima',
                'message' => 'Penerima mempercepat pencatatan pengeluaran. Tambahkan kontak vendor, staf, atau pihak ketiga lainnya.',
                'actionUrl' => site_url('pengaturan/penerima/tambah'), 'actionLabel' => 'Tambah Penerima',
            ]) ?>
        <?php else: ?>
            <div class="space-y-3">
                <?php foreach ($receivers as $receiver): ?>
                    <article class="relative pb-2">
                        <div class="relative z-10">
                            <?= view('partials/receiver_card', ['receiver' => $receiver, 'widthClass' => 'w-full']) ?>
                        </div>
                        <div class="relative -mt-5 pt-[26px] flex items-center justify-between gap-3 rounded-b-[1.4rem] border border-zinc-100 bg-white px-4 py-3 shadow-sm">
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-zinc-950"><?= esc($receiver['type'] ?? 'Lainnya') ?></p>
                                <p class="mt-1 text-xs text-zinc-500"><?= esc((string) ($receiver['transaction_count'] ?? 0)) ?> transaksi terkait</p>
                            </div>
                            <div class="flex shrink-0 items-center gap-2">
                                <a href="<?= site_url('penerima/' . $receiver['slug']) ?>" class="rounded-full border border-zinc-200 bg-white px-3 py-2 text-xs font-semibold text-zinc-950">Lihat Penerima</a>
                                <?php if ((int) ($receiver['transaction_count'] ?? 0) === 0): ?>
                                    <button type="button" onclick="openDeleteModal('<?= site_url('pengaturan/penerima/' . $receiver['slug'] . '/hapus') ?>', '<?= esc($receiver['name'], 'js') ?>', '<?= csrf_hash() ?>')" class="rounded-full bg-rose-500 px-3 py-2 text-xs font-semibold text-white">Hapus</button>
                                <?php else: ?>
                                    <span class="rounded-full border border-zinc-200 bg-white px-3 py-2 text-xs font-medium text-zinc-400 cursor-not-allowed" title="Penerima memiliki <?= esc((string) ($receiver['transaction_count'] ?? 0)) ?> transaksi. Hapus transaksi terlebih dahulu.">Hapus</span>
                                <?php endif; ?>
                                <a href="<?= site_url('pengaturan/penerima/' . $receiver['slug'] . '/edit') ?>" class="rounded-full border border-zinc-200 bg-white px-3 py-2 text-xs font-semibold text-zinc-950">Edit</a>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</div>

<?= view('partials/confirm_delete_modal') ?>
<?= $this->endSection() ?>
