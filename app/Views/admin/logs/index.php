<?php
function getActionColor($action) {
    if (strpos($action, 'DELETE') !== false || strpos($action, 'FAILED') !== false || strpos($action, 'BAN') !== false) return 'red';
    if (strpos($action, 'UPDATE') !== false || strpos($action, 'EDIT') !== false || strpos($action, 'PAUSE') !== false || strpos($action, 'DUPLICATE') !== false) return 'yellow';
    if (strpos($action, 'CREATE') !== false || strpos($action, 'IMPORT') !== false || strpos($action, 'SUCCESS') !== false || strpos($action, 'LAUNCH') !== false || strpos($action, 'STORE') !== false) return 'green';
    return 'blue';
}

function parseUserAgentStr($ua) {
    $os = 'Unknown OS';
    $browser = 'Unknown Browser';
    if (preg_match('/windows nt 11/i', $ua)) $os = 'Windows 11';
    elseif (preg_match('/windows nt 10/i', $ua)) $os = 'Windows 10';
    elseif (preg_match('/mac os x/i', $ua)) $os = 'Mac OS';
    elseif (preg_match('/linux/i', $ua)) $os = 'Linux';
    elseif (preg_match('/android/i', $ua)) $os = 'Android';
    elseif (preg_match('/iphone|ipad/i', $ua)) $os = 'iOS';
    
    if (preg_match('/edg/i', $ua)) $browser = 'Edge';
    elseif (preg_match('/chrome/i', $ua)) $browser = 'Chrome';
    elseif (preg_match('/firefox/i', $ua)) $browser = 'Firefox';
    elseif (preg_match('/safari/i', $ua)) $browser = 'Safari';
    
    if (empty($ua)) return 'System/Cron';
    if (strpos($ua, 'Postman') !== false) return 'Postman / API';
    
    return $os . ' • ' . $browser;
}
?>
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
                <table class="table table-vcenter text-nowrap card-table">
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
                                <?php $color = getActionColor($log['action']); ?>
                                <span class="badge badge-outline text-<?= $color ?> border-<?= $color ?>-lt fw-medium small">
                                    <?= esc($log['action']) ?>
                                </span>
                            </td>
                            <td>
                                <div class="small mb-1">
                                    <?= esc($log['description']) ?>
                                </div>
                            </td>
                            <td class="text-muted small">
                                <div><i class="ti ti-world me-1"></i> <?= esc($log['ip_address']) ?></div>
                                <div class="text-truncate" style="max-width: 150px;" title="<?= esc($log['user_agent']) ?>">
                                    <i class="ti ti-device-laptop me-1"></i> <?= parseUserAgentStr($log['user_agent']) ?>
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

<!-- Modal JSON Context -->
<div class="modal modal-blur fade" id="modal-context" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content shadow-lg border-0">
            <div class="modal-header">
                <h5 class="modal-title"><i class="ti ti-code me-2"></i>Data Konteks (JSON)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <pre id="context-content" class="p-3 m-0 bg-dark text-light" style="font-size: 12px; max-height: 400px; overflow-y: auto;"></pre>
            </div>
        </div>
    </div>
</div>

<script>
function showContext(jsonStr) {
    try {
        const obj = typeof jsonStr === 'string' ? JSON.parse(jsonStr) : jsonStr;
        document.getElementById('context-content').innerText = JSON.stringify(obj, null, 4);
    } catch (e) {
        document.getElementById('context-content').innerText = jsonStr;
    }
    new bootstrap.Modal(document.getElementById('modal-context')).show();
}

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