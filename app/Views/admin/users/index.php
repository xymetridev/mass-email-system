<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<div class="row mb-3 align-items-center">
    <div class="col">
        <h2 class="page-title">Manajemen Pengguna</h2>
        <div class="text-muted mt-1">Daftar tim operasional yang memiliki akses ke sistem.</div>
    </div>
    <div class="col-auto ms-auto d-print-none">
        <div class="btn-list">
            <a href="#" class="btn btn-primary d-none d-sm-inline-block" data-bs-toggle="modal" data-bs-target="#modal-add-user">
                <i class="ti ti-plus me-1"></i> Tambah Pengguna
            </a>
            <a href="#" class="btn btn-primary d-sm-none btn-icon" data-bs-toggle="modal" data-bs-target="#modal-add-user" aria-label="Tambah Pengguna">
                <i class="ti ti-plus"></i>
            </a>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-vcenter card-table">
            <thead>
                <tr>
                    <th>Pengguna</th>
                    <th>Role</th>
                    <th>Status Akun</th>
                    <th>Terdaftar</th>
                    <th class="w-1">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user) : ?>
                <tr>
                    <td>
                        <div class="d-flex py-1 align-items-center">
                            <span class="avatar me-2"><?= strtoupper(substr($user->username, 0, 2)) ?></span>
                            <div class="flex-fill">
                                <div class="font-weight-medium"><?= esc($user->username) ?></div>
                                <div class="text-muted small"><?= esc($user->email) ?></div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <?php $group = $user->getGroups()[0] ?? 'user'; ?>
                        <span class="badge bg-<?= $group == 'admin' ? 'purple' : 'azure' ?>-lt">
                            <?= strtoupper($group) ?>
                        </span>
                    </td>
                    <td>
                        <?php if ($user->isBanned()) : ?>
                            <span class="status status-red"><span class="status-dot"></span> Dinonaktifkan</span>
                        <?php else : ?>
                            <span class="status status-green"><span class="status-dot"></span> Aktif</span>
                        <?php endif ?>
                    </td>
                    <td class="text-muted small"><?= $user->created_at->toFormattedDateString() ?></td>
                    <td>
                        <?php if ($user->id !== auth()->id()) : ?>
                            <form action="<?= site_url('admin/users/toggle/' . $user->id) ?>" method="POST" class="d-inline" onsubmit="return confirm('<?= $user->isBanned() ? 'Aktifkan kembali akun ini?' : 'Nonaktifkan/blokir akun ini?' ?>')">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-sm btn-<?= $user->isBanned() ? 'success' : 'outline-danger' ?>">
                                    <?= $user->isBanned() ? 'Aktifkan' : 'Blokir' ?>
                                </button>
                            </form>
                        <?php else : ?>
                            <span class="text-muted small">Anda</span>
                        <?php endif ?>
                    </td>
                </tr>
                <?php endforeach ?>
            </tbody>
        </table>
    </div>
    <div class="card-footer d-flex align-items-center">
        <?= $pager->links('default', 'tabler_pagination') ?>
    </div>
</div>

<!-- Modal Tambah Pengguna -->
<div class="modal modal-blur fade" id="modal-add-user" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form action="<?= url_to('admin.users.store') ?>" method="POST">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Pengguna Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info" role="alert">
                        <div class="d-flex">
                            <div><i class="ti ti-info-circle me-2"></i></div>
                            <div>
                                Sistem tidak akan meminta password. Pengguna baru cukup mengakses fitur <strong>Lupa Password (Magic Link)</strong> di halaman login menggunakan email yang didaftarkan.
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label required">Username</label>
                        <input type="text" class="form-control" name="username" placeholder="Masukkan username" required minlength="3" maxlength="30">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label required">Email</label>
                        <input type="email" class="form-control" name="email" placeholder="Masukkan email perusahaan" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label required">Role (Akses Akses)</label>
                        <select class="form-select" name="role" required>
                            <option value="user">User (Hanya mengelola kampanyenya sendiri)</option>
                            <option value="admin">Admin (Akses penuh ke semua kampanye & pengaturan)</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn me-auto" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Tambah Pengguna</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>