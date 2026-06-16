<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<?php
$surfaceText = surface_label($unit['short_name'] ?? $unit['name']);
$isEnabled = (bool) ($shareState['is_enabled'] ?? false);
$hasPin = (bool) ($shareState['has_pin'] ?? false);
$latestPin = trim((string) ($latestPin ?? ''));
$showPinModal = $latestPin !== '';
?>
<div class="space-y-3">
    <?= view('partials/top_nav_back', [
        'title' => 'Bagikan Laporan',
        'subtitle' => 'Unit / Program',
        'backUrl' => $backUrl,
    ]) ?>

    <section class="rounded-3xl border border-zinc-100 bg-white p-4 shadow-sm">
        <div class="flex items-start justify-between gap-4">
            <div class="min-w-0">
                <span class="inline-flex rounded-full bg-lime-100 px-3 py-1.5 text-xs font-medium text-zinc-950">Akses Publik dengan PIN</span>
                <p class="mt-3 text-2xl font-semibold leading-tight tracking-tight text-zinc-950"><?= esc($unit['name']) ?></p>
                <p class="mt-1.5 text-sm text-zinc-500">Satu link publik aktif untuk laporan unit ini. Akses dibuka dengan PIN dan diingat per browser.</p>
            </div>
            <span class="shrink-0 rounded-full border border-zinc-200 bg-white px-3 py-1.5 text-xs font-medium text-zinc-700"><?= esc($surfaceText) ?></span>
        </div>
        <div class="mt-4 grid grid-cols-2 gap-2 sm:grid-cols-4">
            <div class="rounded-2xl border border-zinc-100 bg-zinc-50 px-3.5 py-2.5">
                <p class="text-xs text-zinc-500">Status</p>
                <p class="mt-0.5 text-sm font-semibold text-zinc-950"><?= $isEnabled ? 'Aktif' : 'Nonaktif' ?></p>
            </div>
            <div class="rounded-2xl border border-zinc-100 bg-zinc-50 px-3.5 py-2.5">
                <p class="text-xs text-zinc-500">PIN</p>
                <p class="mt-0.5 text-sm font-semibold text-zinc-950"><?= $hasPin ? 'Tersimpan aman' : 'Belum dibuat' ?></p>
            </div>
            <div class="rounded-2xl border border-zinc-100 bg-zinc-50 px-3.5 py-2.5">
                <p class="text-xs text-zinc-500">Akses</p>
                <p class="mt-0.5 text-sm font-semibold text-zinc-950">Publik + PIN</p>
            </div>
            <div class="rounded-2xl border border-zinc-100 bg-zinc-50 px-3.5 py-2.5">
                <p class="text-xs text-zinc-500">Mode</p>
                <p class="mt-0.5 text-sm font-semibold text-zinc-950">View only</p>
            </div>
        </div>
    </section>

    <section class="rounded-3xl border border-zinc-100 bg-white p-4 shadow-sm">
        <div class="flex items-center justify-between gap-3">
            <h2 class="text-base font-semibold text-zinc-950">Tautan Publik</h2>
            <div class="flex items-center gap-2">
                <button type="button" onclick="window.open('<?= esc($previewUrl, 'js') ?>', '_blank', 'noopener')" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-zinc-200 bg-white text-zinc-950" aria-label="Preview internal">
                    <span class="material-symbols-rounded text-base" aria-hidden="true">preview</span>
                </button>
                <button type="button" onclick="copyToClipboard('<?= esc($shareUrl, 'js') ?>', this)" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-zinc-200 bg-white text-zinc-950" aria-label="Salin link">
                    <span class="material-symbols-rounded text-base" aria-hidden="true">content_copy</span>
                </button>
            </div>
        </div>
        <div class="mt-3 rounded-2xl bg-zinc-50 p-4">
            <p class="text-xs font-medium uppercase tracking-wide text-zinc-500">URL Laporan</p>
            <p class="mt-2 break-all text-sm font-semibold text-zinc-950"><?= esc($shareUrl) ?></p>
        </div>
        <div class="mt-3 flex flex-wrap gap-2">
            <?php if (! $isEnabled): ?>
                <button type="button" data-share-modal-open="enable-share-modal" class="inline-flex h-12 items-center justify-center gap-2 rounded-full bg-zinc-950 px-5 text-sm font-semibold text-white">
                    <span class="material-symbols-rounded text-base" aria-hidden="true">shield_lock</span>
                    <span>Aktifkan Share</span>
                </button>
            <?php else: ?>
                <button type="button" data-share-modal-open="rotate-pin-modal" class="inline-flex h-12 items-center justify-center gap-2 rounded-full bg-zinc-950 px-5 text-sm font-semibold text-white">
                    <span class="material-symbols-rounded text-base" aria-hidden="true">password</span>
                    <span>Ganti PIN</span>
                </button>
                <form action="<?= esc(site_url('unit/' . $unit['slug'] . '/bagikan')) ?>" method="post" class="contents">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="disable">
                    <button type="submit" class="inline-flex h-12 items-center justify-center gap-2 rounded-full border border-zinc-200 bg-white px-5 text-sm font-semibold text-zinc-950">
                        <span class="material-symbols-rounded text-base" aria-hidden="true">block</span>
                        <span>Nonaktifkan</span>
                    </button>
                </form>
            <?php endif; ?>
        </div>
        <p class="mt-3 text-xs text-zinc-500">Preview internal selalu bisa dibuka dari akun admin. Link publik biasa hanya bisa dibuka jika share aktif dan PIN benar.</p>
    </section>

    <section class="rounded-3xl border border-zinc-100 bg-white p-4 shadow-sm">
        <h2 class="text-base font-semibold text-zinc-950">Yang Terbuka ke Publik</h2>
        <div class="mt-3 grid gap-2">
            <?php foreach ([
                ['icon' => 'account_balance_wallet', 'title' => 'Ringkasan Unit', 'text' => 'Saldo, masuk, keluar, dan laba unit atau kegiatan yang dipilih.'],
                ['icon' => 'folder_supervised', 'title' => 'Daftar Kegiatan', 'text' => 'Pengunjung bisa mengganti scope dari unit ke kegiatan tertentu.'],
                ['icon' => 'receipt_long', 'title' => 'Transaksi', 'text' => 'Daftar transaksi tampil dengan filter jenis dan pagination 10 data.'],
                ['icon' => 'visibility_off', 'title' => 'Data Disembunyikan', 'text' => 'Preview bukti transaksi dan detail rekening sensitif tidak ikut ditampilkan.'],
            ] as $item): ?>
                <div class="flex items-start gap-3 rounded-2xl bg-zinc-50 px-3 py-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-white text-zinc-950">
                        <span class="material-symbols-rounded text-[20px]" aria-hidden="true"><?= esc($item['icon']) ?></span>
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-zinc-950"><?= esc($item['title']) ?></p>
                        <p class="mt-0.5 text-xs text-zinc-500"><?= esc($item['text']) ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
</div>

<div id="enable-share-modal" class="pointer-events-none fixed inset-0 z-50 flex items-start justify-center bg-zinc-950/50 px-4 pb-6 pt-6 opacity-0 transition sm:pt-8" aria-hidden="true">
    <div class="w-full max-w-lg rounded-[2rem] bg-white p-5 shadow-2xl transition">
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="text-sm font-semibold text-zinc-950">Aktifkan Share Publik</p>
                <p class="mt-1 text-xs text-zinc-500">Sistem akan membuat PIN 6 digit baru dan menampilkannya sekali setelah disimpan.</p>
            </div>
            <button type="button" data-share-modal-close="enable-share-modal" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-zinc-200 bg-white text-zinc-700">
                <span class="material-symbols-rounded text-base" aria-hidden="true">close</span>
            </button>
        </div>
        <form action="<?= esc(site_url('unit/' . $unit['slug'] . '/bagikan')) ?>" method="post" class="mt-4 flex flex-wrap items-center justify-end gap-2">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="enable">
            <button type="button" data-share-modal-close="enable-share-modal" class="inline-flex h-11 items-center justify-center rounded-full border border-zinc-200 bg-white px-4 text-sm font-semibold text-zinc-950">Batal</button>
            <button type="submit" class="inline-flex h-11 items-center justify-center rounded-full bg-zinc-950 px-5 text-sm font-semibold text-white">Aktifkan & Buat PIN</button>
        </form>
    </div>
</div>

<div id="rotate-pin-modal" class="pointer-events-none fixed inset-0 z-50 flex items-start justify-center bg-zinc-950/50 px-4 pb-6 pt-6 opacity-0 transition sm:pt-8" aria-hidden="true">
    <div class="w-full max-w-lg rounded-[2rem] bg-white p-5 shadow-2xl transition">
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="text-sm font-semibold text-zinc-950">Ganti PIN Akses</p>
                <p class="mt-1 text-xs text-zinc-500">PIN lama akan langsung tidak berlaku. Sistem membuat PIN 6 digit baru dan menampilkannya sekali.</p>
            </div>
            <button type="button" data-share-modal-close="rotate-pin-modal" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-zinc-200 bg-white text-zinc-700">
                <span class="material-symbols-rounded text-base" aria-hidden="true">close</span>
            </button>
        </div>
        <form action="<?= esc(site_url('unit/' . $unit['slug'] . '/bagikan')) ?>" method="post" class="mt-4 flex flex-wrap items-center justify-end gap-2">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="rotate_pin">
            <button type="button" data-share-modal-close="rotate-pin-modal" class="inline-flex h-11 items-center justify-center rounded-full border border-zinc-200 bg-white px-4 text-sm font-semibold text-zinc-950">Batal</button>
            <button type="submit" class="inline-flex h-11 items-center justify-center rounded-full bg-zinc-950 px-5 text-sm font-semibold text-white">Generate PIN Baru</button>
        </form>
    </div>
</div>

<div id="share-pin-result-modal" class="<?= $showPinModal ? '' : 'pointer-events-none opacity-0' ?> fixed inset-0 z-50 flex items-start justify-center bg-zinc-950/50 px-4 pb-6 pt-6 transition sm:pt-8" aria-hidden="<?= $showPinModal ? 'false' : 'true' ?>">
    <div class="w-full max-w-lg rounded-[2rem] bg-white p-5 shadow-2xl transition">
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="text-sm font-semibold text-zinc-950">PIN Baru Siap Dipakai</p>
                <p class="mt-1 text-xs text-zinc-500">PIN ini hanya ditampilkan sekali. Simpan lalu bagikan ke penerima link yang berwenang.</p>
            </div>
            <button type="button" data-share-modal-close="share-pin-result-modal" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-zinc-200 bg-white text-zinc-700">
                <span class="material-symbols-rounded text-base" aria-hidden="true">close</span>
            </button>
        </div>
        <div class="mt-4 rounded-3xl border border-zinc-200 bg-zinc-50 px-4 py-4 text-center">
            <p class="text-xs font-medium uppercase tracking-wide text-zinc-500">PIN Akses</p>
            <p class="mt-2 text-3xl font-black tracking-[0.3em] text-zinc-950"><?= esc($latestPin !== '' ? $latestPin : '------') ?></p>
        </div>
        <div class="mt-4 flex flex-wrap items-center justify-end gap-2">
            <button type="button" onclick="copyToClipboard('<?= esc($latestPin, 'js') ?>', this)" class="inline-flex h-11 items-center justify-center gap-2 rounded-full border border-zinc-200 bg-white px-4 text-sm font-semibold text-zinc-950">
                <span class="material-symbols-rounded text-base" aria-hidden="true">content_copy</span>
                <span>Salin PIN</span>
            </button>
            <button type="button" data-share-modal-close="share-pin-result-modal" class="inline-flex h-11 items-center justify-center rounded-full bg-zinc-950 px-5 text-sm font-semibold text-white">Selesai</button>
        </div>
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

        document.querySelectorAll('[data-share-modal-open]').forEach(function (button) {
            button.addEventListener('click', function () {
                openModal(button.getAttribute('data-share-modal-open'));
            });
        });

        document.querySelectorAll('[data-share-modal-close]').forEach(function (button) {
            button.addEventListener('click', function () {
                closeModal(button.getAttribute('data-share-modal-close'));
            });
        });

        ['enable-share-modal', 'rotate-pin-modal', 'share-pin-result-modal'].forEach(function (modalId) {
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

            ['enable-share-modal', 'rotate-pin-modal', 'share-pin-result-modal'].forEach(function (modalId) {
                var modal = document.getElementById(modalId);
                if (modal && modal.getAttribute('aria-hidden') === 'false') {
                    closeModal(modalId);
                }
            });
        });

        if (<?= $showPinModal ? 'true' : 'false' ?>) {
            document.body.classList.add('overflow-hidden');
        }
    })();
</script>
<?= $this->endSection() ?>
