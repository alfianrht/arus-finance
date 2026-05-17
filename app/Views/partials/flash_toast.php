<?php

$flashTypes = [
    'success' => [
        'wrapper' => 'border-lime-200 bg-white text-zinc-950',
        'iconWrap' => 'bg-lime-100 text-lime-700',
        'icon' => 'check_circle',
    ],
    'warning' => [
        'wrapper' => 'border-amber-200 bg-white text-zinc-950',
        'iconWrap' => 'bg-amber-100 text-amber-700',
        'icon' => 'warning',
    ],
    'error' => [
        'wrapper' => 'border-rose-200 bg-white text-zinc-950',
        'iconWrap' => 'bg-rose-100 text-rose-700',
        'icon' => 'error',
    ],
];
?>

<div class="pointer-events-none fixed inset-x-0 top-4 z-50 flex justify-center px-4">
    <div class="w-full max-w-md space-y-2">
        <?php foreach ($flashTypes as $key => $config): ?>
            <?php $message = session()->getFlashdata($key); ?>
            <?php if (! empty($message)): ?>
                <div
                    class="pointer-events-auto flex items-start gap-3 rounded-2xl border px-3 py-3 shadow-lg transition duration-300 <?= esc($config['wrapper']) ?>"
                    data-flash-toast
                    role="status"
                    aria-live="polite"
                >
                    <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full <?= esc($config['iconWrap']) ?>">
                        <span class="material-symbols-rounded text-base" aria-hidden="true"><?= esc($config['icon']) ?></span>
                    </span>
                    <p class="min-w-0 flex-1 pt-1 text-sm font-medium leading-5"><?= esc((string) $message) ?></p>
                    <button type="button" class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-zinc-400 transition hover:bg-zinc-100 hover:text-zinc-700" data-flash-close aria-label="Tutup notifikasi">
                        <span class="material-symbols-rounded text-base" aria-hidden="true">close</span>
                    </button>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
</div>
