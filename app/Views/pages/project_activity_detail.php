<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<?php $surfaceText = surface_label($activity['short_name'] ?? $activity['name']); ?>
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

        <div class="relative mt-6">
            <p class="text-xs font-medium uppercase tracking-wide text-zinc-400">Saldo Total Semua Kantong</p>
            <p class="mt-2 text-4xl font-black tracking-tight text-white"><?= esc(rupiah($projectSummary['total_pocket_balance'] ?? 0)) ?></p>
            <p class="mt-2 text-sm text-zinc-300">
                <?= esc((string) ($projectSummary['transaction_count'] ?? 0)) ?> transaksi ·
                <?= esc((string) ($projectSummary['execution_pocket_count'] ?? 0)) ?> kantong pelaksanaan
            </p>
        </div>

        <p class="pointer-events-none absolute -bottom-3 left-4 text-7xl font-black uppercase tracking-tight text-white/10" aria-hidden="true"><?= esc($surfaceText) ?></p>

        <div class="relative mt-4 grid grid-cols-2 gap-3 border-t border-white/10 pt-4 sm:grid-cols-4">
            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-zinc-500">Nilai Kontrak</p>
                <p class="mt-1 text-sm font-semibold text-white"><?= esc(rupiah($projectSummary['contract_value'] ?? 0)) ?></p>
            </div>
            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-zinc-500">Termin Cair</p>
                <p class="mt-1 text-sm font-semibold text-white"><?= esc(rupiah($projectSummary['termin_cair'] ?? 0)) ?></p>
            </div>
            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-zinc-500">Sisa Tagihan</p>
                <p class="mt-1 text-sm font-semibold text-white"><?= esc(rupiah($projectSummary['outstanding'] ?? 0)) ?></p>
            </div>
            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-zinc-500">Jumlah Termin</p>
                <p class="mt-1 text-sm font-semibold text-white"><?= esc((string) ($projectSummary['contract_terms_count'] ?? 0)) ?></p>
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
        <div class="flex items-center justify-between gap-3">
            <div>
                <h2 class="text-base font-semibold text-zinc-950">Pengaturan Proyek</h2>
                <p class="mt-1 text-xs text-zinc-500">Kontrak dan termin disimpan di Kantong Utama, bukan di kegiatan induk.</p>
            </div>
            <span class="rounded-full bg-zinc-100 px-3 py-2 text-xs font-medium text-zinc-700">Kantong Utama</span>
        </div>
        <form action="<?= esc(site_url('kegiatan/' . $activity['slug'] . '/proyek')) ?>" method="post" class="mt-4 grid gap-3 sm:grid-cols-2">
            <?= csrf_field() ?>
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
                <textarea name="notes" rows="3" class="w-full rounded-2xl border border-zinc-100 bg-white px-4 py-3 text-sm text-zinc-950 outline-none focus:border-zinc-300 focus:ring-2 focus:ring-lime-400" placeholder="Contoh: rumah utama kontrak, termin, dan sisa tagihan."><?= esc(old('notes', $mainPocket['notes'] ?? '')) ?></textarea>
            </div>
            <div class="sm:col-span-2">
                <button type="submit" class="inline-flex h-12 items-center justify-center rounded-full bg-zinc-950 px-5 text-sm font-semibold text-white shadow-sm">Simpan Pengaturan Proyek</button>
            </div>
        </form>
    </section>

    <section class="space-y-3">
        <div class="flex items-center justify-between gap-3">
            <h2 class="text-base font-semibold text-zinc-950">Daftar Kantong</h2>
            <span class="text-xs text-zinc-500">Rumah saldo dan transaksi per scope</span>
        </div>

        <?php foreach ($pocketCards as $pocketCard): ?>
            <?php $surface = surface_label($pocketCard['short_name'] ?? $pocketCard['name']); ?>
            <article class="relative pb-2">
                <div class="relative z-10 overflow-hidden rounded-3xl bg-zinc-950 p-4 text-white shadow-sm">
                    <div class="absolute inset-0 bg-white/5" aria-hidden="true"></div>
                    <div class="relative flex items-start justify-between gap-4">
                        <div class="min-w-0">
                            <p class="text-xs font-medium uppercase tracking-wide text-zinc-400"><?= esc($pocketCard['type_label']) ?></p>
                            <p class="mt-1.5 text-base font-semibold leading-tight text-white"><?= esc($pocketCard['name']) ?></p>
                            <p class="mt-1 text-xs text-zinc-400"><?= esc($activity['name']) ?></p>
                        </div>
                        <p class="text-lg font-black uppercase tracking-tight text-white"><?= esc($surface) ?></p>
                    </div>

                    <div class="relative mt-4">
                        <p class="text-xs font-medium uppercase tracking-wide text-zinc-400">Saldo Kantong</p>
                        <p class="mt-1.5 text-2xl font-black tracking-tight text-white"><?= esc(rupiah($pocketCard['balance'])) ?></p>
                        <p class="mt-2 text-xs text-zinc-300"><?= esc($pocketCard['transaction_count']) ?> transaksi terkait</p>
                    </div>

                    <p class="pointer-events-none absolute bottom-12 left-3 text-6xl font-black uppercase tracking-tight text-white/10" aria-hidden="true"><?= esc($surface) ?></p>

                    <div class="relative mt-4 grid grid-cols-3 gap-2 border-t border-white/10 pt-3">
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wide text-zinc-500">Masuk</p>
                            <p class="mt-1 text-xs font-semibold text-white"><?= esc(rupiah($pocketCard['income'])) ?></p>
                        </div>
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wide text-zinc-500">Keluar</p>
                            <p class="mt-1 text-xs font-semibold text-white"><?= esc(rupiah($pocketCard['expense'])) ?></p>
                        </div>
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wide text-zinc-500">Pindah</p>
                            <p class="mt-1 text-xs font-semibold text-white"><?= esc(rupiah($pocketCard['transfer_out'])) ?></p>
                        </div>
                    </div>
                </div>

                <div class="relative -mt-5 rounded-[1.4rem] border border-zinc-100 bg-white px-4 py-3 pt-[26px] shadow-sm">
                    <div class="flex items-center justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-zinc-950"><?= esc($pocketCard['type_label']) ?></p>
                            <p class="mt-1 text-xs text-zinc-500"><?= esc($pocketCard['notes'] !== '' ? $pocketCard['notes'] : 'Buka detail kantong untuk melihat penerima, rekening, pindah dana, dan transaksi terakhir.') ?></p>
                        </div>
                        <div class="flex shrink-0 items-center gap-2">
                            <a href="<?= esc($pocketCard['detail_url']) ?>" class="rounded-full border border-zinc-200 bg-white px-3 py-2 text-xs font-semibold text-zinc-950">Lihat Kantong</a>
                            <?php if (($pocketCard['pocket_type'] ?? '') === 'execution'): ?>
                                <form action="<?= esc($pocketCard['deactivate_url']) ?>" method="post">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="rounded-full bg-zinc-950 px-3 py-2 text-xs font-semibold text-white">Nonaktifkan</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
    </section>

    <section class="rounded-3xl bg-white p-4 shadow-sm">
        <div class="flex items-center justify-between gap-3">
            <div>
                <h2 class="text-base font-semibold text-zinc-950">Tambah Kantong Pelaksanaan</h2>
                <p class="mt-1 text-xs text-zinc-500">Dipakai untuk biaya, honor, dan saldo operasional yang harus dipisah dari kontrak utama.</p>
            </div>
            <span class="rounded-full bg-lime-400 px-3 py-2 text-xs font-semibold text-zinc-950">Eksekusi</span>
        </div>
        <form action="<?= esc(site_url('kegiatan/' . $activity['slug'] . '/kantong')) ?>" method="post" class="mt-4 grid gap-3 sm:grid-cols-2">
            <?= csrf_field() ?>
            <div class="space-y-2">
                <label class="text-sm font-semibold text-zinc-900">Nama Kantong</label>
                <input type="text" name="name" value="<?= esc(old('name', '')) ?>" class="h-12 w-full rounded-2xl border border-zinc-100 bg-white px-4 text-sm text-zinc-950 outline-none focus:border-zinc-300 focus:ring-2 focus:ring-lime-400" placeholder="Contoh: WPA Asessor">
            </div>
            <div class="space-y-2 sm:col-span-2">
                <label class="text-sm font-semibold text-zinc-900">Catatan Singkat</label>
                <textarea name="notes" rows="3" class="w-full rounded-2xl border border-zinc-100 bg-white px-4 py-3 text-sm text-zinc-950 outline-none focus:border-zinc-300 focus:ring-2 focus:ring-lime-400" placeholder="Contoh: khusus biaya pelaksanaan batch 1."><?= esc(old('notes', '')) ?></textarea>
            </div>
            <div class="sm:col-span-2">
                <button type="submit" class="inline-flex h-12 items-center justify-center rounded-full bg-zinc-950 px-5 text-sm font-semibold text-white shadow-sm">Tambah Kantong Pelaksanaan</button>
            </div>
        </form>
    </section>

    <section class="rounded-3xl bg-white p-4 shadow-sm">
        <div class="flex items-center justify-between">
            <h2 class="text-base font-semibold text-zinc-950">Transaksi Terakhir Proyek</h2>
            <p class="text-xs text-zinc-500"><?= esc($activity['name']) ?></p>
        </div>
        <div class="mt-3 divide-y divide-zinc-100">
            <?php if ($projectTransactions === []): ?>
                <div class="px-4 pb-1">
                    <?= view('partials/empty_state', [
                        'icon' => 'receipt_long',
                        'title' => 'Belum ada transaksi pada proyek ini.',
                        'description' => 'Transaksi proyek akan muncul di sini setelah ada pencatatan pada salah satu kantong.',
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
<?= $this->endSection() ?>
