<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<div class="page-header d-print-none mb-4">
    <div class="row align-items-center">
        <div class="col">
            <h2 class="page-title text-primary"><i class="ti ti-history me-2"></i>System & Activity Logs</h2>
            <div class="text-muted mt-1">Pantau seluruh aktivitas pengguna dan kesehatan teknis sistem Anda.</div>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-header p-0">
        <ul class="nav nav-tabs card-header-tabs m-0" data-bs-toggle="tabs">
            <li class="nav-item">
                <a href="#tab-activity" class="nav-link active py-3 px-4" data-bs-toggle="tab">
                    <i class="ti ti-activity me-2"></i> Aktivitas Pengguna
                </a>
            </li>
            <li class="nav-item">
                <a href="#tab-system" class="nav-link py-3 px-4" data-bs-toggle="tab">
                    <i class="ti ti-terminal-2 me-2"></i> Log Sistem (Teknis)
                </a>
            </li>
        </ul>
    </div>
    
    <div class="tab-content">
        <!-- TAB 1: AKTIVITAS PENGGUNA -->
        <div class="tab-pane fade show active" id="tab-activity">
            <div class="table-responsive">
                <table class="table table-vcenter table-mobile-md card-table">
                    <thead>
                        <tr>
                            <th class="ps-4">Waktu</th>
                            <th>Pengguna</th>
                            <th>Aksi</th>
                            <th>Keterangan</th>
                            <th>IP & Device</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($activityLogs)): ?>
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">Belum ada aktivitas tercatat.</td>
                            </tr>
                        <?php endif; ?>
                        <?php foreach($activityLogs as $log): ?>
                        <tr>
                            <td class="ps-4 text-muted small">
                                <?= date('d M Y', strtotime($log['created_at'])) ?><br>
                                <span class="fw-bold"><?= date('H:i:s', strtotime($log['created_at'])) ?></span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <span class="avatar avatar-xs rounded-circle me-2 bg-blue-lt">
                                        <?= $log['username'] ? strtoupper(substr($log['username'], 0, 1)) : 'S' ?>
                                    </span>
                                    <div>
                                        <div class="font-weight-bold mb-0 small"><?= esc($log['username'] ?? 'System') ?></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-outline text-azure border-azure-lt fw-medium small">
                                    <?= esc($log['action']) ?>
                                </span>
                            </td>
                            <td>
                                <div class="text-dark small" style="max-width: 300px; white-space: normal;">
                                    <?= esc($log['description']) ?>
                                </div>
                            </td>
                            <td class="text-muted small">
                                <div><i class="ti ti-world me-1"></i> <?= esc($log['ip_address']) ?></div>
                                <div class="text-truncate" style="max-width: 150px;" title="<?= esc($log['user_agent']) ?>">
                                    <i class="ti ti-device-laptop me-1"></i> <?= esc($log['user_agent']) ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- TAB 2: LOG SISTEM (TEKNIS) -->
        <div class="tab-pane fade" id="tab-system">
            <div class="card-body py-4">
                <div class="alert alert-info border-0 shadow-none bg-blue-lt mb-4 small">
                    Log di bawah adalah rekaman teknis harian CodeIgniter. Berguna untuk mendiagnosis error pemrograman atau masalah server.
                </div>
                <div class="list-group list-group-flush list-group-hoverable border rounded">
                    <?php if (empty($logFiles)): ?>
                        <div class="list-group-item text-center py-4 text-muted">Tidak ada file log ditemukan.</div>
                    <?php endif; ?>

                    <?php foreach ($logFiles as $file): ?>
                    <div class="list-group-item">
                        <div class="row align-items-center">
                            <div class="col-auto"><span class="badge bg-red-lt">RAW LOG</span></div>
                            <div class="col text-truncate">
                                <a href="javascript:void(0)" onclick="readLog('<?= $file ?>')" class="text-reset d-block font-weight-medium">
                                    <?= $file ?>
                                </a>
                                <div class="d-block text-muted mt-n1 small">Log file harian sistem</div>
                            </div>
                            <div class="col-auto">
                                <button class="btn btn-sm btn-outline-primary shadow-sm" onclick="readLog('<?= $file ?>')">
                                    <i class="ti ti-eye me-1"></i> Lihat Detail
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Baca Log Teknis -->
<div class="modal modal-blur fade" id="modal-log" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
        <div class="modal-content shadow-lg border-0">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title" id="log-title">Isi File Log</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0 bg-dark">
                <pre id="log-content" class="text-light p-4 m-0" style="font-size: 11px; line-height: 1.6; white-space: pre-wrap; font-family: 'Fira Code', monospace;"></pre>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-link link-secondary ms-auto" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
function readLog(fileName) {
    const modalElement = document.getElementById('modal-log');
    const bootstrapModal = new bootstrap.Modal(modalElement);
    const contentArea = document.getElementById('log-content');
    const titleArea = document.getElementById('log-title');

    titleArea.innerText = "Teknis: " + fileName;
    contentArea.innerText = "Menghubungi server...";
    bootstrapModal.show();

    const baseUrl = "<?= url_to('admin.logs.view', 'LOG_FILE') ?>";
    const targetUrl = baseUrl.replace('LOG_FILE', fileName);

    fetch(targetUrl)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                contentArea.innerText = data.content;
            } else {
                contentArea.innerText = "Gagal memuat log: " + data.message;
            }
        })
        .catch(error => {
            contentArea.innerText = "Error: " + error;
        });
}
</script>

<style>
    .nav-tabs .nav-link.active {
        border-bottom-color: var(--tblr-primary) !important;
        font-weight: 600;
        color: var(--tblr-primary);
    }
</style>
<?= $this->endSection() ?>