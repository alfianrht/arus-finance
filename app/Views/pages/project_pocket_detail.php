<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<?php
$surfaceText = surface_label($activity['short_name'] ?? $activity['name']);
$isMainPocket = ($pocket['pocket_type'] ?? '') === 'main';
$openPocketSettingsModal = old('form_scope') === 'pocket_settings';
?>
<div class="space-y-3">
    <?= view('partials/top_nav_back', [
        'title' => $activity['name'],
        'subtitle' => $isMainPocket ? 'Kantong utama proyek' : 'Kantong pelaksanaan proyek',
        'backUrl' => $backUrl ?? site_url('rekap'),
    ]) ?>

    <div class="space-y-3 xl:grid xl:grid-cols-[minmax(0,1.45fr)_minmax(0,0.92fr)] xl:items-start xl:gap-4 xl:space-y-0">
        <div class="space-y-3">
            <section class="relative overflow-hidden rounded-3xl bg-zinc-950 p-5 text-white shadow-sm">
        <div class="absolute inset-0 bg-white/5" aria-hidden="true"></div>
        <div class="relative flex items-start justify-between gap-4">
            <div class="min-w-0">
                <p class="text-xs font-medium uppercase tracking-wide text-zinc-400"><?= esc($isMainPocket ? 'Kantong Utama' : 'Kantong Pelaksanaan') ?></p>
                <p class="mt-2 text-2xl font-semibold leading-tight text-white"><?= esc($activity['name']) ?></p>
                <p class="mt-2 text-sm text-zinc-400"><?= esc($activity['unit_name']) ?></p>
            </div>
            <p class="text-lg font-black uppercase tracking-tight text-white"><?= esc($surfaceText) ?></p>
        </div>

        <div class="relative mt-6">
            <p class="text-xs font-medium uppercase tracking-wide text-zinc-400">Saldo Kantong</p>
            <p class="mt-2 text-4xl font-black tracking-tight text-white"><?= esc(rupiah($activity['related_balance'] ?? 0)) ?></p>
            <p class="mt-3 text-sm text-zinc-300"><?= esc($pocketSummary['transaction_count'] ?? 0) ?> transaksi terkait</p>
        </div>

        <p class="pointer-events-none absolute -bottom-3 left-4 text-7xl font-black uppercase tracking-tight text-white/10" aria-hidden="true"><?= esc($surfaceText) ?></p>

        <div class="relative mt-4 grid grid-cols-2 gap-3 border-t border-white/10 pt-4 sm:grid-cols-4">
            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-zinc-500">Masuk</p>
                <p class="mt-1 text-sm font-semibold text-white"><?= esc(rupiah($activity['income'])) ?></p>
            </div>
            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-zinc-500">Keluar</p>
                <p class="mt-1 text-sm font-semibold text-white"><?= esc(rupiah($activity['expense'])) ?></p>
            </div>
            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-zinc-500">Transfer Masuk</p>
                <p class="mt-1 text-sm font-semibold text-white"><?= esc(rupiah($pocketSummary['transfer_in'] ?? 0)) ?></p>
            </div>
            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-zinc-500">Transfer Keluar</p>
                <p class="mt-1 text-sm font-semibold text-white"><?= esc(rupiah($pocketSummary['transfer_out'] ?? 0)) ?></p>
            </div>
        </div>
            </section>

            <section class="rounded-3xl bg-white p-4 shadow-sm">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-zinc-950 px-3 py-1.5 text-[11px] font-semibold text-white">
                        <span class="material-symbols-rounded text-[14px]" aria-hidden="true">workspaces</span>
                        <span class="truncate"><?= esc($activity['unit_name']) ?></span>
                    </span>
                    <span class="inline-flex items-center gap-1.5 rounded-full border border-zinc-950/10 bg-white px-3 py-1.5 text-[11px] font-semibold text-zinc-700">
                        <span class="material-symbols-rounded text-[14px]" aria-hidden="true"><?= $isMainPocket ? 'kid_star' : 'inventory_2' ?></span>
                        <span><?= esc($isMainPocket ? 'Kantong Utama' : 'Kantong Pelaksanaan') ?></span>
                    </span>
                    <?php if (!((bool) ($pocket['is_active'] ?? true))): ?>
                        <span class="inline-flex rounded-full bg-rose-50 px-3 py-1.5 text-[11px] font-semibold text-rose-600">Nonaktif</span>
                    <?php endif; ?>
                </div>
                <p class="mt-3 text-sm font-semibold text-zinc-950">Pengaturan Kantong</p>
                <p class="mt-1 text-xs text-zinc-500">
                    <?= esc($isMainPocket
                        ? 'Kontrak, termin, dan catatan utama kantong dikelola dari sini.'
                        : 'Nama dan catatan kantong pelaksanaan bisa dirapikan tanpa mengubah detail transaksi.') ?>
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2 sm:justify-end">
                <button type="button" data-pocket-modal-open="pocket-settings-modal" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-zinc-200 bg-white text-zinc-950 shadow-sm" title="Atur Kantong" aria-label="Atur Kantong">
                    <span class="material-symbols-rounded text-[18px]" aria-hidden="true">tune</span>
                </button>
                <?php if (! $isMainPocket): ?>
                    <form action="<?= esc(site_url('kegiatan/' . $activity['project_slug'] . '/kantong/' . $pocket['slug'] . '/nonaktif')) ?>" method="post">
                        <?= csrf_field() ?>
                        <button type="submit" class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-zinc-950 text-white shadow-sm" title="Nonaktifkan Kantong" aria-label="Nonaktifkan Kantong">
                            <span class="material-symbols-rounded text-[18px]" aria-hidden="true">block</span>
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>

        <div class="mt-4 grid grid-cols-2 gap-2 sm:grid-cols-3">
            <?php if ($isMainPocket): ?>
                <div class="rounded-2xl border border-zinc-100 bg-zinc-50 px-3 py-2.5">
                    <p class="text-[10px] font-medium uppercase tracking-wide text-zinc-500">Nilai Kontrak</p>
                    <p class="mt-1 text-sm font-semibold text-zinc-950"><?= esc(rupiah((float) ($pocket['contract_value'] ?? 0))) ?></p>
                </div>
                <div class="rounded-2xl border border-zinc-100 bg-zinc-50 px-3 py-2.5">
                    <p class="text-[10px] font-medium uppercase tracking-wide text-zinc-500">Jumlah Termin</p>
                    <p class="mt-1 text-sm font-semibold text-zinc-950"><?= esc((string) ($pocket['contract_terms_count'] ?? 0)) ?></p>
                </div>
            <?php else: ?>
                <div class="rounded-2xl border border-zinc-100 bg-zinc-50 px-3 py-2.5">
                    <p class="text-[10px] font-medium uppercase tracking-wide text-zinc-500">Tipe</p>
                    <p class="mt-1 text-sm font-semibold text-zinc-950">Pelaksanaan</p>
                </div>
                <div class="rounded-2xl border border-zinc-100 bg-zinc-50 px-3 py-2.5">
                    <p class="text-[10px] font-medium uppercase tracking-wide text-zinc-500">Status</p>
                    <p class="mt-1 text-sm font-semibold text-zinc-950"><?= esc(((bool) ($pocket['is_active'] ?? true)) ? 'Aktif' : 'Nonaktif') ?></p>
                </div>
            <?php endif; ?>
            <div class="rounded-2xl border border-zinc-100 bg-zinc-50 px-3 py-2.5 col-span-2 sm:col-span-1">
                <p class="text-[10px] font-medium uppercase tracking-wide text-zinc-500">Catatan</p>
                <p class="mt-1 text-sm font-semibold text-zinc-950"><?= esc(trim((string) ($pocket['notes'] ?? '')) !== '' ? mb_strimwidth((string) $pocket['notes'], 0, 60, '...') : '-') ?></p>
            </div>
        </div>
            </section>

            <section class="rounded-3xl bg-white p-4 shadow-sm">
        <div class="flex items-center justify-between">
            <h2 class="text-base font-semibold text-zinc-950">Rincian Biaya per Kategori</h2>
            <p class="text-xs text-zinc-500">Scope kantong</p>
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
                    'description' => 'Biaya per kategori akan muncul setelah ada transaksi pengeluaran pada kantong ini.',
                    'compact' => true,
                ]) ?>
            <?php endif; ?>
        </div>
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
                        'description' => 'Penerima akan muncul dari transaksi honor atau pengeluaran pada kantong ini.',
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
            <p class="text-xs text-zinc-500">Dipakai kantong ini</p>
        </div>
        <div class="mt-3 space-y-3 md:grid md:grid-cols-2 md:gap-3 md:space-y-0">
            <?php if ($involvedAccounts === []): ?>
                <?= view('partials/empty_state', [
                    'icon' => 'account_balance_wallet',
                    'title' => 'Belum ada rekening terlibat.',
                    'description' => 'Rekening akan muncul saat ada transaksi yang memakai rekening atau dompet pada kantong ini.',
                    'compact' => true,
                ]) ?>
            <?php endif; ?>
            <?php foreach ($involvedAccounts as $account): ?>
                <?= view('partials/account_card', ['account' => $account, 'cardWidthClass' => 'w-full']) ?>
            <?php endforeach; ?>
        </div>
            </section>
        </div>

        <div class="space-y-3">
            <section class="rounded-3xl bg-white py-4 shadow-sm">
        <div class="px-4 flex items-center justify-between">
            <h2 class="text-base font-semibold text-zinc-950">Pindah Dana</h2>
            <span class="rounded-full bg-sky-50 px-3 py-2 text-xs font-medium text-sky-700">Antar rekening / kantong</span>
        </div>
        <div class="mt-3 divide-y divide-zinc-100">
            <?php if ($transferItems === []): ?>
                <div class="px-4 pb-1">
                    <?= view('partials/empty_state', [
                        'icon' => 'swap_horiz',
                        'title' => 'Belum ada pindah dana.',
                        'description' => 'Pindah dana akan muncul di sini saat ada perpindahan antar rekening atau antar kantong pada scope ini.',
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
            'prevUrl' => route_query('kegiatan/' . $activity['project_slug'] . '/kantong/' . $pocket['slug'], ['mutasi_page' => $activityTransferPagination['prevPage'], 'transaksi_page' => $activityTransactionPagination['page']]),
            'nextUrl' => route_query('kegiatan/' . $activity['project_slug'] . '/kantong/' . $pocket['slug'], ['mutasi_page' => $activityTransferPagination['nextPage'], 'transaksi_page' => $activityTransactionPagination['page']]),
        ]) ?>
            </section>

            <section class="rounded-3xl bg-white p-4 shadow-sm">
        <div class="flex items-center justify-between">
            <h2 class="text-base font-semibold text-zinc-950">Transaksi Terakhir Kantong</h2>
            <p class="text-xs text-zinc-500"><?= esc($activity['name']) ?></p>
        </div>
        <div class="mt-3 divide-y divide-zinc-100">
            <?php if ($activityTransactions === []): ?>
                <div class="px-4 pb-1">
                    <?= view('partials/empty_state', [
                        'icon' => 'receipt_long',
                        'title' => 'Belum ada transaksi untuk kantong ini.',
                        'description' => 'Transaksi terbaru akan muncul di sini setelah ada pencatatan pada kantong ini.',
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
            'prevUrl' => route_query('kegiatan/' . $activity['project_slug'] . '/kantong/' . $pocket['slug'], ['transaksi_page' => $activityTransactionPagination['prevPage'], 'mutasi_page' => $activityTransferPagination['page']]),
            'nextUrl' => route_query('kegiatan/' . $activity['project_slug'] . '/kantong/' . $pocket['slug'], ['transaksi_page' => $activityTransactionPagination['nextPage'], 'mutasi_page' => $activityTransferPagination['page']]),
        ]) ?>
            </section>
        </div>
    </div>
</div>

<div id="pocket-settings-modal" class="<?= $openPocketSettingsModal ? '' : 'pointer-events-none opacity-0' ?> fixed inset-0 z-50 flex items-start justify-center bg-zinc-950/50 px-4 pb-6 pt-6 transition sm:pt-8" aria-hidden="<?= $openPocketSettingsModal ? 'false' : 'true' ?>">
    <div class="w-full max-w-2xl rounded-[2rem] bg-white p-5 shadow-2xl transition">
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="text-sm font-semibold text-zinc-950">Pengaturan Kantong</p>
                <p class="mt-1 text-xs text-zinc-500">
                    <?= esc($isMainPocket ? 'Atur kontrak, termin, dan catatan kantong utama.' : 'Atur nama dan catatan kantong pelaksanaan.') ?>
                </p>
            </div>
            <button type="button" data-pocket-modal-close="pocket-settings-modal" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-zinc-200 bg-white text-zinc-700">
                <span class="material-symbols-rounded text-base" aria-hidden="true">close</span>
            </button>
        </div>

        <form action="<?= esc(site_url('kegiatan/' . $activity['project_slug'] . '/kantong/' . $pocket['slug'])) ?>" method="post" class="mt-4 grid gap-3 sm:grid-cols-2">
            <?= csrf_field() ?>
            <input type="hidden" name="form_scope" value="pocket_settings">
            <?php if (! $isMainPocket): ?>
                <div class="space-y-2">
                    <label class="text-sm font-semibold text-zinc-900">Nama Kantong</label>
                    <input type="text" name="name" value="<?= esc(old('name', $pocket['name'] ?? '')) ?>" class="h-12 w-full rounded-2xl border border-zinc-100 bg-white px-4 text-sm text-zinc-950 outline-none focus:border-zinc-300 focus:ring-2 focus:ring-lime-400">
                </div>
            <?php endif; ?>
            <?php if ($isMainPocket): ?>
                <div class="space-y-2">
                    <label class="text-sm font-semibold text-zinc-900">Nilai Kontrak</label>
                    <input type="text" inputmode="numeric" name="contract_value" value="<?= esc(old('contract_value', rupiah((float) ($pocket['contract_value'] ?? 0)))) ?>" class="h-12 w-full rounded-2xl border border-zinc-100 bg-white px-4 text-sm text-zinc-950 outline-none focus:border-zinc-300 focus:ring-2 focus:ring-lime-400">
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-semibold text-zinc-900">Jumlah Termin</label>
                    <input type="number" min="0" name="contract_terms_count" value="<?= esc(old('contract_terms_count', (string) ($pocket['contract_terms_count'] ?? ''))) ?>" class="h-12 w-full rounded-2xl border border-zinc-100 bg-white px-4 text-sm text-zinc-950 outline-none focus:border-zinc-300 focus:ring-2 focus:ring-lime-400">
                </div>
            <?php endif; ?>
            <div class="space-y-2 sm:col-span-2">
                <label class="text-sm font-semibold text-zinc-900">Catatan</label>
                <textarea name="notes" rows="4" class="w-full rounded-2xl border border-zinc-100 bg-white px-4 py-3 text-sm text-zinc-950 outline-none focus:border-zinc-300 focus:ring-2 focus:ring-lime-400"><?= esc(old('notes', $pocket['notes'] ?? '')) ?></textarea>
            </div>
            <div class="sm:col-span-2 flex flex-wrap items-center justify-end gap-2 pt-1">
                <button type="button" data-pocket-modal-close="pocket-settings-modal" class="inline-flex h-11 items-center justify-center rounded-full border border-zinc-200 bg-white px-4 text-sm font-semibold text-zinc-950">Batal</button>
                <button type="submit" class="inline-flex h-11 items-center justify-center rounded-full bg-zinc-950 px-5 text-sm font-semibold text-white">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<script>
    (function () {
        var openModal = function (modalId) {
            var modal = document.getElementById(modalId);
            if (!modal) {
                return;
            }

            modal.classList.remove('pointer-events-none', 'opacity-0');
            modal.setAttribute('aria-hidden', 'false');
            document.body.classList.add('overflow-hidden');
        };

        var closeModal = function (modalId) {
            var modal = document.getElementById(modalId);
            if (!modal) {
                return;
            }

            modal.classList.add('pointer-events-none', 'opacity-0');
            modal.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('overflow-hidden');
        };

        document.querySelectorAll('[data-pocket-modal-open]').forEach(function (button) {
            button.addEventListener('click', function () {
                openModal(button.getAttribute('data-pocket-modal-open'));
            });
        });

        document.querySelectorAll('[data-pocket-modal-close]').forEach(function (button) {
            button.addEventListener('click', function () {
                closeModal(button.getAttribute('data-pocket-modal-close'));
            });
        });

        ['pocket-settings-modal'].forEach(function (modalId) {
            var modal = document.getElementById(modalId);
            if (!modal) {
                return;
            }

            modal.addEventListener('click', function (event) {
                if (event.target === modal) {
                    closeModal(modalId);
                }
            });
        });

        document.addEventListener('keydown', function (event) {
            if (event.key !== 'Escape') {
                return;
            }

            ['pocket-settings-modal'].forEach(function (modalId) {
                var modal = document.getElementById(modalId);
                if (modal && modal.getAttribute('aria-hidden') === 'false') {
                    closeModal(modalId);
                }
            });
        });

        if (<?= $openPocketSettingsModal ? 'true' : 'false' ?>) {
            document.body.classList.add('overflow-hidden');
        }
    })();
</script>
<?= $this->endSection() ?>
