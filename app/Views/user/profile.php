<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<div class="page-header d-print-none mb-3">
    <div class="row g-2 align-items-center">
        <div class="col">
            <h2 class="page-title">Pengaturan Akun</h2>
        </div>
    </div>
</div>

<div class="row row-cards">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body p-4 text-center">
                <span class="avatar avatar-xl mb-3 rounded bg-primary-lt">
                    <?= strtoupper(substr($user->username, 0, 1)) ?>
                </span>
                <h3 class="m-0 mb-1"><?= esc($user->username) ?></h3>
                <div class="text-muted"><?= esc($user->email) ?></div>
                <div class="mt-3">
                    <span class="badge bg-purple-lt"><?= strtoupper(implode(', ', $user->getGroups())) ?></span>
                </div>
            </div>
            <div class="d-flex">
                <div class="card-btn">Terdaftar: <?= $user->created_at->toFormattedDateString() ?></div>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card">
            <div class="card-header"><h3 class="card-title">Informasi Dasar</h3></div>
            <div class="card-body">
                <form action="<?= url_to('app.profile.update') ?>" method="post">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label">Alamat Email</label>
                        <input type="text" class="form-control" value="<?= esc($user->email) ?>" disabled>
                        <small class="form-hint">Email tidak dapat diubah untuk alasan keamanan.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" name="username" class="form-control" value="<?= old('username', $user->username) ?>">
                    </div>
                    <div class="form-footer">
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card mt-3 border-0 shadow-sm">
            <div class="card-header"><h3 class="card-title">Ganti Password</h3></div>
            <div class="card-body">
                <form action="<?= url_to('app.profile.change_password') ?>" method="post">
                    <?= csrf_field() ?>
                    
                    <div class="mb-3">
                        <label class="form-label">Password Baru</label>
                        <div class="input-group input-group-flat">
                            <input type="password" name="new_password" id="pass-1" class="form-control" placeholder="Minimal 8 karakter" autocomplete="off">
                            <span class="input-group-text">
                                <a href="javascript:void(0)" class="link-secondary" title="Show password" onclick="togglePassword('pass-1', this)">
                                    <i class="ti ti-eye"></i>
                                </a>
                            </span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Konfirmasi Password Baru</label>
                        <div class="input-group input-group-flat">
                            <input type="password" name="confirm_pw" id="pass-2" class="form-control" placeholder="Ulangi password baru" autocomplete="off">
                            <span class="input-group-text">
                                <a href="javascript:void(0)" class="link-secondary" title="Show password" onclick="togglePassword('pass-2', this)">
                                    <i class="ti ti-eye"></i>
                                </a>
                            </span>
                        </div>
                    </div>

                    <div class="form-footer">
                        <button type="submit" class="btn btn-danger">Perbarui Password</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function togglePassword(inputId, el) {
    const input = document.getElementById(inputId);
    const icon = el.querySelector('i');
    
    if (input.type === "password") {
        input.type = "text";
        icon.classList.replace('ti-eye', 'ti-eye-off');
    } else {
        input.type = "password";
        icon.classList.replace('ti-eye-off', 'ti-eye');
    }
}
</script>

<?= $this->endSection() ?>