<?php

$flashTypes = [
    'success' => [
        'wrapper' => 'border-lime-300 bg-lime-50 text-lime-950',
        'icon' => 'check_circle',
    ],
    'warning' => [
        'wrapper' => 'border-amber-300 bg-amber-50 text-amber-950',
        'icon' => 'warning',
    ],
    'error' => [
        'wrapper' => 'border-rose-300 bg-rose-50 text-rose-950',
        'icon' => 'error',
    ],
];
?>

<?php foreach ($flashTypes as $key => $config): ?>
    <?php $message = session()->getFlashdata($key); ?>
    <?php if (! empty($message)): ?>
        <div class="flex items-start gap-3 rounded-3xl border px-4 py-3 shadow-sm <?= esc($config['wrapper']) ?>">
            <span class="material-symbols-rounded mt-0.5 text-lg" aria-hidden="true"><?= esc($config['icon']) ?></span>
            <p class="text-sm font-medium leading-6"><?= esc($message) ?></p>
        </div>
    <?php endif; ?>
<?php endforeach; ?>
