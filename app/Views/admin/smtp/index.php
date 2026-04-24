<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<div class="row mb-3 align-items-center">
    <div class="col">
        <h2 class="page-title">Akun Pengirim (SMTP)</h2>
        <div class="text-muted mt-1">Kelola kredensial server email untuk pengiriman massal.</div>
    </div>
    <div class="col-auto ms-auto">
        <div class="btn-list">
            <button class="btn btn-outline-info" data-bs-toggle="modal" data-bs-target="#modal-domain-help">
                <i class="ti ti-help-circle me-2"></i> Panduan Verifikasi
            </button>
            <a href="<?= site_url('admin/smtp/new') ?>" class="btn btn-primary">
                <i class="ti ti-plus me-2"></i> Tambah Akun
            </a>
        </div>
    </div>
</div>

<!-- Modal Panduan Verifikasi Domain -->
<div class="modal modal-blur fade" id="modal-domain-help" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
        <div class="modal-content shadow-lg border-0">
            <div class="modal-header bg-info-lt">
                <h5 class="modal-title"><i class="ti ti-shield-check me-2"></i>Panduan Reputasi Email (Admin)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-4">
                <p class="mb-4 text-muted">Sebagai Admin, pastikan akun pengirim utama sudah memiliki record DNS yang valid agar email tidak diblokir oleh provider besar.</p>
                
                <div class="space-y-3">
                    <div class="card border-0 bg-light-lt shadow-none">
                        <div class="card-body">
                            <h4 class="mb-1 text-primary">SPF (Sender Policy Framework)</h4>
                            <div class="input-group">
                                <span class="input-group-text small">TXT</span>
                                <input type="text" class="form-control form-control-sm" value="v=spf1 include:domainanda.com ~all" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="card border-0 bg-light-lt shadow-none">
                        <div class="card-body">
                            <h4 class="mb-1 text-primary">DMARC (Recommended)</h4>
                            <div class="input-group">
                                <span class="input-group-text small">TXT</span>
                                <input type="text" class="form-control form-control-sm" value="v=DMARC1; p=none;" readonly>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary ms-auto px-4" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<div class="row row-cards">
    <?php if (empty($accounts)): ?>
        <div class="col-12 text-center py-5">
            <div class="empty">
                <p class="empty-title">Belum ada akun SMTP</p>
                <p class="empty-subtitle text-muted">Tambahkan akun pertama Anda untuk mulai mengirim kampanye.</p>
            </div>
        </div>
    <?php endif; ?>

    <?php foreach ($accounts as $acc): ?>
    <div class="col-md-6 col-lg-4">
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <span class="avatar bg-blue-lt"><i class="ti ti-server"></i></span>
                    <div class="ms-3">
                        <div class="font-weight-medium"><?= esc($acc->sender_name) ?></div>
                        <div class="text-muted small"><?= esc($acc->sender_email) ?></div>
                    </div>
                </div>
                <div class="mb-2">
                    <small class="text-muted">Host:</small> <code><?= esc($acc->smtp_host) ?>:<?= $acc->smtp_port ?></code>
                </div>
                <div class="mb-3">
                    <small class="text-muted">Enkripsi:</small> <span class="badge bg-azure-lt"><?= $acc->encryption ?></span>
                </div>
            </div>
            <div class="card-footer d-flex justify-content-between">
    <div>
        <a href="<?= url_to('admin.smtp.edit', $acc->id) ?>" class="btn btn-sm btn-outline-primary">Edit</a>
        <a href="<?= url_to('admin.smtp.test', $acc->id) ?>" class="btn btn-icon btn-ghost-info" title="Test Koneksi">
            <i class="ti ti-test-pipe"></i>
        </a>
    </div>
    <form action="<?= site_url('admin/smtp/delete/'.$acc->id) ?>" method="POST" onsubmit="return confirm('Hapus akun ini?')">
        <?= csrf_field() ?>
        <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
    </form>
</div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?= $this->endSection() ?>