<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>404 - Halaman Tidak Ditemukan</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/core@latest/dist/css/tabler.min.css">
</head>
<body class="border-top-wide border-primary d-flex flex-column">
    <div class="page page-center">
        <div class="container-tight py-4">
            <div class="empty">
                <div class="empty-header">404</div>
                <p class="empty-title">Ups... Halaman Tidak Ditemukan</p>
                <p class="empty-subtitle text-muted">
                    Maaf, halaman yang Anda cari tidak ada atau telah dipindahkan.
                </p>
                <div class="empty-action">
                    <a href="<?= site_url() ?>" class="btn btn-primary">
                        <i class="ti ti-arrow-left me-2"></i> Kembali ke Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>