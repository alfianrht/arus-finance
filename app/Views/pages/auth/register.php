<?= $this->extend('layouts/auth') ?>

<?= $this->section('content') ?>
<div class="space-y-8">
    <?= $this->include('partials/auth_flash') ?>

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-zinc-950">Daftar Akun</h1>
            <p class="mt-2 text-sm text-zinc-500">Mulai kelola keuangan lembaga Anda hari ini.</p>
        </div>
        <div class="h-10 w-10 overflow-hidden rounded-full border-2 border-white shadow-sm">
            <img src="<?= base_url('images/logo-primary-1.webp') ?>" alt="Logo" class="h-full w-full object-cover">
        </div>
    </div>

    <form action="<?= site_url('auth/request-otp') ?>" method="post" class="space-y-6">
        <?= csrf_field() ?>
        <input type="hidden" name="flow" value="register">
        <div class="space-y-4">
            <div class="space-y-2">
                <label class="text-sm font-medium text-zinc-950">Nama Lengkap</label>
                <div class="flex rounded-2xl border border-zinc-200 bg-white focus-within:border-lime-400 focus-within:ring-1 focus-within:ring-lime-400 shadow-sm transition-shadow">
                    <input type="text" name="name" value="<?= esc(old('name', '')) ?>" placeholder="Cago Andalas" class="h-14 flex-1 border-0 bg-transparent px-4 text-base font-medium text-zinc-950 placeholder-zinc-400 focus:ring-0">
                </div>
            </div>

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
                    <input type="tel" name="whatsapp" value="<?= esc(old('whatsapp', '')) ?>" placeholder="812-3456-7890" class="h-14 flex-1 border-0 bg-transparent px-4 text-base font-medium text-zinc-950 placeholder-zinc-400 focus:ring-0">
                </div>
            </div>
        </div>

        <label class="flex items-center justify-between rounded-2xl border border-zinc-200 bg-white px-4 py-3 shadow-sm">
            <div>
                <p class="text-sm font-semibold text-zinc-950">Ingat perangkat ini</p>
                <p class="mt-1 text-xs text-zinc-500">Setelah verifikasi OTP, perangkat tetap login hingga 30 hari.</p>
            </div>
            <input type="checkbox" name="remember_device" value="1" class="h-5 w-5 rounded border-zinc-300 text-zinc-950 focus:ring-zinc-950" <?= old('remember_device') !== null ? 'checked' : 'checked' ?>>
        </label>

        <button type="submit" class="w-full rounded-full bg-zinc-950 py-4 text-sm font-semibold text-white shadow-md hover:bg-zinc-800 transition-colors">
            Daftar & Kirim OTP
        </button>

        <div class="relative flex items-center justify-center py-2">
            <div class="absolute inset-0 flex items-center">
                <div class="w-full border-t border-zinc-200"></div>
            </div>
            <span class="relative bg-zinc-50 px-4 text-xs font-medium text-zinc-400 uppercase tracking-wide">Atau daftar dengan</span>
        </div>

        <div class="space-y-3">
            <button type="button" class="flex w-full items-center justify-center gap-3 rounded-full border border-zinc-200 bg-white py-3.5 text-sm font-medium text-zinc-700 shadow-sm hover:bg-zinc-50 transition-colors">
                <img src="https://www.svgrepo.com/show/475656/google-color.svg" alt="Google" class="h-5 w-5">
                Google
            </button>
        </div>
    </form>

    <p class="text-center text-sm text-zinc-500">
        Sudah punya akun? <a href="<?= site_url('auth/login') ?>" class="font-semibold text-lime-600 hover:text-lime-700">Masuk</a>
    </p>
</div>
<?= $this->endSection() ?>
