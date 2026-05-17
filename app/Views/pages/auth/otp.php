<?= $this->extend('layouts/auth') ?>

<?= $this->section('content') ?>
<div class="flex flex-col h-full space-y-8">
    <header class="flex items-center justify-between">
        <a href="<?= site_url('auth/login') ?>" class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-full border border-zinc-200 bg-white text-zinc-700 shadow-sm hover:bg-zinc-50" aria-label="Kembali">
            <span class="material-symbols-rounded text-base" aria-hidden="true">arrow_back</span>
        </a>
        <p class="text-base font-semibold text-zinc-950">Verifikasi OTP</p>
        <div class="w-10"></div> <!-- Spacer for center alignment -->
    </header>

    <div class="flex-1 flex flex-col items-center justify-center space-y-10 py-6">
        <!-- Icon -->
        <div class="relative flex h-28 w-28 items-center justify-center rounded-full bg-zinc-50 border-2 border-dashed border-zinc-200">
            <div class="flex h-20 w-20 items-center justify-center rounded-full bg-zinc-950 shadow-lg shadow-zinc-900/20">
                <span class="material-symbols-rounded text-4xl text-white" aria-hidden="true">phonelink_lock</span>
            </div>
        </div>

        <div class="text-center space-y-3">
            <h2 class="text-2xl font-bold tracking-tight text-zinc-950">Kode Verifikasi</h2>
            <p class="text-sm text-zinc-500 max-w-[280px] mx-auto leading-relaxed">
                Kami telah mengirimkan kode verifikasi OTP ke <?= esc('+' . ($pendingAuth['whatsapp'] ?? '62')) ?>
            </p>
            <?php if (! empty($pendingAuth['remember_device'])): ?>
                <p class="text-xs font-medium text-zinc-400">Perangkat ini akan diingat hingga 30 hari setelah login berhasil.</p>
            <?php endif; ?>
        </div>

        <form action="<?= site_url('auth/otp') ?>" method="post" class="w-full space-y-8">
            <?= csrf_field() ?>
            <div class="space-y-3">
                <div class="flex justify-center">
                    <input type="text" name="otp_code" value="<?= esc(old('otp_code', '')) ?>" maxlength="5" placeholder="Masukkan 5 digit OTP" class="h-16 w-full max-w-xs rounded-2xl border border-zinc-950 bg-white px-5 text-center text-2xl font-semibold tracking-[0.35em] text-zinc-950 shadow-sm focus:border-lime-500 focus:outline-none focus:ring-1 focus:ring-lime-500">
                </div>
                <?php if (! empty($otpPreview)): ?>
                    <p class="text-center text-xs font-medium text-lime-700">OTP development: <?= esc((string) $otpPreview) ?></p>
                <?php endif; ?>
            </div>

            <div class="flex items-center justify-center gap-3">
                <p class="text-sm font-semibold text-zinc-700 tracking-wide"><?= esc('+' . ($pendingAuth['whatsapp'] ?? '62')) ?></p>
                <a href="<?= site_url('auth/login') ?>" class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-zinc-100 text-zinc-500 hover:bg-zinc-200 transition-colors">
                    <span class="material-symbols-rounded text-[14px]" aria-hidden="true">edit</span>
                </a>
            </div>

            <div class="space-y-3 pt-4">
                <button type="submit" class="w-full rounded-full bg-zinc-950 py-4 text-sm font-semibold text-white shadow-md hover:bg-zinc-800 transition-colors">
                    Verifikasi & Masuk
                </button>
            </div>
        </form>

        <form action="<?= site_url('auth/otp/resend') ?>" method="post" class="w-full">
            <?= csrf_field() ?>
            <button type="submit" class="w-full rounded-full border border-zinc-200 bg-white py-4 text-sm font-semibold text-zinc-700 shadow-sm hover:bg-zinc-50 transition-colors">
                Kirim Ulang Kode
            </button>
        </form>
    </div>
</div>
<?= $this->endSection() ?>
