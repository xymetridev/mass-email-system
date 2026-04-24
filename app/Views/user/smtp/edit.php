<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<div class="row mb-3">
    <div class="col">
        <h2 class="page-title">Edit Konfigurasi SMTP</h2>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card shadow-sm border-0">
            <form action="<?= url_to('app.smtp.update', $account->id) ?>" method="POST">
                <?= csrf_field() ?>
                <div class="card-body">
                    <h3 class="card-title text-primary mb-4">Update Kredensial</h3>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">Nama Pengirim</label>
                            <input type="text" name="sender_name" class="form-control" value="<?= esc($account->sender_name) ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">Email Pengirim</label>
                            <input type="email" name="sender_email" class="form-control" value="<?= esc($account->sender_email) ?>" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label required">SMTP Host</label>
                            <input type="text" name="smtp_host" class="form-control" value="<?= esc($account->smtp_host) ?>" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label required">Port</label>
                            <input type="number" name="smtp_port" class="form-control" value="<?= esc($account->smtp_port) ?>" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label required">SMTP Username</label>
                        <input type="text" name="smtp_username" class="form-control" value="<?= esc($account->smtp_username) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label required">SMTP Password</label>
                        <div class="input-group input-group-flat">
                            <input type="password" name="smtp_password" id="smtp_password" class="form-control" placeholder="Kosongkan jika tidak ingin mengubah password">
                            <span class="input-group-text">
                                <a href="#" id="togglePassword" class="link-secondary"><i class="ti ti-eye" id="toggleIcon"></i></a>
                            </span>
                        </div>
                        <small class="text-muted">Isi hanya jika ingin mengganti password SMTP yang lama.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Encryption Style</label>
                        <div class="form-selectgroup">
                            <?php foreach(['TLS', 'SSL', 'None'] as $enc): ?>
                            <label class="form-selectgroup-item">
                                <input type="radio" name="encryption" value="<?= $enc ?>" class="form-selectgroup-input" <?= $account->encryption == $enc ? 'checked' : '' ?>>
                                <span class="form-selectgroup-label text-uppercase"><?= $enc ?></span>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <div class="card-footer text-end">
                    <a href="<?= url_to('app.smtp') ?>" class="btn btn-link link-secondary">Batal</a>
                    <button type="submit" class="btn btn-primary">Update Konfigurasi</button>
                </div>
            </form>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card bg-info-lt border-0 shadow-sm">
            <div class="card-body">
                <h3 class="card-title text-info"><i class="ti ti-info-circle me-2"></i>Tips Edit</h3>
                <p class="small">Jika Anda mengganti <b>Port</b>, pastikan juga menyesuaikan tipe <b>Encryption</b> (biasanya 465 untuk SSL dan 587 untuk TLS).</p>
                <p class="small">Jangan lupa untuk menekan tombol <b>Test Connection</b> di halaman depan setelah menyimpan perubahan ini.</p>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>