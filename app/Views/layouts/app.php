<!doctype html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($pageTitle ?? 'Arus') ?> | <?= esc($appName ?? 'Arus') ?></title>
    <meta name="description" content="Prototype mobile-first Arus untuk pencatatan keuangan harian.">
    <link rel="shortcut icon" type="image/webp" href="<?= base_url('images/logo-primary-1.webp') ?>">
    <link rel="apple-touch-icon" href="<?= base_url('images/logo-primary-1.webp') ?>">
    <meta property="og:image" content="<?= base_url('images/logo-primary-1.webp') ?>">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0">
    <script>
        const arusUiScale = 0.875;
        document.documentElement.style.fontSize = `${16 * arusUiScale}px`;

        const tailwindRemScale = 1;
        const scaleRemValue = (value) => `${(value * tailwindRemScale).toFixed(4).replace(/0+$/, '').replace(/\.$/, '')}rem`;
        const buildFont = (size, lineHeight, letterSpacing = null) => [
            scaleRemValue(size),
            {
                lineHeight: typeof lineHeight === 'number' ? scaleRemValue(lineHeight) : lineHeight,
                ...(letterSpacing !== null ? { letterSpacing } : {}),
            },
        ];
        const spacingScale = {
            px: '1px',
            0: '0px',
            '0.5': 0.125,
            1: 0.25,
            '1.5': 0.375,
            2: 0.5,
            '2.5': 0.625,
            3: 0.75,
            '3.5': 0.875,
            4: 1,
            5: 1.25,
            6: 1.5,
            7: 1.75,
            8: 2,
            9: 2.25,
            10: 2.5,
            11: 2.75,
            12: 3,
            14: 3.5,
            16: 4,
            20: 5,
            24: 6,
            28: 7,
            32: 8,
            36: 9,
            40: 10,
            44: 11,
            48: 12,
            52: 13,
            56: 14,
            60: 15,
            64: 16,
            72: 18,
            80: 20,
            96: 24,
        };

        window.tailwind = window.tailwind || {};
        window.tailwind.config = {
            theme: {
                spacing: Object.fromEntries(
                    Object.entries(spacingScale).map(([key, value]) => [key, typeof value === 'number' ? scaleRemValue(value) : value])
                ),
                fontSize: {
                    xs: buildFont(0.75, 1),
                    sm: buildFont(0.875, 1.25),
                    base: buildFont(1, 1.5),
                    lg: buildFont(1.125, 1.75),
                    xl: buildFont(1.25, 1.75),
                    '2xl': buildFont(1.5, 2),
                    '3xl': buildFont(1.875, 2.25),
                    '4xl': buildFont(2.25, 2.5),
                    '5xl': buildFont(3, 1),
                    '6xl': buildFont(3.75, 1),
                    '7xl': buildFont(4.5, 1),
                    '8xl': buildFont(6, 1),
                    '9xl': buildFont(8, 1),
                },
            },
        };
    </script>
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
    <div class="min-h-screen bg-zinc-50">
        <main class="mx-auto max-w-4xl px-4 pb-28 pt-4 sm:px-5 md:px-6 md:pt-5">
            <?= $this->renderSection('content') ?>
        </main>
    </div>

    <?= $this->include('partials/bottom_nav') ?>
</body>
</html>
