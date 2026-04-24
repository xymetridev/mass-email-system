<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light dark">
    <title><?= $this->renderSection('title') ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0-beta21/dist/css/tabler.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@2.47.0/tabler-icons.min.css">
    <script>
        (function () {
            const key = 'mes-theme';
            const stored = localStorage.getItem(key);
            const systemDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            const theme = stored || (systemDark ? 'dark' : 'light');
            document.documentElement.setAttribute('data-bs-theme', theme);
        })();
    </script>
</head>
<body class="d-flex flex-column bg-body-tertiary">
<div class="page page-center">
    <div class="container container-tight py-4">
        <div class="text-center mb-4">
            <a href="<?= site_url('/') ?>" class="navbar-brand navbar-brand-autodark">
                <span class="navbar-brand-text">Mass Email System</span>
            </a>
        </div>
        <?= $this->renderSection('main') ?>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0-beta17/dist/js/tabler.min.js"></script>
</body>
</html>
