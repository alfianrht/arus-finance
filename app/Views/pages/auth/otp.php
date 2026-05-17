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
                Kami telah mengirimkan kode verifikasi OTP ke No. WhatsApp Anda
            </p>
        </div>

        <form action="<?= site_url('/') ?>" method="get" class="w-full space-y-8">
            <!-- OTP Inputs -->
            <div class="flex justify-center gap-2 sm:gap-3">
                <input type="text" maxlength="1" value="4" class="h-14 w-12 sm:w-14 rounded-2xl border border-zinc-950 bg-white text-center text-2xl font-semibold text-zinc-950 shadow-sm focus:border-lime-500 focus:outline-none focus:ring-1 focus:ring-lime-500">
                <input type="text" maxlength="1" value="2" class="h-14 w-12 sm:w-14 rounded-2xl border border-zinc-950 bg-white text-center text-2xl font-semibold text-zinc-950 shadow-sm focus:border-lime-500 focus:outline-none focus:ring-1 focus:ring-lime-500">
                <input type="text" maxlength="1" value="8" class="h-14 w-12 sm:w-14 rounded-2xl border border-zinc-950 bg-white text-center text-2xl font-semibold text-zinc-950 shadow-sm focus:border-lime-500 focus:outline-none focus:ring-1 focus:ring-lime-500">
                <input type="text" maxlength="1" class="h-14 w-12 sm:w-14 rounded-2xl border border-zinc-200 bg-zinc-50 text-center text-2xl font-semibold text-zinc-950 focus:bg-white focus:border-lime-500 focus:outline-none focus:ring-1 focus:ring-lime-500">
                <input type="text" maxlength="1" class="h-14 w-12 sm:w-14 rounded-2xl border border-zinc-200 bg-zinc-50 text-center text-2xl font-semibold text-zinc-950 focus:bg-white focus:border-lime-500 focus:outline-none focus:ring-1 focus:ring-lime-500">
            </div>

            <!-- Phone Number Edit -->
            <div class="flex items-center justify-center gap-3">
                <p class="text-sm font-semibold text-zinc-700 tracking-wide">+62 812-3456-7890</p>
                <a href="<?= site_url('auth/login') ?>" class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-zinc-100 text-zinc-500 hover:bg-zinc-200 transition-colors">
                    <span class="material-symbols-rounded text-[14px]" aria-hidden="true">edit</span>
                </a>
            </div>

            <div class="space-y-3 pt-4">
                <button type="button" class="w-full rounded-full border border-zinc-200 bg-white py-4 text-sm font-semibold text-zinc-700 shadow-sm hover:bg-zinc-50 transition-colors">
                    Kirim Ulang Kode
                </button>
                <button type="submit" class="w-full rounded-full bg-zinc-950 py-4 text-sm font-semibold text-white shadow-md hover:bg-zinc-800 transition-colors">
                    Verifikasi & Masuk
                </button>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>
