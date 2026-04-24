<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<div class="page-header d-print-none mb-4">
    <div class="row align-items-center">
        <div class="col">
            <h2 class="page-title text-primary">Manajemen Kontak & Segmen</h2>
            <div class="text-muted mt-1">Kelola basis data pelanggan dan segmentasi pemasaran Anda.</div>
        </div>
        <div class="col-auto ms-auto">
            <div class="btn-list">
                <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modal-import">
                    <i class="ti ti-upload me-2"></i> Import
                </button>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal-add-contact">
                    <i class="ti ti-plus me-2"></i> Kontak Baru
                </button>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <!-- Header dengan Navigasi Tab -->
    <div class="card-header">
        <ul class="nav nav-tabs card-header-tabs" data-bs-toggle="tabs" role="tablist">
            <li class="nav-item" role="presentation">
                <a href="#tab-contacts" id="btn-tab-contacts" class="nav-link active" data-bs-toggle="tab" role="tab">
                    <i class="ti ti-users me-2"></i> Database Kontak
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a href="#tab-segments" id="btn-tab-segments" class="nav-link" data-bs-toggle="tab" role="tab">
                    <i class="ti ti-tags me-2"></i> Segmentasi (Tag)
                </a>
            </li>
        </ul>
    </div>
    
    <div class="tab-content">
        <!-- TAB 1: DAFTAR KONTAK -->
        <div class="tab-pane fade show active" id="tab-contacts" role="tabpanel">
            <div class="table-responsive">
                <table class="table table-vcenter table-mobile-md card-table">
                    <thead>
                        <tr>
                            <th class="ps-4">Informasi Kontak</th>
                            <th>Segmen / Tag</th>
                            <th>Tanggal Bergabung</th>
                            <th class="w-1 text-end pe-4">Opsi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($contacts)): ?>
                            <tr>
                                <td colspan="4" class="text-center py-5">
                                    <div class="empty">
                                        <div class="empty-icon"><i class="ti ti-user-x text-muted" style="font-size: 3rem;"></i></div>
                                        <p class="empty-title">Belum ada kontak</p>
                                        <p class="empty-subtitle text-muted small">Tambahkan kontak pertama Anda atau gunakan fitur import CSV.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                        <?php foreach($contacts as $c): ?>
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center">
                                    <span class="avatar avatar-sm rounded-circle me-3 bg-azure-lt shadow-none border">
                                        <?= strtoupper(substr($c['name'], 0, 1)) ?>
                                    </span>
                                    <div>
                                        <div class="font-weight-bold mb-0"><?= esc($c['name']) ?></div>
                                        <div class="text-muted small"><?= esc($c['email']) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <?php if(!empty($c['tag_names'])): ?>
                                    <div class="d-flex flex-wrap gap-1">
                                        <?php foreach(explode(',', $c['tag_names']) as $tn): ?>
                                            <span class="badge bg-blue-lt border-0"><?= esc($tn) ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted small"><em>Tanpa Tag</em></span>
                                <?php endif; ?>
                            </td>
                            <td class="text-muted small">
                                <?= date('d M Y', strtotime($c['created_at'])) ?>
                            </td>
                            <td class="text-end pe-4">
                                <div class="btn-list flex-nowrap justify-content-end">
                                    <button class="btn btn-icon btn-ghost-primary rounded-2" 
                                            onclick="editContact(<?= $c['id'] ?>, '<?= esc($c['name'], 'js') ?>', '<?= esc($c['email'], 'js') ?>', '<?= $c['tag_ids'] ?>')"
                                            title="Edit">
                                        <i class="ti ti-edit"></i>
                                    </button>
                                    <form action="<?= url_to('user.contacts.delete', $c['id']) ?>" method="POST" id="form-delete-<?= $c['id'] ?>" class="d-inline">
                                        <?= csrf_field() ?>
                                        <button type="button" class="btn btn-icon btn-ghost-danger rounded-2" 
                                                onclick="confirmAction({
                                                    title: 'Hapus Kontak?',
                                                    text: 'Email <?= esc($c['email'], 'js') ?> akan dihapus secara permanen.',
                                                    onConfirm: () => document.getElementById('form-delete-<?= $c['id'] ?>').submit()
                                                })">
                                            <i class="ti ti-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- TAB 2: SEGMEN (TAG) -->
        <div class="tab-pane fade" id="tab-segments" role="tabpanel">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <div>
                        <h3 class="mb-0">Daftar Tag / Segmen</h3>
                        <p class="text-muted small mb-0">Klik icon pensil untuk mengedit nama atau ikon X untuk menghapus tag.</p>
                    </div>
                    <button class="btn btn-pill" data-bs-toggle="modal" data-bs-target="#modal-add-tag">
                        <i class="ti ti-plus me-2"></i> Segmen Baru
                    </button>
                </div>
                
                <div class="row g-3">
                    <?php if(empty($tags)): ?>
                        <div class="col-12 text-center py-5">
                            <div class="empty">
                                <div class="empty-icon text-muted opacity-25"><i class="ti ti-tags" style="font-size: 3rem;"></i></div>
                                <p class="empty-title">Belum ada Segmen</p>
                            </div>
                        </div>
                    <?php endif; ?>
                    <?php foreach($tags as $t): ?>
                    <div class="col-md-4 col-lg-3">
                        <div class="card card-sm shadow-sm border border-light-subtle">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center">
                                    <div class="p-2 bg-white rounded shadow-sm me-3">
                                        <i class="ti ti-tag text-purple"></i>
                                    </div>
                                    <div class="flex-fill overflow-hidden">
                                        <div class="font-weight-bold text-truncate" title="<?= esc($t['name']) ?>"><?= esc($t['name']) ?></div>
                                        <div class="text-muted small">ID #<?= $t['id'] ?></div>
                                    </div>
                                    <div class="btn-list ms-2">
                                        <button class="btn btn-icon btn-ghost-primary border-0" 
                                                onclick="editTag(<?= $t['id'] ?>, '<?= esc($t['name'], 'js') ?>')">
                                            <i class="ti ti-pencil"></i>
                                        </button>
                                        <form action="<?= url_to('user.contacts.delete_tag', $t['id']) ?>" method="POST" id="form-delete-tag-<?= $t['id'] ?>">
                                            <?= csrf_field() ?>
                                            <button type="button" class="btn btn-icon btn-ghost-danger border-0"
                                                    onclick="confirmAction({
                                                        title: 'Hapus Tag?',
                                                        text: 'Tag <?= esc($t['name'], 'js') ?> akan dihapus. Data kontak tetap aman.',
                                                        onConfirm: () => document.getElementById('form-delete-tag-<?= $t['id'] ?>').submit()
                                                    })">
                                                <i class="ti ti-x"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL EDIT SEGMEN (TAG) -->
<div class="modal modal-blur fade" id="modal-edit-tag" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
        <div class="modal-content shadow-lg border-0">
            <form id="form-edit-tag" method="POST">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title">Edit Nama Segmen</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nama Segmen</label>
                        <input type="text" name="name" id="edit-tag-name" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-link link-secondary me-auto" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL TAMBAH KONTAK -->
<div class="modal modal-blur fade" id="modal-add-contact" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content shadow-lg border-0">
            <form action="<?= url_to('user.contacts.store') ?>" method="POST">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Kontak Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body py-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nama Lengkap</label>
                        <input type="text" name="name" class="form-control" placeholder="Budi Santoso" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Email</label>
                        <input type="email" name="email" class="form-control" placeholder="budi@domain.com" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Pilih Tag / Segmen</label>
                        <select name="tag_id" class="form-select">
                            <option value="">-- Tanpa Tag --</option>
                            <?php foreach($tags as $t): ?>
                                <option value="<?= $t['id'] ?>"><?= esc($t['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-link link-secondary me-auto" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL EDIT KONTAK -->
<div class="modal modal-blur fade" id="modal-edit-contact" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content shadow-lg border-0">
            <form id="form-edit-contact" method="POST">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title">Edit Detail Kontak</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body py-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nama</label>
                        <input type="text" name="name" id="edit-name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Email</label>
                        <input type="email" name="email" id="edit-email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Ganti Tag</label>
                        <select name="tag_id" id="edit-tag" class="form-select">
                            <option value="">-- Tetap / Tanpa Tag --</option>
                            <?php foreach($tags as $t): ?>
                                <option value="<?= $t['id'] ?>"><?= esc($t['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-link link-secondary me-auto" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL IMPORT -->
<div class="modal modal-blur fade" id="modal-import" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content shadow-lg border-0">
            <form action="<?= url_to('user.contacts.import') ?>" method="POST" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title">Import Bulk Contacts</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body py-4">
                    <div class="alert alert-info border-0 shadow-none bg-blue-lt mb-3">
                        File CSV harus memiliki kolom <strong>email</strong> dan <strong>name</strong>.
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Pilih Berkas CSV</label>
                        <input type="file" name="csv_file" class="form-control" accept=".csv" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Target Segmen (Untuk semua baris):</label>
                        <select name="tag_id" class="form-select">
                            <option value="">-- Tanpa Tag --</option>
                            <?php foreach($tags as $t): ?>
                                <option value="<?= $t['id'] ?>"><?= esc($t['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="<?= url_to('user.contacts.sample') ?>" class="btn btn-link me-auto">Contoh CSV</a>
                    <button type="submit" class="btn btn-success px-4">Mulai Impor</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL TAMBAH TAG -->
<div class="modal modal-blur fade" id="modal-add-tag" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
        <div class="modal-content shadow-lg border-0">
            <form action="<?= url_to('user.contacts.store_tag') ?>" method="POST">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title">Buat Segmen</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nama Segmen</label>
                        <input type="text" name="name" class="form-control" placeholder="Contoh: Member VIP" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-link link-secondary me-auto" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Konfirmasi</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function editContact(id, name, email, tagIds) {
        document.getElementById('edit-name').value = name;
        document.getElementById('edit-email').value = email;
        
        if (tagIds) {
            const firstTag = tagIds.split(',')[0];
            document.getElementById('edit-tag').value = firstTag;
        } else {
            document.getElementById('edit-tag').value = "";
        }

        const url = "<?= url_to('user.contacts.update', 999999) ?>".replace('999999', id);
        document.getElementById('form-edit-contact').action = url;

        new bootstrap.Modal(document.getElementById('modal-edit-contact')).show();
    }

    function editTag(id, name) {
        document.getElementById('edit-tag-name').value = name;
        const url = "<?= url_to('user.contacts.update_tag', 999999) ?>".replace('999999', id);
        document.getElementById('form-edit-tag').action = url;
        new bootstrap.Modal(document.getElementById('modal-edit-tag')).show();
    }

    // Logika Auto-Tab berdasarkan Hash URL
    document.addEventListener("DOMContentLoaded", function() {
        const hash = window.location.hash;
        if (hash === '#tab-segments') {
            const tabBtn = document.getElementById('btn-tab-segments');
            if (tabBtn) tabBtn.click();
        }
    });
</script>

<style>
    .nav-tabs .nav-link.active {
        background: transparent !important;
        border-bottom-color: var(--tblr-primary) !important;
        color: var(--tblr-primary) !important;
        font-weight: 600;
    }
    .table-vcenter td {
        vertical-align: middle !important;
    }
    .bg-light-lt {
        background-color: #f8fafc !important;
    }
</style>

<?= $this->endSection() ?>
