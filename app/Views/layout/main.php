<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($pageTitle ?? 'Dashboard') ?> | Mass Email System</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0-beta21/dist/css/tabler.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@2.47.0/tabler-icons.min.css">
    <style>
        @import url('https://rsms.me/inter/inter.css');
        :root { --tblr-font-sans-serif: 'Inter Var', -apple-system, BlinkMacSystemFont, San Francisco, Segoe UI, Roboto, Helvetica Neue, sans-serif; }
        body { font-feature-settings: "cv03", "cv04", "cv11"; }
        .page-wrapper { min-height: 100vh; display: flex; flex-direction: column; }
        .footer { margin-top: auto; }
    </style>
    <script>
        (function () {
            const key = 'mes-theme';
            const theme = localStorage.getItem(key) || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
            document.documentElement.setAttribute('data-bs-theme', theme);
        })();
    </script>
</head>
<body>
    <div class="page">
        <?= $this->include('layout/sidebar') ?>
        
        <div class="page-wrapper">
            <?= $this->include('layout/header') ?>
            
            <main class="page-body">
                <div class="container-xl">
                        <?php if (session()->getFlashdata('success')) : ?>
                            <div class="alert alert-important alert-success alert-dismissible shadow-sm mb-3" role="alert">
                                <div class="d-flex">
                                    <div><i class="ti ti-check icon alert-icon"></i></div>
                                    <div><?= session()->getFlashdata('success') ?></div>
                                </div>
                                <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
                            </div>
                        <?php endif; ?>

                        <?php if (session()->getFlashdata('errors')) : ?>
                            <div class="alert alert-important alert-danger alert-dismissible shadow-sm mb-3" role="alert">
                                <div class="d-flex">
                                    <div><i class="ti ti-alert-triangle icon alert-icon"></i></div>
                                    <div>
                                        <?php foreach (session()->getFlashdata('errors') as $error) : ?>
                                            <li><?= esc($error) ?></li>
                                        <?php endforeach ?>
                                    </div>
                                </div>
                                <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
                            </div>
                        <?php endif; ?>

                        <?php if (session()->has('email_debug')) : ?>
                            <div class="alert alert-important alert-danger alert-dismissible shadow-sm mb-4" role="alert">
                                <div class="d-flex">
                                    <div>
                                        <i class="ti ti-bug me-2" style="font-size: 1.2rem;"></i>
                                    </div>
                                    <div>
                                        <h4 class="alert-title">Detail Log SMTP (Debugging)</h4>
                                        <div class="text-muted mt-1">
                                            Analisis log di bawah untuk menemukan penyebab kegagalan koneksi:
                                        </div>
                                        <div class="mt-3">
                                            <pre class="bg-dark text-white p-3 rounded-2 border-0" style="max-height: 300px; overflow-y: auto; font-size: 11px; line-height: 1.5;">
                                                <?= session('email_debug') ?>
                                            </pre>
                                        </div>
                                    </div>
                                </div>
                                <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
                            </div>
                        <?php endif; ?>
                    <?= $this->renderSection('content') ?>
                </div>
            </main>

            <?= $this->include('layout/footer') ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0-beta17/dist/js/tabler.min.js"></script>
    
    <!-- Modal Konfirmasi Global -->
    <!-- Modal Konfirmasi & Alert Global ala Tabler -->
    <div class="modal modal-blur fade" id="modal-global" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
            <div class="modal-content">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                <div class="modal-status bg-danger" id="global-status-bar"></div>
                <div class="modal-body text-center py-4">
                    <div id="global-icon-wrapper" class="mb-2">
                        <i class="ti ti-alert-triangle text-danger display-4" id="global-icon"></i>
                    </div>
                    <h3 id="global-title">Judul</h3>
                    <div class="text-muted" id="global-text">Konten pesan di sini.</div>
                </div>
                <div class="modal-footer" id="global-footer">
                    <div class="w-100">
                        <div class="row" id="global-footer-row">
                            <div class="col" id="global-cancel-col"><a href="#" class="btn w-100" data-bs-dismiss="modal">Batal</a></div>
                            <div class="col"><a href="#" class="btn btn-danger w-100" id="global-btn-ok">OK</a></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // HELPER MODAL GLOBAL (Alert & Confirm)
        window.showAlert = function(title, text, type = 'info') {
            const modalEl = document.getElementById('modal-global');
            const modal = new bootstrap.Modal(modalEl);
            
            document.getElementById('global-title').innerText = title;
            document.getElementById('global-text').innerText = text;
            
            // UI elements
            const statusBar = document.getElementById('global-status-bar');
            const icon = document.getElementById('global-icon');
            const btnOk = document.getElementById('global-btn-ok');
            const cancelCol = document.getElementById('global-cancel-col');
            
            // Set Color Theme
            statusBar.className = `modal-status bg-${type}`;
            btnOk.className = `btn btn-${type} w-100`;
            cancelCol.style.display = 'none'; // Sembunyikan tombol batal untuk Alert biasa
            
            // Set Icon based on type
            let iconClass = 'ti-info-circle';
            if (type === 'danger') iconClass = 'ti-alert-circle';
            if (type === 'success') iconClass = 'ti-circle-check';
            if (type === 'warning') iconClass = 'ti-alert-triangle';
            icon.className = `ti ${iconClass} text-${type} display-4`;

            btnOk.onclick = function() { modal.hide(); };
            modal.show();
        };

        window.confirmAction = function(options) {
            const modalEl = document.getElementById('modal-global');
            const modal = new bootstrap.Modal(modalEl);
            
            document.getElementById('global-title').innerText = options.title || 'Konfirmasi';
            document.getElementById('global-text').innerText = options.text || 'Apakah Anda yakin?';
            
            const statusBar = document.getElementById('global-status-bar');
            const icon = document.getElementById('global-icon');
            const btnOk = document.getElementById('global-btn-ok');
            const cancelCol = document.getElementById('global-cancel-col');
            
            const type = options.type || 'danger';
            statusBar.className = `modal-status bg-${type}`;
            btnOk.className = `btn btn-${type} w-100`;
            btnOk.innerText = options.confirmText || 'Ya, Lanjutkan';
            cancelCol.style.display = 'block'; 
            
            icon.className = `ti ti-alert-triangle text-${type} display-4`;
            
            btnOk.onclick = function(e) {
                e.preventDefault();
                modal.hide();
                if (typeof options.onConfirm === 'function') options.onConfirm();
            };
            
            modal.show();
        };

        document.addEventListener("DOMContentLoaded", function() {
            const key = 'mes-theme';
            const buttons = document.querySelectorAll('.theme-toggle');
            
            const applyTheme = (theme) => {
                document.documentElement.setAttribute('data-bs-theme', theme);
                localStorage.setItem(key, theme);
                buttons.forEach(btn => {
                    btn.innerHTML = theme === 'dark' ? '<i class="ti ti-sun"></i>' : '<i class="ti ti-moon"></i>';
                });
            };

            buttons.forEach(button => {
                button.addEventListener('click', () => {
                    const current = document.documentElement.getAttribute('data-bs-theme');
                    applyTheme(current === 'dark' ? 'light' : 'dark');
                });
            });

            // Set initial button state
            const currentTheme = document.documentElement.getAttribute('data-bs-theme');
            buttons.forEach(btn => {
                btn.innerHTML = currentTheme === 'dark' ? '<i class="ti ti-sun"></i>' : '<i class="ti ti-moon"></i>';
            });
        });
    </script>
</body>
</html>