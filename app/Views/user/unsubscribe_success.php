<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Berhasil Berhenti Berlangganan</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0-beta21/dist/css/tabler.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@2.47.0/tabler-icons.min.css">
</head>
<body class="d-flex flex-column bg-body-tertiary">
<div class="page page-center">
    <div class="container container-tight py-4">
        <div class="card card-md shadow-sm text-center">
            <div class="card-body p-5">
                <span class="avatar avatar-xl rounded bg-success-lt mb-4">
                    <i class="ti ti-mail-off fs-1 text-success"></i>
                </span>
                <h2 class="h2 mb-4">Berhenti Berlangganan Sukses</h2>
                <p class="text-muted">
                    Email <strong><?= esc($email) ?></strong> telah berhasil dihapus dari daftar pengiriman kami. Anda tidak akan menerima email promosi dari kami lagi.
                </p>
                <div class="mt-4 text-muted small">
                    Terima kasih atas waktu Anda selama ini.
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
