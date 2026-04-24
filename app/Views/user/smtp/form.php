<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<div class="row mb-3">
    <div class="col">
        <h2 class="page-title">Konfigurasi SMTP Baru</h2>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card shadow-sm border-0">
            <form action="<?= url_to('app.smtp.store') ?>" method="POST">
                <?= csrf_field() ?>
                <div class="card-body">
                    <h3 class="card-title text-primary mb-4">Identitas & Server</h3>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">Nama Pengirim</label>
                            <input type="text" name="sender_name" class="form-control" placeholder="Contoh: CS MailCore" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">Email Pengirim</label>
                            <input type="email" name="sender_email" id="sender_email" class="form-control" placeholder="email@domain.com" required>
                        </div>
                    </div>

                    <!-- Gmail Helper Alert (Hidden by default) -->
                    <div id="gmail-alert" class="alert alert-warning border-0 shadow-none bg-warning-lt mb-3" style="display: none;">
                        <div class="d-flex">
                            <div><i class="ti ti-brand-google me-2"></i></div>
                            <div>
                                <h4 class="alert-title mb-1">Terdeteksi Akun Gmail</h4>
                                <div class="text-muted small">
                                    Gunakan <b>smtp.gmail.com</b> (Port 587) dan pastikan Anda menggunakan <b>App Password</b> (16 digit), bukan password utama akun Google Anda.
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label required">SMTP Host</label>
                            <input type="text" name="smtp_host" class="form-control" placeholder="smtp.gmail.com" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label required">Port</label>
                            <input type="number" name="smtp_port" class="form-control" value="587" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label required">SMTP Username</label>
                        <input type="text" name="smtp_username" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label required">SMTP Password</label>
                        <div class="input-group input-group-flat">
                            <input type="password" name="smtp_password" id="smtp_password" class="form-control" required>
                            <span class="input-group-text">
                                <a href="#" id="togglePassword" class="link-secondary" title="Show password">
                                    <i class="ti ti-eye" id="toggleIcon"></i>
                                </a>
                            </span>
                        </div>
                        <small class="text-muted">Password Anda akan dienkripsi secara aman di database kami.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Encryption</label>
                        <div class="form-selectgroup">
                            <label class="form-selectgroup-item">
                                <input type="radio" name="encryption" value="TLS" class="form-selectgroup-input" checked>
                                <span class="form-selectgroup-label">TLS</span>
                            </label>
                            <label class="form-selectgroup-item">
                                <input type="radio" name="encryption" value="SSL" class="form-selectgroup-input">
                                <span class="form-selectgroup-label">SSL</span>
                            </label>
                            <label class="form-selectgroup-item">
                                <input type="radio" name="encryption" value="None" class="form-selectgroup-input">
                                <span class="form-selectgroup-label">None</span>
                            </label>
                        </div>
                    </div>

                    <h3 class="card-title text-primary mt-4 mb-4">Pengaturan Lanjutan (Warm-up Mode)</h3>
                    <div class="alert alert-info shadow-sm mb-3">
                        <div class="d-flex">
                            <div><i class="ti ti-flame me-2"></i></div>
                            <div>
                                <strong>Warm-up Mode</strong> sangat disarankan untuk akun SMTP baru agar reputasi domain tetap bagus dan tidak masuk SPAM.
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="warmup_mode" id="warmupToggle" value="1">
                            <span class="form-check-label form-label mb-0">Aktifkan Warm-up Mode</span>
                        </label>
                    </div>

                    <div class="mb-3" id="warmupLimitWrapper" style="display: none;">
                        <label class="form-label">Batas Pengiriman Harian (Daily Limit)</label>
                        <input type="number" name="warmup_daily_limit" class="form-control" value="50" min="10">
                        <small class="text-muted">Jika limit tercapai, antrean kampanye akan dilanjutkan otomatis esok hari.</small>
                    </div>

                </div>
                
                <script>
                    document.getElementById('warmupToggle').addEventListener('change', function() {
                        document.getElementById('warmupLimitWrapper').style.display = this.checked ? 'block' : 'none';
                    });

                    document.getElementById('sender_email').addEventListener('input', function() {
                        const email = this.value.toLowerCase();
                        const alert = document.getElementById('gmail-alert');
                        if (email.endsWith('@gmail.com')) {
                            alert.style.display = 'block';
                        } else {
                            alert.style.display = 'none';
                        }
                    });
                </script>
                <div class="card-footer text-end">
                    <a href="<?= url_to('app.smtp') ?>" class="btn btn-link link-secondary">Batal</a>
                    <button type="submit" class="btn btn-primary">Simpan Konfigurasi</button>
                </div>
            </form>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card bg-primary-lt border-0 shadow-sm">
            <div class="card-body">
                <h3 class="card-title"><i class="ti ti-help me-2"></i>Panduan Cepat</h3>
                <div class="markdown">
                    <p><b>Gmail:</b><br>Host: <code>smtp.gmail.com</code><br>Port: 587 (TLS)<br><i>*Wajib pakai App Password.</i></p>
                    <hr class="my-2">
                    <p><b>Outlook:</b><br>Host: <code>smtp-mail.outlook.com</code><br>Port: 587 (TLS)</p>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>