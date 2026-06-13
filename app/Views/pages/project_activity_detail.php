<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<?php
$surfaceText = surface_label($activity['short_name'] ?? $activity['name']);
$openProjectModal = old('form_scope') === 'project_settings';
$openPocketModal = old('form_scope') === 'add_execution_pocket';
?>
<div class="space-y-3">
    <?= view('partials/top_nav_back', [
        'title' => $activity['name'],
        'subtitle' => $unit['name'] . ' · Mode kantong proyek aktif',
        'backUrl' => $backUrl ?? site_url('rekap'),
    ]) ?>

    <section class="relative overflow-hidden rounded-3xl bg-zinc-950 p-5 text-white shadow-sm">
        <div class="absolute inset-0 bg-white/5" aria-hidden="true"></div>
        <div class="relative flex items-start justify-between gap-4">
            <div class="min-w-0">
                <p class="text-xs font-medium uppercase tracking-wide text-zinc-400">Kegiatan Proyek</p>
                <p class="mt-2 text-2xl font-semibold leading-tight text-white"><?= esc($activity['name']) ?></p>
                <p class="mt-2 text-sm text-zinc-400"><?= esc($unit['name']) ?></p>
            </div>
            <span class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/5 px-3 py-2 text-xs font-semibold text-white">
                <span class="material-symbols-rounded text-sm" aria-hidden="true">folder_managed</span>
                <span>Kantong Aktif</span>
            </span>
        </div>

        <div class="relative mt-5">
            <p class="text-xs font-medium uppercase tracking-wide text-zinc-400">Saldo Total Semua Kantong</p>
            <p class="mt-1.5 text-[2rem] font-black tracking-tight text-white sm:text-4xl"><?= esc(rupiah($projectSummary['total_pocket_balance'] ?? 0)) ?></p>
            <p class="mt-1 text-sm text-zinc-300">
                <?= esc((string) ($projectSummary['transaction_count'] ?? 0)) ?> transaksi ·
                <?= esc((string) ($projectSummary['execution_pocket_count'] ?? 0)) ?> kantong pelaksanaan
            </p>
        </div>

        <p class="pointer-events-none absolute -bottom-3 left-4 text-7xl font-black uppercase tracking-tight text-white/10" aria-hidden="true"><?= esc($surfaceText) ?></p>

        <div class="relative mt-4 grid grid-cols-3 gap-2 border-t border-white/10 pt-4">
            <div>
                <p class="text-[10px] font-medium uppercase tracking-wide text-zinc-500">Kontrak</p>
                <p class="mt-1 text-xs font-semibold text-white"><?= esc(rupiah($projectSummary['contract_value'] ?? 0)) ?></p>
            </div>
            <div>
                <p class="text-[10px] font-medium uppercase tracking-wide text-zinc-500">Termin Cair</p>
                <p class="mt-1 text-xs font-semibold text-white"><?= esc(rupiah($projectSummary['termin_cair'] ?? 0)) ?></p>
            </div>
            <div>
                <p class="text-[10px] font-medium uppercase tracking-wide text-zinc-500">Sisa</p>
                <p class="mt-1 text-xs font-semibold text-white"><?= esc(rupiah($projectSummary['outstanding'] ?? 0)) ?></p>
            </div>
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
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="rounded-full bg-lime-400 px-3 py-1.5 text-[11px] font-semibold text-zinc-950">Unit · <?= esc($unit['name']) ?></span>
                    <span class="rounded-full bg-zinc-950 px-3 py-1.5 text-[11px] font-semibold text-white">Kegiatan · <?= esc($activity['name']) ?></span>
                    <span class="rounded-full border border-zinc-950/10 bg-white px-3 py-1.5 text-[11px] font-semibold text-zinc-700"><?= esc((string) ($projectSummary['execution_pocket_count'] ?? 0)) ?> kantong pelaksanaan</span>
                </div>
                <p class="mt-3 text-sm font-semibold text-zinc-950">Ringkasan Proyek</p>
                <p class="mt-1 text-xs text-zinc-500">Kontrak dan termin disimpan di Kantong Utama, sedangkan biaya operasional dipisahkan ke kantong pelaksanaan.</p>
            </div>

            <div class="flex flex-wrap items-center gap-2 sm:justify-end">
                <button type="button" data-modal-open="project-settings-modal" class="inline-flex h-10 items-center justify-center rounded-full border border-zinc-200 bg-white px-4 text-sm font-semibold text-zinc-950 shadow-sm">
                    Atur Proyek
                </button>
                <button type="button" data-modal-open="add-pocket-modal" class="inline-flex h-10 items-center justify-center rounded-full bg-zinc-950 px-4 text-sm font-semibold text-white shadow-sm">
                    Tambah Kantong
                </button>
            </div>
        </div>

        <div class="mt-4 grid grid-cols-3 gap-2">
            <div class="rounded-2xl border border-zinc-100 bg-zinc-50 px-3 py-2.5">
                <p class="text-[10px] font-medium uppercase tracking-wide text-zinc-500">Kontrak</p>
                <p class="mt-1 text-sm font-semibold text-zinc-950"><?= esc(rupiah($projectSummary['contract_value'] ?? 0)) ?></p>
            </div>
            <div class="rounded-2xl border border-zinc-100 bg-zinc-50 px-3 py-2.5">
                <p class="text-[10px] font-medium uppercase tracking-wide text-zinc-500">Termin</p>
                <p class="mt-1 text-sm font-semibold text-zinc-950"><?= esc((string) ($projectSummary['contract_terms_count'] ?? 0)) ?></p>
            </div>
            <div class="rounded-2xl border border-zinc-100 bg-zinc-50 px-3 py-2.5">
                <p class="text-[10px] font-medium uppercase tracking-wide text-zinc-500">Sisa</p>
                <p class="mt-1 text-sm font-semibold text-zinc-950"><?= esc(rupiah($projectSummary['outstanding'] ?? 0)) ?></p>
            </div>
        </div>
    </section>

    <section class="rounded-3xl bg-white p-4 shadow-sm">
        <div class="flex items-center justify-between gap-3">
            <div>
                <h2 class="text-base font-semibold text-zinc-950">Daftar Kantong</h2>
                <p class="mt-1 text-xs text-zinc-500">Setiap kantong punya detail sendiri. Halaman ini hanya menampilkan ringkasan scope.</p>
            </div>
            <span class="rounded-full border border-zinc-200 bg-white px-3 py-1.5 text-[11px] font-semibold text-zinc-700"><?= esc((string) count($pocketCards)) ?> kantong</span>
        </div>

        <div class="mt-4 space-y-2.5">
            <?php foreach ($pocketCards as $pocketCard): ?>
                <article class="rounded-[1.35rem] border border-zinc-950/10 bg-white px-4 py-3 shadow-sm">
                    <div class="flex flex-col gap-3 lg:grid lg:grid-cols-[minmax(0,1.3fr)_minmax(0,1fr)_auto] lg:items-center">
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="rounded-full border border-zinc-950/15 bg-white px-2.5 py-1 text-[10px] font-semibold uppercase tracking-wide text-zinc-700"><?= esc($pocketCard['type_label']) ?></span>
                                <?php if (!($pocketCard['is_active'] ?? true)): ?>
                                    <span class="rounded-full bg-rose-50 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-wide text-rose-600">Nonaktif</span>
                                <?php endif; ?>
                            </div>
                            <p class="mt-2 truncate text-sm font-semibold text-zinc-950"><?= esc($pocketCard['name']) ?></p>
                            <p class="mt-1 text-[11px] text-zinc-500">
                                <?= esc((string) $pocketCard['transaction_count']) ?> transaksi
                                <?php if (($pocketCard['notes'] ?? '') !== ''): ?>
                                    · <?= esc(mb_strimwidth($pocketCard['notes'], 0, 70, '...')) ?>
                                <?php endif; ?>
                            </p>
                        </div>

                        <div class="grid grid-cols-2 gap-2 sm:grid-cols-3">
                            <div class="rounded-2xl border border-zinc-100 bg-zinc-50 px-3 py-2">
                                <p class="text-[10px] font-medium uppercase tracking-wide text-zinc-500">Masuk</p>
                                <p class="mt-1 text-xs font-semibold text-zinc-950"><?= esc(rupiah($pocketCard['income'])) ?></p>
                            </div>
                            <div class="rounded-2xl border border-zinc-100 bg-zinc-50 px-3 py-2">
                                <p class="text-[10px] font-medium uppercase tracking-wide text-zinc-500">Keluar</p>
                                <p class="mt-1 text-xs font-semibold text-zinc-950"><?= esc(rupiah($pocketCard['expense'])) ?></p>
                            </div>
                            <div class="rounded-2xl border border-zinc-100 bg-zinc-50 px-3 py-2 sm:col-span-1 col-span-2">
                                <p class="text-[10px] font-medium uppercase tracking-wide text-zinc-500">Saldo</p>
                                <p class="mt-1 text-sm font-semibold text-zinc-950"><?= esc(rupiah($pocketCard['balance'])) ?></p>
                            </div>
                        </div>

                        <div class="flex items-center justify-between gap-2 lg:flex-col lg:items-end">
                            <a href="<?= esc($pocketCard['detail_url']) ?>" class="inline-flex h-10 items-center justify-center rounded-full border border-zinc-950 bg-white px-4 text-xs font-semibold text-zinc-950 shadow-sm">
                                Lihat Kantong
                            </a>
                            <?php if (($pocketCard['pocket_type'] ?? '') === 'execution'): ?>
                                <form action="<?= esc($pocketCard['deactivate_url']) ?>" method="post">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="inline-flex h-9 items-center justify-center rounded-full bg-zinc-950 px-3 text-[11px] font-semibold text-white">Nonaktifkan</button>
                                </form>
                            <?php else: ?>
                                <span class="inline-flex h-9 items-center justify-center rounded-full border border-zinc-200 bg-zinc-50 px-3 text-[11px] font-semibold text-zinc-500">Kantong Utama</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="rounded-3xl bg-white p-4 shadow-sm">
        <div class="flex items-center justify-between">
            <h2 class="text-base font-semibold text-zinc-950">Transaksi Kegiatan</h2>
            <p class="text-xs text-zinc-500"><?= esc($activity['name']) ?></p>
        </div>
        <div class="mt-3 divide-y divide-zinc-100">
            <?php if ($projectTransactions === []): ?>
                <div class="px-4 pb-1">
                    <?= view('partials/empty_state', [
                        'icon' => 'receipt_long',
                        'title' => 'Belum ada transaksi pada proyek ini.',
                        'description' => 'Transaksi kegiatan akan muncul di sini setelah ada pencatatan pada salah satu kantong.',
                        'compact' => true,
                    ]) ?>
                </div>
            <?php endif; ?>
            <?php foreach ($projectTransactions as $transaction): ?>
                <?= view('partials/transaction_item', ['transaction' => $transaction]) ?>
            <?php endforeach; ?>
        </div>
        <?= view('partials/pagination_controls', [
            'pagination' => $projectTransactionPagination,
            'prevUrl' => route_query('kegiatan/' . $activity['slug'], ['periode' => $projectFilters['periode'], 'unit' => $projectFilters['unit'], 'transaksi_page' => $projectTransactionPagination['prevPage']]),
            'nextUrl' => route_query('kegiatan/' . $activity['slug'], ['periode' => $projectFilters['periode'], 'unit' => $projectFilters['unit'], 'transaksi_page' => $projectTransactionPagination['nextPage']]),
        ]) ?>
    </section>
</div>

<div id="project-settings-modal" class="<?= $openProjectModal ? '' : 'pointer-events-none opacity-0' ?> fixed inset-0 z-50 flex items-end justify-center bg-zinc-950/50 px-4 py-6 transition sm:items-center" aria-hidden="<?= $openProjectModal ? 'false' : 'true' ?>">
    <div class="w-full max-w-2xl rounded-[2rem] bg-white p-5 shadow-2xl transition">
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="text-sm font-semibold text-zinc-950">Pengaturan Proyek</p>
                <p class="mt-1 text-xs text-zinc-500">Kontrak dan termin disimpan di Kantong Utama.</p>
            </div>
            <button type="button" data-modal-close="project-settings-modal" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-zinc-200 bg-white text-zinc-700">
                <span class="material-symbols-rounded text-base" aria-hidden="true">close</span>
            </button>
        </div>

        <form action="<?= esc(site_url('kegiatan/' . $activity['slug'] . '/proyek')) ?>" method="post" class="mt-4 grid gap-3 sm:grid-cols-2">
            <?= csrf_field() ?>
            <input type="hidden" name="form_scope" value="project_settings">
            <div class="space-y-2">
                <label class="text-sm font-semibold text-zinc-900">Nilai Kontrak</label>
                <input type="text" inputmode="numeric" name="contract_value" value="<?= esc(old('contract_value', rupiah((float) ($mainPocket['contract_value'] ?? 0)))) ?>" class="h-12 w-full rounded-2xl border border-zinc-100 bg-white px-4 text-sm text-zinc-950 outline-none focus:border-zinc-300 focus:ring-2 focus:ring-lime-400" placeholder="Rp 0">
            </div>
            <div class="space-y-2">
                <label class="text-sm font-semibold text-zinc-900">Jumlah Termin</label>
                <input type="number" min="0" name="contract_terms_count" value="<?= esc(old('contract_terms_count', (string) ($mainPocket['contract_terms_count'] ?? ''))) ?>" class="h-12 w-full rounded-2xl border border-zinc-100 bg-white px-4 text-sm text-zinc-950 outline-none focus:border-zinc-300 focus:ring-2 focus:ring-lime-400" placeholder="0">
            </div>
            <div class="space-y-2 sm:col-span-2">
                <label class="text-sm font-semibold text-zinc-900">Catatan Kantong Utama</label>
                <textarea name="notes" rows="4" class="w-full rounded-2xl border border-zinc-100 bg-white px-4 py-3 text-sm text-zinc-950 outline-none focus:border-zinc-300 focus:ring-2 focus:ring-lime-400" placeholder="Contoh: rumah utama kontrak, termin, dan sisa tagihan."><?= esc(old('notes', $mainPocket['notes'] ?? '')) ?></textarea>
            </div>
            <div class="sm:col-span-2 flex flex-wrap items-center justify-end gap-2 pt-1">
                <button type="button" data-modal-close="project-settings-modal" class="inline-flex h-11 items-center justify-center rounded-full border border-zinc-200 bg-white px-4 text-sm font-semibold text-zinc-950">Batal</button>
                <button type="submit" class="inline-flex h-11 items-center justify-center rounded-full bg-zinc-950 px-5 text-sm font-semibold text-white">Simpan Pengaturan Proyek</button>
            </div>
        </form>
    </div>
</div>

<div id="add-pocket-modal" class="<?= $openPocketModal ? '' : 'pointer-events-none opacity-0' ?> fixed inset-0 z-50 flex items-end justify-center bg-zinc-950/50 px-4 py-6 transition sm:items-center" aria-hidden="<?= $openPocketModal ? 'false' : 'true' ?>">
    <div class="w-full max-w-xl rounded-[2rem] bg-white p-5 shadow-2xl transition">
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="text-sm font-semibold text-zinc-950">Tambah Kantong Pelaksanaan</p>
                <p class="mt-1 text-xs text-zinc-500">Dipakai untuk biaya, honor, dan saldo operasional yang harus dipisah dari kontrak utama.</p>
            </div>
            <button type="button" data-modal-close="add-pocket-modal" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-zinc-200 bg-white text-zinc-700">
                <span class="material-symbols-rounded text-base" aria-hidden="true">close</span>
            </button>
        </div>

        <form action="<?= esc(site_url('kegiatan/' . $activity['slug'] . '/kantong')) ?>" method="post" class="mt-4 space-y-3">
            <?= csrf_field() ?>
            <input type="hidden" name="form_scope" value="add_execution_pocket">
            <div class="space-y-2">
                <label class="text-sm font-semibold text-zinc-900">Nama Kantong</label>
                <input type="text" name="name" value="<?= esc(old('name', '')) ?>" class="h-12 w-full rounded-2xl border border-zinc-100 bg-white px-4 text-sm text-zinc-950 outline-none focus:border-zinc-300 focus:ring-2 focus:ring-lime-400" placeholder="Contoh: WPA Asessor">
            </div>
            <div class="space-y-2">
                <label class="text-sm font-semibold text-zinc-900">Catatan Singkat</label>
                <textarea name="notes" rows="4" class="w-full rounded-2xl border border-zinc-100 bg-white px-4 py-3 text-sm text-zinc-950 outline-none focus:border-zinc-300 focus:ring-2 focus:ring-lime-400" placeholder="Contoh: khusus biaya pelaksanaan batch 1."><?= esc(old('notes', '')) ?></textarea>
            </div>
            <div class="flex flex-wrap items-center justify-end gap-2 pt-1">
                <button type="button" data-modal-close="add-pocket-modal" class="inline-flex h-11 items-center justify-center rounded-full border border-zinc-200 bg-white px-4 text-sm font-semibold text-zinc-950">Batal</button>
                <button type="submit" class="inline-flex h-11 items-center justify-center rounded-full bg-zinc-950 px-5 text-sm font-semibold text-white">Tambah Kantong</button>
            </div>
        </form>
    </div>
</div>

<script>
    (function () {
        const openModal = function (modalId) {
            const modal = document.getElementById(modalId);
            if (!modal) {
                return;
            }

            modal.classList.remove('pointer-events-none', 'opacity-0');
            modal.setAttribute('aria-hidden', 'false');
            document.body.classList.add('overflow-hidden');
        };

        const closeModal = function (modalId) {
            const modal = document.getElementById(modalId);
            if (!modal) {
                return;
            }

            modal.classList.add('pointer-events-none', 'opacity-0');
            modal.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('overflow-hidden');
        };

        document.querySelectorAll('[data-modal-open]').forEach(function (button) {
            button.addEventListener('click', function () {
                openModal(button.getAttribute('data-modal-open'));
            });
        });

        document.querySelectorAll('[data-modal-close]').forEach(function (button) {
            button.addEventListener('click', function () {
                closeModal(button.getAttribute('data-modal-close'));
            });
        });

        ['project-settings-modal', 'add-pocket-modal'].forEach(function (modalId) {
            const modal = document.getElementById(modalId);
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

            ['project-settings-modal', 'add-pocket-modal'].forEach(function (modalId) {
                const modal = document.getElementById(modalId);
                if (modal && modal.getAttribute('aria-hidden') === 'false') {
                    closeModal(modalId);
                }
            });
        });

        if (<?= $openProjectModal ? 'true' : 'false' ?> || <?= $openPocketModal ? 'true' : 'false' ?>) {
            document.body.classList.add('overflow-hidden');
        }
    })();
</script>
<?= $this->endSection() ?>
