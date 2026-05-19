<!doctype html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($pageTitle ?? 'Laporan Publik') ?> | Arus</title>
    <meta name="description" content="Laporan unit publik Arus dengan akses PIN.">
    <link rel="shortcut icon" type="image/webp" href="<?= base_url('images/logo-primary-2.webp') ?>">
    <?= $this->include('partials/pwa_bootstrap') ?>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .material-symbols-rounded {
            font-family: 'Material Symbols Rounded';
            font-weight: normal;
            font-style: normal;
            display: inline-block;
            line-height: 1;
            text-transform: none;
            letter-spacing: normal;
            white-space: nowrap;
            word-wrap: normal;
            direction: ltr;
            font-feature-settings: 'liga';
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
            -webkit-font-smoothing: antialiased;
        }
    </style>
</head>
<body class="bg-zinc-50 text-zinc-950">
    <div class="min-h-screen">
        <main class="mx-auto max-w-7xl px-4 py-4 sm:px-5 lg:px-6">
            <?= $this->renderSection('content') ?>
        </main>
    </div>
</body>
</html>
