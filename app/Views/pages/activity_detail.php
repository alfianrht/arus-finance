<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<?php $surfaceText = surface_label($activity['short_name'] ?? $activity['name']); ?>
<div class="space-y-3">
    <?= view('partials/top_nav_back', [
        'title' => $activity['name'],
        'subtitle' => $activity['unit_name'],
        'backUrl' => $backUrl ?? site_url('rekap'),
    ]) ?>

    <section class="relative overflow-hidden rounded-3xl bg-zinc-950 p-5 text-white shadow-sm">
        <div class="absolute inset-0 bg-white/5" aria-hidden="true"></div>
        <div class="relative flex items-start justify-between gap-4">
            <div class="min-w-0">
                <p class="text-xs font-medium uppercase tracking-wide text-zinc-400">Kegiatan</p>
                <p class="mt-2 text-2xl font-semibold leading-tight text-white"><?= esc($activity['name']) ?></p>
                <p class="mt-2 text-sm text-zinc-400"><?= esc($activity['unit_name']) ?></p>
            </div>
            <p class="text-lg font-black uppercase tracking-tight text-white"><?= esc($surfaceText) ?></p>
        </div>

        <div class="relative mt-6">
            <p class="text-xs font-medium uppercase tracking-wide text-zinc-400">Saldo Terkait</p>
            <p class="mt-2 text-4xl font-black tracking-tight text-white"><?= esc(rupiah($activity['related_balance'] ?? 0)) ?></p>
            <?php if (!empty($activity['related_accounts'])): ?>
            <p class="mt-3 text-sm text-zinc-300"><?= esc(implode(', ', $activity['related_accounts'])) ?></p>
            <?php else: ?>
            <p class="mt-3 text-sm text-zinc-400">Semua Rekening</p>
            <?php endif; ?>
        </div>

        <p class="pointer-events-none absolute -bottom-3 left-4 text-7xl font-black uppercase tracking-tight text-white/10" aria-hidden="true"><?= esc($surfaceText) ?></p>

        <div class="relative mt-4 grid grid-cols-2 gap-3 border-t border-white/10 pt-4 sm:grid-cols-4">
            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-zinc-500">Masuk</p>
                <p class="mt-1 text-sm font-semibold text-white"><?= esc(rupiah($activity['income'])) ?></p>
            </div>
            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-zinc-500">Biaya</p>
                <p class="mt-1 text-sm font-semibold text-white"><?= esc(rupiah($activity['expense'])) ?></p>
            </div>
            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-zinc-500">Surplus</p>
                <p class="mt-1 text-sm font-semibold text-white"><?= esc(rupiah($activity['surplus'])) ?></p>
            </div>
            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-zinc-500">Saldo Terkait</p>
                <p class="mt-1 text-sm font-semibold text-white"><?= esc(rupiah($activity['related_balance'] ?? 0)) ?></p>
            </div>
        </div>

        <div class="relative mt-4 flex items-center justify-between gap-3">
            <p class="text-sm font-semibold tracking-wide text-zinc-300">•••• <?= esc(surface_tail($activity['slug'] ?? $activity['name'])) ?></p>
            <span class="inline-flex items-center gap-1 rounded-full bg-lime-400 px-3 py-2 text-xs font-semibold text-zinc-950">
                <span class="material-symbols-rounded text-sm" aria-hidden="true">radio_button_checked</span>
                <span>Siap Dicatat</span>
            </span>
        </div>
    </section>

    <div class="grid grid-cols-2 gap-3">
        <a href="<?= esc($activity['masuk_url']) ?>" class="inline-flex h-12 items-center justify-center gap-2 rounded-full border border-zinc-200 bg-white px-4 text-sm font-semibold text-zinc-950 shadow-sm">
            <span class="material-symbols-rounded text-base" aria-hidden="true">add</span>
            <span>Uang Masuk</span>
        </a>
        <a href="<?= esc($activity['keluar_url']) ?>" class="inline-flex h-12 items-center justify-center gap-2 rounded-full bg-zinc-950 px-4 text-sm font-semibold text-white shadow-sm">
            <span class="material-symbols-rounded text-base" aria-hidden="true">remove</span>
            <span>Uang Keluar</span>
        </a>
    </div>

    <section class="rounded-3xl bg-white p-4 shadow-sm">
        <div class="flex items-center justify-between gap-3">
            <div>
                <h2 class="text-base font-semibold text-zinc-950">Mode Kantong Proyek</h2>
                <p class="mt-1 text-xs text-zinc-500">Aktifkan jika kegiatan ini perlu dipisah antara kontrak utama dan kantong pelaksanaan.</p>
            </div>
            <span class="rounded-full bg-zinc-100 px-3 py-2 text-xs font-medium text-zinc-700">Belum aktif</span>
        </div>

        <form action="<?= esc($projectActivationUrl) ?>" method="post" class="mt-4 grid gap-3 sm:grid-cols-2">
            <?= csrf_field() ?>
            <div class="space-y-2">
                <label class="text-sm font-semibold text-zinc-900">Nilai Kontrak</label>
                <input type="text" inputmode="numeric" name="contract_value" value="<?= esc(old('contract_value', '')) ?>" class="h-12 w-full rounded-2xl border border-zinc-100 bg-white px-4 text-sm text-zinc-950 outline-none focus:border-zinc-300 focus:ring-2 focus:ring-lime-400" placeholder="Opsional, isi jika kegiatan ini berbasis kontrak">
            </div>
            <div class="space-y-2">
                <label class="text-sm font-semibold text-zinc-900">Jumlah Termin</label>
                <input type="number" min="0" name="contract_terms_count" value="<?= esc(old('contract_terms_count', '')) ?>" class="h-12 w-full rounded-2xl border border-zinc-100 bg-white px-4 text-sm text-zinc-950 outline-none focus:border-zinc-300 focus:ring-2 focus:ring-lime-400" placeholder="0">
            </div>
            <div class="space-y-2 sm:col-span-2">
                <label class="text-sm font-semibold text-zinc-900">Catatan Awal</label>
                <textarea name="notes" rows="3" class="w-full rounded-2xl border border-zinc-100 bg-white px-4 py-3 text-sm text-zinc-950 outline-none focus:border-zinc-300 focus:ring-2 focus:ring-lime-400" placeholder="Contoh: rumah utama termin proyek dan tagihan pelaksanaan."><?= esc(old('notes', '')) ?></textarea>
            </div>
            <div class="sm:col-span-2">
                <button type="submit" class="inline-flex h-12 items-center justify-center rounded-full bg-zinc-950 px-5 text-sm font-semibold text-white shadow-sm">Aktifkan Mode Proyek</button>
            </div>
        </form>
    </section>

    <section class="rounded-3xl bg-white p-4 shadow-sm">
        <div class="flex items-center justify-between">
            <h2 class="text-base font-semibold text-zinc-950">Rincian Biaya per Kategori</h2>
            <p class="text-xs text-zinc-500">Kegiatan aktif</p>
        </div>
        <div class="mt-3 space-y-3">
            <?php $hasCategoryCost = false; ?>
            <?php foreach ($categoryBreakdown as $item): ?>
                <?php if ($item['total_amount'] <= 0) {
                    continue;
                } ?>
                <?php $hasCategoryCost = true; ?>
                <div class="flex items-center justify-between rounded-2xl bg-zinc-50 px-4 py-3">
                    <span class="text-sm font-medium text-zinc-800"><?= esc($item['category_name']) ?></span>
                    <span class="text-sm font-semibold text-zinc-950"><?= esc(rupiah($item['total_amount'])) ?></span>
                </div>
            <?php endforeach; ?>
            <?php if (! $hasCategoryCost): ?>
                <?= view('partials/empty_state', [
                    'icon' => 'sell',
                    'title' => 'Belum ada rincian biaya.',
                    'description' => 'Biaya per kategori akan muncul setelah ada transaksi pengeluaran pada kegiatan ini.',
                    'compact' => true,
                ]) ?>
            <?php endif; ?>
        </div>
    </section>

    <section class="rounded-3xl bg-white py-4 shadow-sm">
        <div class="px-4 flex items-center justify-between">
            <h2 class="text-base font-semibold text-zinc-950">Pindah Dana</h2>
            <span class="rounded-full bg-sky-50 px-3 py-2 text-xs font-medium text-sky-700">Bukan biaya</span>
        </div>
        <div class="mt-3 divide-y divide-zinc-100">
            <?php if ($transferItems === []): ?>
                <div class="px-4 pb-1">
                    <?= view('partials/empty_state', [
                        'icon' => 'swap_horiz',
                        'title' => 'Belum ada pindah dana.',
                        'description' => 'Pindah dana akan muncul di sini saat ada perpindahan antar rekening pada kegiatan ini.',
                        'compact' => true,
                    ]) ?>
                </div>
            <?php endif; ?>
            <?php foreach ($transferItems as $transaction): ?>
                <?= view('partials/transaction_item', ['transaction' => $transaction]) ?>
            <?php endforeach; ?>
        </div>
        <?= view('partials/pagination_controls', [
            'pagination' => $activityTransferPagination,
            'prevUrl' => route_query('kegiatan/' . $activity['slug'], ['periode' => service('request')->getGet('periode') ?: 'semua', 'unit' => service('request')->getGet('unit') ?: 'semua', 'mutasi_page' => $activityTransferPagination['prevPage'], 'transaksi_page' => $activityTransactionPagination['page']]),
            'nextUrl' => route_query('kegiatan/' . $activity['slug'], ['periode' => service('request')->getGet('periode') ?: 'semua', 'unit' => service('request')->getGet('unit') ?: 'semua', 'mutasi_page' => $activityTransferPagination['nextPage'], 'transaksi_page' => $activityTransactionPagination['page']]),
        ]) ?>
    </section>

    <section class="rounded-3xl bg-white pt-4 pb-1 shadow-sm overflow-hidden">
        <div class="flex items-center justify-between px-4">
            <h2 class="text-base font-semibold text-zinc-950">Penerima Terlibat</h2>
        </div>
        <div class="mt-4">
            <?php if (empty($involvedReceivers)): ?>
                <div class="px-4 pb-4">
                    <?= view('partials/empty_state', [
                        'icon' => 'groups',
                        'title' => 'Belum ada penerima terlibat.',
                        'description' => 'Penerima akan muncul dari transaksi honor atau pengeluaran yang terkait ke kegiatan ini.',
                        'compact' => true,
                    ]) ?>
                </div>
            <?php else: ?>
                <div class="flex flex-nowrap gap-3 overflow-x-auto px-4 pb-4 snap-x snap-mandatory scroll-p-4" style="scrollbar-width: none;">
                    <style>
                        .overflow-x-auto::-webkit-scrollbar { display: none; }
                    </style>
                    
                    <?php foreach ($involvedReceivers as $receiver): ?>
                        <?= view('partials/receiver_card', ['receiver' => $receiver, 'widthClass' => 'w-48 sm:w-52']) ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <section class="rounded-3xl bg-white p-4 shadow-sm">
        <div class="flex items-center justify-between">
            <h2 class="text-base font-semibold text-zinc-950">Rekening Terlibat</h2>
            <p class="text-xs text-zinc-500">Dipakai kegiatan ini</p>
        </div>
        <div class="mt-3 space-y-3 md:grid md:grid-cols-2 md:gap-3 md:space-y-0">
            <?php if ($involvedAccounts === []): ?>
                <?= view('partials/empty_state', [
                    'icon' => 'account_balance_wallet',
                    'title' => 'Belum ada rekening terlibat.',
                    'description' => 'Rekening akan muncul saat ada transaksi yang memakai rekening atau dompet pada kegiatan ini.',
                    'compact' => true,
                ]) ?>
            <?php endif; ?>
            <?php foreach ($involvedAccounts as $account): ?>
                <?= view('partials/account_card', ['account' => $account, 'cardWidthClass' => 'w-full']) ?>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="rounded-3xl bg-white p-4 shadow-sm">
        <div class="flex items-center justify-between">
            <h2 class="text-base font-semibold text-zinc-950">Transaksi Terakhir Kegiatan</h2>
            <p class="text-xs text-zinc-500"><?= esc($activity['name']) ?></p>
        </div>
        <div class="mt-3 divide-y divide-zinc-100">
            <?php if ($activityTransactions === []): ?>
                <div class="px-4 pb-1">
                    <?= view('partials/empty_state', [
                        'icon' => 'receipt_long',
                        'title' => 'Belum ada transaksi untuk kegiatan ini.',
                        'description' => 'Transaksi terbaru akan muncul di sini setelah ada pencatatan pada kegiatan ini.',
                        'compact' => true,
                    ]) ?>
                </div>
            <?php endif; ?>
            <?php foreach ($activityTransactions as $transaction): ?>
                <?= view('partials/transaction_item', ['transaction' => $transaction]) ?>
            <?php endforeach; ?>
        </div>
        <?= view('partials/pagination_controls', [
            'pagination' => $activityTransactionPagination,
            'prevUrl' => route_query('kegiatan/' . $activity['slug'], ['periode' => service('request')->getGet('periode') ?: 'semua', 'unit' => service('request')->getGet('unit') ?: 'semua', 'transaksi_page' => $activityTransactionPagination['prevPage'], 'mutasi_page' => $activityTransferPagination['page']]),
            'nextUrl' => route_query('kegiatan/' . $activity['slug'], ['periode' => service('request')->getGet('periode') ?: 'semua', 'unit' => service('request')->getGet('unit') ?: 'semua', 'transaksi_page' => $activityTransactionPagination['nextPage'], 'mutasi_page' => $activityTransferPagination['page']]),
        ]) ?>
    </section>
</div>
<?= $this->endSection() ?>
