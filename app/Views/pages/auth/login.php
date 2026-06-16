<?= $this->extend('layouts/auth') ?>

<?= $this->section('content') ?>
<div class="space-y-6">
    <div class="flex items-start justify-between gap-4">
        <div class="space-y-2">
            <span class="inline-flex items-center rounded-full border border-zinc-200 bg-white px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.2em] text-zinc-500 shadow-sm">
                Google Sign-In
            </span>
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-zinc-950">Masuk atau buat akun dengan Google</h1>
                <p class="mt-2 text-sm text-zinc-500">Untuk saat ini Arus memakai Google Sign-In sebagai satu-satunya pintu masuk akun.</p>
            </div>
        </div>
        <div class="h-11 w-11 overflow-hidden rounded-full border-2 border-white shadow-sm">
            <img src="<?= base_url('images/logo-primary-1.webp') ?>" alt="Logo Arus" class="h-full w-full object-cover">
        </div>
    </div>

    <div class="rounded-[2rem] border border-zinc-100 bg-white p-5 shadow-[0_20px_60px_rgba(0,0,0,0.06)]">
        <div class="space-y-4">
            <div class="rounded-3xl bg-zinc-950 p-5 text-white">
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-zinc-400">Masuk Aman</p>
                <h2 class="mt-3 text-xl font-semibold tracking-tight">Satu klik untuk lanjut ke workspace Arus Anda.</h2>
                <p class="mt-2 text-sm text-zinc-300">Akun baru akan langsung dibuatkan lembaga baru. Akun lama bisa dipetakan manual oleh admin bila diperlukan.</p>
            </div>

            <form action="<?= site_url('auth/google') ?>" method="get" class="space-y-4">
                <label class="flex items-center justify-between rounded-3xl border border-zinc-200 bg-zinc-50 px-4 py-3 shadow-sm">
                    <div>
                        <p class="text-sm font-semibold text-zinc-950">Ingat perangkat ini</p>
                        <p class="mt-1 text-xs text-zinc-500">Tetap masuk hingga 30 hari tanpa perlu login ulang.</p>
                    </div>
                    <input type="checkbox" name="remember_device" value="1" class="h-5 w-5 rounded border-zinc-300 text-zinc-950 focus:ring-zinc-950" checked>
                </label>

                <?php if (! empty($googleEnabled)) : ?>
                    <button type="submit" class="inline-flex w-full items-center justify-center gap-3 rounded-full bg-zinc-950 px-5 py-4 text-sm font-semibold text-white shadow-md transition hover:bg-zinc-800">
                        <img src="https://www.svgrepo.com/show/475656/google-color.svg" alt="Google" class="h-5 w-5">
                        <span>Lanjut dengan Google</span>
                    </button>
                <?php else : ?>
                    <button type="button" disabled class="inline-flex w-full cursor-not-allowed items-center justify-center gap-3 rounded-full border border-zinc-200 bg-zinc-100 px-5 py-4 text-sm font-semibold text-zinc-400">
                        <img src="https://www.svgrepo.com/show/475656/google-color.svg" alt="Google" class="h-5 w-5 opacity-60">
                        <span>Google Sign-In belum dikonfigurasi</span>
                    </button>
                    <p class="text-sm text-amber-600">Konfigurasi `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET`, dan `GOOGLE_REDIRECT_URI` belum lengkap di server.</p>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <div class="rounded-3xl border border-zinc-200 bg-white px-4 py-4 shadow-sm">
        <p class="text-sm font-semibold text-zinc-950">Catatan akun lama</p>
        <p class="mt-2 text-sm leading-6 text-zinc-500">Jika sebelumnya Anda memakai login OTP WhatsApp, akun tersebut tetap ada. Tautkan dulu ke Google dari halaman profil saat Anda masih login di perangkat yang aktif.</p>
    </div>
</div>
<?= $this->endSection() ?>
