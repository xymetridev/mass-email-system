<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<div class="row mb-3 align-items-center">
    <div class="col">
        <ol class="breadcrumb" aria-label="breadcrumbs">
            <li class="breadcrumb-item"><a href="<?= url_to('admin.smtp.index') ?>">Pengaturan SMTP</a></li>
            <li class="breadcrumb-item active" aria-current="page">Tambah Akun</li>
        </ol>
        <h2 class="page-title mt-2">Tambah Akun SMTP Baru</h2>
    </div>
</div>

<?php if (session()->has('errors')) : ?>
    <div class="alert alert-danger alert-important" role="alert">
        <ul class="mb-0">
            <?php foreach (session('errors') as $error) : ?>
                <li><?= esc($error) ?></li>
            <?php endforeach ?>
        </ul>
    </div>
<?php endif ?>

<div class="card shadow-sm">
    <form action="<?= site_url('admin/smtp/store') ?>" method="POST">
        <?= csrf_field() ?>
        
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 border-end-md">
                    <h4 class="text-primary mb-3"><i class="ti ti-id me-2"></i>Identitas Pengirim</h4>
                    
                    <div class="mb-3">
                        <label class="form-label required">Nama Pengirim</label>
                        <input type="text" name="sender_name" class="form-control" placeholder="Misal: Tim Marketing / Info Promo" value="<?= old('sender_name') ?>" required>
                        <small class="form-hint">Nama ini akan muncul di kotak masuk penerima.</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label required">Email Pengirim</label>
                        <input type="email" name="sender_email" id="sender_email" class="form-control" placeholder="Misal: marketing@domain.com" value="<?= old('sender_email') ?>" required>
                    </div>

                    <!-- Gmail Helper Alert -->
                    <div id="gmail-alert" class="alert alert-warning border-0 shadow-none bg-warning-lt mb-3" style="display: none;">
                        <div class="d-flex small">
                            <div><i class="ti ti-brand-google me-2"></i></div>
                            <div>
                                <strong>Akun Gmail Terdeteksi:</strong> Gunakan Host <b>smtp.gmail.com</b> dan pastikan memakai <b>App Password</b> (16 digit).
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 ps-md-4">
                    <h4 class="text-primary mb-3"><i class="ti ti-server-cog me-2"></i>Kredensial Server</h4>
                    
                    <div class="mb-3">
                        <label class="form-label required">SMTP Host</label>
                        <input type="text" name="smtp_host" class="form-control" placeholder="Misal: smtp.mailtrap.io atau smtp.gmail.com" value="<?= old('smtp_host') ?>" required>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">SMTP Port</label>
                            <input type="number" name="smtp_port" class="form-control" placeholder="Misal: 587 / 465 / 25" value="<?= old('smtp_port', 587) ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">Enkripsi</label>
                            <select name="encryption" class="form-select" required>
                                <option value="TLS" <?= old('encryption') == 'TLS' ? 'selected' : '' ?>>TLS (Rekomendasi)</option>
                                <option value="SSL" <?= old('encryption') == 'SSL' ? 'selected' : '' ?>>SSL</option>
                                <option value="None" <?= old('encryption') == 'None' ? 'selected' : '' ?>>Tanpa Enkripsi</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label required">SMTP Username</label>
                        <input type="text" name="smtp_username" class="form-control" placeholder="Username SMTP Anda" value="<?= old('smtp_username') ?>" required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label required">SMTP Password</label>
                        <div class="input-group input-group-flat">
                            <input type="password" name="smtp_password" id="smtp_password" class="form-control" placeholder="Masukkan password SMTP" required>
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
                    <div class="card bg-primary-lt border-0 shadow-sm mb-3">
                        <div class="card-body py-2">
                            <i class="ti ti-info-circle me-1"></i> 
                            <strong>Warm-up Mode</strong> membantu menjaga reputasi domain dengan membatasi jumlah pengiriman harian agar tidak terdeteksi sebagai spammer oleh provider email.
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="warmup_mode" id="warmupToggle" value="1">
                            <span class="form-check-label form-label mb-0">Aktifkan Warm-up Mode untuk akun ini</span>
                        </label>
                    </div>

                    <div class="mb-3 col-md-4" id="warmupLimitWrapper" style="display: none;">
                        <label class="form-label">Batas Pengiriman Harian (Daily Limit)</label>
                        <input type="number" name="warmup_daily_limit" class="form-control" value="50" min="10">
                        <small class="text-muted">Sistem akan berhenti mengirim menggunakan akun ini jika limit tercapai hari ini.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Hourly Throttling</label>
                        <div class="row g-2">
                            <div class="col">
                                <input type="number" name="hourly_limit" class="form-control" placeholder="Limit per jam (0 = Unset)" value="0">
                            </div>
                        </div>
                        <small class="text-muted">Maksimal email yang dikirim dalam 1 jam bergulir.</small>
                    </div>

                    <div class="hr-text">Pengaturan Bounce (IMAP)</div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">IMAP Host</label>
                            <input type="text" name="imap_host" class="form-control" placeholder="imap.gmail.com">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Port</label>
                            <input type="number" name="imap_port" class="form-control" placeholder="993" value="993">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Encryption</label>
                            <select name="imap_encryption" class="form-select">
                                <option value="SSL">SSL</option>
                                <option value="TLS">TLS</option>
                                <option value="None">None</option>
                            </select>
                        </div>
                    </div>
                    <small class="text-muted">Username & Password IMAP disamakan dengan SMTP di atas.</small>
                </div>
            </div>
        </div>
        
        <script>
            document.getElementById('warmupToggle').addEventListener('change', function() {
                document.getElementById('warmupLimitWrapper').style.display = this.checked ? 'block' : 'none';
            });
        </script>
        
        <div class="card-footer text-end">
            <a href="<?= url_to('admin.smtp.index') ?>" class="btn btn-link link-secondary me-2">Batal</a>
            <button type="submit" class="btn btn-primary">
                <i class="ti ti-device-floppy me-2"></i> Simpan Akun
            </button>
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

        // Deteksi Gmail
        document.getElementById('sender_email').addEventListener('input', function() {
            const email = this.value.toLowerCase();
            const alert = document.getElementById('gmail-alert');
            if (email.endsWith('@gmail.com')) {
                alert.style.display = 'block';
                // Otomatis isi host jika masih kosong
                const hostInput = document.querySelector('input[name="smtp_host"]');
                if (hostInput.value === '') hostInput.value = 'smtp.gmail.com';
            } else {
                alert.style.display = 'none';
            }
        });
    });
</script>
<?= $this->endSection() ?>