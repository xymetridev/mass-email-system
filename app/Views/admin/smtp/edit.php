<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<div class="row mb-3 align-items-center">
    <div class="col">
        <h2 class="page-title">Edit Akun SMTP</h2>
    </div>
</div>

<div class="card shadow-sm">
    <form action="<?= url_to('admin.smtp.update', $account->id) ?>" method="POST">
        <?= csrf_field() ?>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Nama Pengirim</label>
                        <input type="text" name="sender_name" class="form-control" value="<?= esc($account->sender_name) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email Pengirim</label>
                        <input type="email" name="sender_email" class="form-control" value="<?= esc($account->sender_email) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">SMTP Host</label>
                        <input type="text" name="smtp_host" class="form-control" value="<?= esc($account->smtp_host) ?>" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">SMTP Port</label>
                        <input type="number" name="smtp_port" class="form-control" value="<?= esc($account->smtp_port) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Encryption</label>
                        <select name="encryption" class="form-select">
                            <option value="TLS" <?= $account->encryption == 'TLS' ? 'selected' : '' ?>>TLS</option>
                            <option value="SSL" <?= $account->encryption == 'SSL' ? 'selected' : '' ?>>SSL</option>
                            <option value="None" <?= $account->encryption == 'None' ? 'selected' : '' ?>>None</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password SMTP (Kosongkan jika tidak ingin ganti)</label>
                        <div class="input-group input-group-flat">
                            <input type="password" name="smtp_password" id="smtp_password" class="form-control" placeholder="••••••••">
                            <span class="input-group-text">
                                <a href="#" class="link-secondary" title="Tampilkan password" id="togglePassword" data-bs-toggle="tooltip">
                                    <i class="ti ti-eye" id="toggleIcon"></i>
                                </a>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-12">
                    <h4 class="text-primary mb-3"><i class="ti ti-flame me-2"></i>Pengaturan Lanjutan (Warm-up Mode)</h4>
                    <div class="mb-3">
                        <label class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="warmup_mode" id="warmupToggle" value="1" <?= $account->warmup_mode ? 'checked' : '' ?>>
                            <span class="form-check-label form-label mb-0">Aktifkan Warm-up Mode untuk akun ini</span>
                        </label>
                    </div>

                    <div class="mb-3 col-md-4" id="warmupLimitWrapper" style="display: <?= $account->warmup_mode ? 'block' : 'none' ?>;">
                        <label class="form-label">Batas Pengiriman Harian (Daily Limit)</label>
                        <input type="number" name="warmup_daily_limit" class="form-control" value="<?= $account->warmup_daily_limit ?: 50 ?>" min="10">
                        <small class="text-muted">Telah mengirim <b><?= $account->warmup_sent_today ?></b> email hari ini.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Hourly Throttling</label>
                        <input type="number" name="hourly_limit" class="form-control" value="<?= $account->hourly_limit ?>">
                        <small class="text-muted">Maksimal email per jam (0 = Unset).</small>
                    </div>

                    <div class="hr-text">Pengaturan Bounce (IMAP)</div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">IMAP Host</label>
                            <input type="text" name="imap_host" class="form-control" value="<?= $account->imap_host ?>">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Port</label>
                            <input type="number" name="imap_port" class="form-control" value="<?= $account->imap_port ?>">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Encryption</label>
                            <select name="imap_encryption" class="form-select">
                                <option value="SSL" <?= $account->imap_encryption == 'SSL' ? 'selected' : '' ?>>SSL</option>
                                <option value="TLS" <?= $account->imap_encryption == 'TLS' ? 'selected' : '' ?>>TLS</option>
                                <option value="None" <?= $account->imap_encryption == 'None' ? 'selected' : '' ?>>None</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <script>
            document.getElementById('warmupToggle').addEventListener('change', function() {
                document.getElementById('warmupLimitWrapper').style.display = this.checked ? 'block' : 'none';
            });
        </script>
        <div class="card-footer text-end">
            <a href="<?= url_to('admin.smtp.index') ?>" class="btn btn-link link-secondary">Batal</a>
            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
        </div>
    </form>
</div>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const toggleBtn = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('smtp_password');
        const toggleIcon = document.getElementById('toggleIcon');

        if (toggleBtn && passwordInput) {
            toggleBtn.addEventListener('click', function (e) {
                e.preventDefault(); // Mencegah halaman melompat ke atas saat link diklik
                
                // Cek tipe saat ini, lalu balikkan
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);

                // Ubah Icon
                if (type === 'text') {
                    toggleIcon.classList.remove('ti-eye');
                    toggleIcon.classList.add('ti-eye-off'); // Icon mata dicoret
                    this.setAttribute('title', 'Sembunyikan password');
                } else {
                    toggleIcon.classList.remove('ti-eye-off');
                    toggleIcon.classList.add('ti-eye'); // Icon mata normal
                    this.setAttribute('title', 'Tampilkan password');
                }
            });
        }
    });
</script>
<?= $this->endSection() ?>