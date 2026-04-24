<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<div class="container-xl">
    <div class="page-header d-print-none mb-4">
        <div class="row align-items-center">
            <div class="col">
                <div class="mb-1">
                    <ol class="breadcrumb" aria-label="breadcrumbs">
                        <li class="breadcrumb-item"><a href="<?= url_to('app.campaigns') ?>">Kampanye</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Buka & Kelola</li>
                    </ol>
                </div>
                <h2 class="page-title text-uppercase">
                    <?= esc($campaign['name']) ?>
                    <?php 
                        $badgeClass = [
                            'DRAFT' => 'bg-secondary', 'SCHEDULED' => 'bg-orange', 
                            'READY' => 'bg-azure', 'RUNNING' => 'bg-success', 
                            'PAUSED' => 'bg-warning', 'COMPLETED' => 'bg-blue', 
                            'CANCELLED' => 'bg-red'
                        ];
                        $class = $badgeClass[$campaign['status']] ?? 'bg-secondary';
                    ?>
                    <span class="badge <?= $class ?>-lt ms-3 fs-5"><?= $campaign['status'] ?></span>
                </h2>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <div class="btn-list">
                    <a href="<?= url_to('app.campaigns.export', $campaign['id']) ?>" class="btn btn-outline-success">
                        <i class="ti ti-download me-2"></i> Ekspor CSV
                    </a>
                    <a href="<?= url_to('app.campaigns') ?>" class="btn btn-outline-secondary">
                        <i class="ti ti-arrow-left me-2"></i> Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4 shadow-sm border-0">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <div class="fw-bold text-muted">Progres Pengiriman</div>
                <div class="h3 mb-0"><?= $progress_percent ?>%</div>
            </div>
            <div class="progress progress-sm mb-4">
                <div class="progress-bar <?= $class ?>" style="width: <?= $progress_percent ?>%"></div>
            </div>
            
            <div class="row row-cards">
                <div class="col-sm-6 col-lg-2">
                    <div class="d-flex align-items-center">
                        <span class="bg-primary text-white avatar me-3"><i class="ti ti-users"></i></span>
                        <div>
                            <div class="text-muted small">Total Target</div>
                            <div class="font-weight-medium fs-3"><?= number_format($total) ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-2">
                    <div class="d-flex align-items-center">
                        <span class="bg-green text-white avatar me-3"><i class="ti ti-send"></i></span>
                        <div>
                            <div class="text-success small">Terkirim</div>
                            <div class="font-weight-medium fs-3"><?= number_format($sent) ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-2">
                    <div class="d-flex align-items-center">
                        <span class="bg-danger text-white avatar me-3"><i class="ti ti-x"></i></span>
                        <div>
                            <div class="text-danger small">Gagal</div>
                            <div class="font-weight-medium fs-3"><?= number_format($failed) ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-2">
                    <div class="d-flex align-items-center">
                        <span class="bg-warning text-white avatar me-3"><i class="ti ti-clock"></i></span>
                        <div>
                            <div class="text-warning small">Antrean</div>
                            <div class="font-weight-medium fs-3"><?= number_format($pending) ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-2">
                    <div class="d-flex align-items-center">
                        <span class="bg-info text-white avatar me-3"><i class="ti ti-mail-opened"></i></span>
                        <div>
                            <div class="text-info small">Dibuka (Opens)</div>
                            <div class="font-weight-medium fs-3"><?= number_format($opens) ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-2">
                    <div class="d-flex align-items-center">
                        <span class="bg-purple text-white avatar me-3"><i class="ti ti-click"></i></span>
                        <div>
                            <div class="text-purple small">Diklik (Clicks)</div>
                            <div class="font-weight-medium fs-3"><?= number_format($clicks) ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4 shadow-sm border-0">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title"><i class="ti ti-settings text-muted me-2"></i> Konfigurasi Pengiriman</h3>
            <?php if (in_array($campaign['status'], ['DRAFT', 'SCHEDULED'])): ?>
                <a href="<?= url_to('app.campaigns.edit_draft', $campaign['id']) ?>?step=1" class="btn btn-sm btn-pill">
                    <i class="ti ti-edit me-1"></i> Edit Pengaturan
                </a>
            <?php endif; ?>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-3 mb-md-0">
                    <small class="text-muted d-block mb-1">Akun SMTP (Sender):</small>
                    <div class="fs-3 fw-bold"><?= esc($campaign['sender_name']) ?></div>
                    <div class="text-muted"><?= esc($campaign['sender_email']) ?></div>
                </div>
                <div class="col-md-6">
                    <small class="text-muted d-block mb-1">Panggilan Default (Jika tag nama kosong):</small>
                    <div class="fs-3 fw-bold"><?= esc($campaign['default_name'] ?: '-') ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4 shadow-sm border-0">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title"><i class="ti ti-mail text-muted me-2"></i> Konten Email</h3>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-sm btn-pill" data-bs-toggle="modal" data-bs-target="#modal-preview-email">
                    <i class="ti ti-eye me-1"></i> Preview Full Screen
                </button>
                <?php if (in_array($campaign['status'], ['DRAFT', 'SCHEDULED'])): ?>
                    <a href="<?= url_to('app.campaigns.edit_draft', $campaign['id']) ?>?step=3" class="btn btn-sm btn-pill">
                        <i class="ti ti-edit me-1"></i> Edit Konten
                    </a>
                <?php endif; ?>
            </div>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <small class="text-muted d-block mb-1">Subjek Email:</small>
                <div class="h3 mb-0"><?= esc($campaign['subject']) ?></div>
            </div>
            <div>
                <small class="text-muted d-block mb-1">Cuplikan Konten:</small>
                <div class="border rounded p-3 bg-light" style="max-height: 150px; overflow: hidden; position: relative;">
                    <div class="opacity-50" style="pointer-events: none;">
                        <?= $campaign['content'] ?>
                    </div>
                    <div style="position: absolute; bottom: 0; left: 0; right: 0; height: 50px; background: linear-gradient(transparent, #f6f8fb);"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4 shadow-sm border-0">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title"><i class="ti ti-calendar text-muted me-2"></i> Penjadwalan</h3>
            <?php if (in_array($campaign['status'], ['DRAFT', 'SCHEDULED'])): ?>
                <a href="<?= url_to('app.campaigns.edit_draft', $campaign['id']) ?>?step=4" class="btn btn-sm btn-pill">
                    <i class="ti ti-edit me-1"></i> Edit Jadwal
                </a>
            <?php endif; ?>
        </div>
        <div class="card-body">
            <?php if ($campaign['scheduled_at']): ?>
                <div class="d-flex align-items-center">
                    <div class="avatar avatar-md bg-orange-lt me-3"><i class="ti ti-clock fs-2"></i></div>
                    <div>
                        <div class="text-muted small">Rencana Pengiriman:</div>
                        <div class="h3 mb-0"><?= date('d F Y, H:i', strtotime($campaign['scheduled_at'])) ?></div>
                    </div>
                </div>
            <?php else: ?>
                <div class="d-flex align-items-center">
                    <div class="avatar avatar-md bg-secondary-lt me-3"><i class="ti ti-player-play fs-2"></i></div>
                    <div>
                        <div class="h3 mb-0">Langsung Kirim (Now)</div>
                        <div class="text-muted small">Kampanye akan dieksekusi segera setelah dijalankan.</div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header">
            <h3 class="card-title"><i class="ti ti-list text-muted me-2"></i> Log Pengiriman (Antrean)</h3>
        </div>
        <div class="table-responsive">
            <table class="table card-table table-vcenter text-nowrap">
                <thead>
                    <tr>
                        <th>Alamat Email</th>
                        <th>Subjek (Merge Tag)</th>
                        <th>Status</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($recipients)): ?>
                        <tr><td colspan="4" class="text-center py-5 text-muted">Belum ada data antrean.</td></tr>
                    <?php endif; ?>

                    <?php foreach ($recipients as $row): ?>
                    <tr>
                        <td><span class="text-muted fw-medium"><?= esc($row['to_email']) ?></span></td>
                        <td class="text-truncate" style="max-width: 250px;" title="<?= esc($row['subject']) ?>"><?= esc($row['subject']) ?></td>
                        <td>
                            <?php if ($row['status'] == 'SENT'): ?>
                                <span class="badge bg-success-lt"><i class="ti ti-check me-1"></i> SENT</span>
                            <?php elseif ($row['status'] == 'FAILED'): ?>
                                <span class="badge bg-danger-lt"><i class="ti ti-x me-1"></i> FAILED</span>
                            <?php else: ?>
                                <span class="badge bg-secondary-lt">PENDING</span>
                            <?php endif; ?>
                        </td>
                        <td class="small text-muted text-truncate" style="max-width: 200px;" title="<?= esc($row['last_error'] ?? '') ?>">
                            <?= $row['last_error'] ? esc($row['last_error']) : '-' ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="card-footer d-flex align-items-center justify-content-between">
            <p class="m-0 text-muted">Menampilkan log antrean sistem</p>
            <?= $pager ?>
        </div>
    </div>
</div>

<div class="modal modal-blur fade" id="modal-preview-email" tabindex="-1" role="dialog" aria-modal="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content shadow-lg">
            <div class="modal-header">
                <h5 class="modal-title">Preview Konten Email</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div class="p-3 border-bottom">
                    <div class="mb-1"><strong>Subject:</strong> <?= esc($campaign['subject']) ?></div>
                    <div class="small text-muted"><strong>From:</strong> <?= esc($campaign['sender_name']) ?> &lt;<?= esc($campaign['sender_email']) ?>&gt;</div>
                </div>
                <div class="p-0" style="height: 600px;">
                    <iframe id="email-preview-frame" style="width: 100%; height: 100%; border: none;"></iframe>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>

document.addEventListener('DOMContentLoaded', function() {
    const modalPreview = document.getElementById('modal-preview-email');
    const iframe = document.getElementById('email-preview-frame');
    
    // 1. Ambil data dari PHP dengan aman menggunakan json_encode
    // Pastikan ['content'] sesuai dengan nama kolom di database kamu!
    const emailHtml = <?= json_encode($campaign['content'] ?? $campaign['email_html'] ?? '<h2>Tidak ada konten email.</h2>') ?>;

    if (modalPreview && iframe) {
        // 2. Gunakan event 'show.bs.modal' agar konten siap sebelum modal tampil penuh
        modalPreview.addEventListener('show.bs.modal', function () {
            // Gunakan srcdoc untuk memasukkan HTML langsung ke iframe
            iframe.srcdoc = emailHtml;
        });
        
        // 3. Bersihkan konten saat modal ditutup (Opsional, agar ringan)
        modalPreview.addEventListener('hidden.bs.modal', function () {
            iframe.srcdoc = '';
        });
    }
});
</script>

<?= $this->endSection() ?>