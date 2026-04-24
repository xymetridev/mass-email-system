<?= $this->extend(config('Auth')->views['layout']) ?>
<?= $this->section('title') ?>Set Password<?= $this->endSection() ?>

<?= $this->section('main') ?>
<div class="container-tight py-4">
    <div class="card card-md">
        <div class="card-body">
            <h2 class="card-title text-center mb-4">Set Password Baru</h2>
            <p class="text-secondary mb-4">Selamat datang! Sebelum melanjutkan, silakan buat password untuk akun Anda.</p>
            
            <form action="<?= url_to('set-password-update') ?>" method="post" autocomplete="off">
                <?= csrf_field() ?>
                <div class="mb-3">
                    <label class="form-label">Password Baru</label>
                    <input type="password" name="password" class="form-control" placeholder="Minimal 8 karakter" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Konfirmasi Password</label>
                    <input type="password" name="password_confirm" class="form-control" placeholder="Ulangi password" required>
                </div>
                <div class="form-footer">
                    <button type="submit" class="btn btn-primary w-100">Simpan & Masuk ke Dashboard</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>