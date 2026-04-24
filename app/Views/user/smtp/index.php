<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<div class="row mb-3 align-items-center">
    <div class="col">
        <h2 class="page-title">Akun SMTP Saya</h2>
        <div class="text-muted mt-1">Gunakan akun email Anda sendiri untuk pengiriman kampanye yang lebih personal.</div>
    </div>
    <div class="col-auto ms-auto">
        <div class="btn-list">
            <button class="btn btn-outline-info" data-bs-toggle="modal" data-bs-target="#modal-domain-help">
                <i class="ti ti-help-circle me-2"></i> Panduan Verifikasi Domain
            </button>
            <a href="<?= url_to('app.smtp.create') ?>" class="btn btn-primary">
                <i class="ti ti-plus me-2"></i> Tambah Akun Baru
            </a>
        </div>
    </div>
</div>

<!-- Modal Panduan Verifikasi Domain -->
<div class="modal modal-blur fade" id="modal-domain-help" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
        <div class="modal-content shadow-lg border-0">
            <div class="modal-header bg-info-lt">
                <h5 class="modal-title"><i class="ti ti-shield-check me-2"></i>Panduan Meningkatkan Reputasi Email</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-4">
                <p class="mb-4 text-muted">Agar email Anda tidak masuk folder SPAM, sangat disarankan untuk melakukan verifikasi domain (SPF, DKIM, DMARC) di dashboard DNS provider Anda (seperti Cloudflare, IDCloudHost, dsb).</p>
                
                <div class="space-y-3">
                    <!-- SPF -->
                    <div class="card border-0 bg-light-lt shadow-none">
                        <div class="card-body">
                            <h4 class="mb-1 text-primary">1. SPF (Sender Policy Framework)</h4>
                            <p class="small text-muted mb-2">Memberi tahu Gmail/Yahoo bahwa server kami diizinkan mengirim email atas nama Anda.</p>
                            <div class="input-group">
                                <span class="input-group-text small">TXT</span>
                                <input type="text" class="form-control form-control-sm" value="v=spf1 include:domainanda.com ~all" readonly>
                            </div>
                        </div>
                    </div>
                    
                    <!-- DKIM -->
                    <div class="card border-0 bg-light-lt shadow-none">
                        <div class="card-body">
                            <h4 class="mb-1 text-primary">2. DKIM (DomainKeys Identified Mail)</h4>
                            <p class="small text-muted mb-2">Memberikan tanda tangan digital agar email tidak dianggap palsu.</p>
                            <div class="alert alert-warning py-2 small mb-0">
                                <i class="ti ti-alert-triangle me-1"></i> Nilai DKIM biasanya disediakan oleh provider SMTP Anda (misal: Mailgun, SendGrid, atau Hostinger).
                            </div>
                        </div>
                    </div>

                    <!-- DMARC -->
                    <div class="card border-0 bg-light-lt shadow-none">
                        <div class="card-body">
                            <h4 class="mb-1 text-primary">3. DMARC</h4>
                            <p class="small text-muted mb-2">Instruksi ke penerima jika SPF/DKIM gagal.</p>
                            <div class="input-group">
                                <span class="input-group-text small">TXT</span>
                                <input type="text" class="form-control form-control-sm" value="v=DMARC1; p=none;" readonly>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary ms-auto px-4" data-bs-dismiss="modal">Saya Mengerti</button>
            </div>
        </div>
    </div>
</div>

<div class="row row-cards">
    <?php if (empty($accounts)): ?>
        <div class="col-12 text-center py-5">
            <div class="empty">
                <div class="empty-icon"><i class="ti ti-mail-off" style="font-size: 48px"></i></div>
                <p class="empty-title">Belum ada akun SMTP</p>
                <p class="empty-subtitle text-muted">Hubungkan akun email Anda untuk mulai mengirim kampanye.</p>
            </div>
        </div>
    <?php endif; ?>

    <?php foreach ($accounts as $acc): ?>
    <div class="col-md-6 col-lg-4">
        <div class="card shadow-sm border-0">
            <div class="card-status-top bg-primary"></div>
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <span class="avatar bg-blue-lt shadow-sm"><i class="ti ti-server"></i></span>
                    <div class="ms-3">
                        <div class="font-weight-medium"><?= esc($acc->sender_name) ?></div>
                        <div class="text-muted small"><?= esc($acc->sender_email) ?></div>
                    </div>
                    <div class="ms-auto">
                         <span class="badge bg-green-lt text-uppercase" style="font-size: 10px">Individu</span>
                    </div>
                </div>
                <div class="divide-y-2 mt-4">
                    <div>
                        Host: <code class="text-primary"><?= esc($acc->smtp_host) ?></code>
                    </div>
                    <div>
                        Port: <code class="text-primary"><?= esc($acc->smtp_port) ?> (<?= esc($acc->encryption) ?>)</code>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-transparent d-flex justify-content-between">
                <div class="btn-list">
                    <a href="<?= url_to('app.smtp.edit', $acc->id) ?>" class="btn btn-sm btn-pill">
                        <i class="ti ti-edit me-1"></i> Edit
                    </a>
                    <a href="<?= url_to('app.smtp.test', $acc->id) ?>" class="btn btn-sm btn-info btn-pill" title="Test Connection">
                        <i class="ti ti-test-pipe me-1"></i> Test
                    </a>
                </div>
                <form action="<?= url_to('app.smtp.delete', $acc->id) ?>" method="POST" onsubmit="return confirm('Hapus akun ini?')">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-pill btn-danger">
                        <i class="ti ti-trash"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?= $this->endSection() ?>