<?= $this->extend('layouts/auth') ?>

<?= $this->section('content') ?>
<div class="space-y-8">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-zinc-950">Pemulihan Akun</h1>
            <p class="mt-2 text-sm text-zinc-500">Masukkan No. WhatsApp untuk menerima instruksi login atau pemulihan.</p>
        </div>
        <div class="h-10 w-10 overflow-hidden rounded-full border-2 border-white shadow-sm">
            <img src="<?= base_url('images/logo-primary-1.webp') ?>" alt="Logo" class="h-full w-full object-cover">
        </div>
    </div>

    <form action="<?= site_url('auth/otp') ?>" method="get" class="space-y-6">
        <div class="space-y-2">
            <label class="text-sm font-medium text-zinc-950">No. WhatsApp Terdaftar</label>
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
            </div>
        </div>

        <button type="submit" class="w-full rounded-full bg-zinc-950 py-4 text-sm font-semibold text-white shadow-md hover:bg-zinc-800 transition-colors">
            Kirim Kode OTP
        </button>
    </form>

    <div class="flex justify-center">
        <a href="<?= site_url('auth/login') ?>" class="inline-flex items-center gap-2 text-sm font-semibold text-zinc-500 hover:text-zinc-950 transition-colors">
            <span class="material-symbols-rounded text-base" aria-hidden="true">arrow_back</span>
            Kembali ke Masuk
        </a>
    </div>
</div>
<?= $this->endSection() ?>
