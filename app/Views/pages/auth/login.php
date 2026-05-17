<?= $this->extend('layouts/auth') ?>

<?= $this->section('content') ?>
<div class="space-y-8">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-zinc-950">Masuk Akun</h1>
            <p class="mt-2 text-sm text-zinc-500">Halo, selamat datang kembali di Arus!</p>
        </div>
        <div class="h-10 w-10 overflow-hidden rounded-full border-2 border-white shadow-sm">
            <img src="<?= base_url('images/logo-primary-1.webp') ?>" alt="Logo" class="h-full w-full object-cover">
        </div>
    </div>

    <!-- Toggle Email / Phone -->
    <div class="flex rounded-full bg-zinc-100 p-1">
        <button type="button" class="flex-1 rounded-full px-4 py-2.5 text-sm font-medium text-zinc-500 hover:text-zinc-950">Email</button>
        <button type="button" class="flex-1 rounded-full bg-white px-4 py-2.5 text-sm font-semibold text-zinc-950 shadow-sm">No. WhatsApp</button>
    </div>

    <form action="<?= site_url('auth/otp') ?>" method="get" class="space-y-6">
        <div class="space-y-2">
            <label class="text-sm font-medium text-zinc-950">No. WhatsApp</label>
            <div class="flex rounded-2xl border border-zinc-200 bg-white focus-within:border-lime-400 focus-within:ring-1 focus-within:ring-lime-400 shadow-sm transition-shadow">
                <div class="relative flex items-center">
                    <select class="h-14 appearance-none rounded-l-2xl border-0 bg-transparent pl-4 pr-8 text-base font-medium text-zinc-950 focus:ring-0">
                        <option>🇮🇩 +62</option>
                        <option>🇺🇸 +1</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2">
                        <span class="material-symbols-rounded text-xl text-zinc-400" aria-hidden="true">expand_more</span>
                    </div>
                </div>
                <div class="w-px bg-zinc-200 my-3"></div>
                <input type="tel" placeholder="812-3456-7890" class="h-14 flex-1 border-0 bg-transparent px-4 text-base font-medium text-zinc-950 placeholder-zinc-400 focus:ring-0">
                <div class="flex items-center pr-4">
                    <span class="material-symbols-rounded text-lime-500" aria-hidden="true">check_circle</span>
                </div>
            </div>
        </div>

        <button type="submit" class="w-full rounded-full bg-zinc-950 py-4 text-sm font-semibold text-white shadow-md hover:bg-zinc-800 transition-colors">
            Kirim Kode OTP
        </button>

        <div class="relative flex items-center justify-center py-2">
            <div class="absolute inset-0 flex items-center">
                <div class="w-full border-t border-zinc-200"></div>
            </div>
            <span class="relative bg-zinc-50 px-4 text-xs font-medium text-zinc-400 uppercase tracking-wide">Atau masuk dengan</span>
        </div>

        <div class="space-y-3">
            <button type="button" class="flex w-full items-center justify-center gap-3 rounded-full border border-zinc-200 bg-white py-3.5 text-sm font-medium text-zinc-700 shadow-sm hover:bg-zinc-50 transition-colors">
                <img src="https://www.svgrepo.com/show/475656/google-color.svg" alt="Google" class="h-5 w-5">
                Google
            </button>
            <button type="button" class="flex w-full items-center justify-center gap-3 rounded-full border border-zinc-200 bg-white py-3.5 text-sm font-medium text-zinc-700 shadow-sm hover:bg-zinc-50 transition-colors">
                <img src="https://www.svgrepo.com/show/475647/facebook-color.svg" alt="Facebook" class="h-5 w-5">
                Facebook
            </button>
        </div>
    </form>

    <p class="text-center text-sm text-zinc-500">
        Belum punya akun? <a href="<?= site_url('auth/register') ?>" class="font-semibold text-lime-600 hover:text-lime-700">Daftar Sekarang</a>
    </p>
    <div class="flex justify-center">
        <a href="<?= site_url('auth/forgot-password') ?>" class="text-xs font-medium text-zinc-400 hover:text-zinc-600 transition-colors">Masalah masuk / Lupa Sandi?</a>
    </div>
</div>
<?= $this->endSection() ?>
