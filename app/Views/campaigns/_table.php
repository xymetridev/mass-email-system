<div class="table-responsive" style="min-height: 250px;">
        <table class="table table-vcenter text-nowrap card-table">
            <thead>
                <tr>
                    <th>Nama Kampanye</th>
                    <th>Pengirim</th>
                    <th class="text-center">Status</th>
                    <th>Progres Pengiriman</th>
                    <th class="w-1">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($campaigns)): ?>
                <tr>
                    <td colspan="5" class="text-center py-5 text-muted">
                        <i class="ti ti-mail-off fs-1 d-block mb-2"></i> Belum ada kampanye yang dibuat.
                    </td>
                </tr>
                <?php endif; ?>

                <?php foreach ($campaigns as $c): ?>
                <tr>
                    <td data-label="Name">
                        <div class="fw-bold text-primary fs-3"><?= esc($c['name']) ?></div>
                        <div class="text-muted small">
                            <?= $c['updated_at'] ? date('d M Y, H:i', strtotime($c['updated_at'])) : date('d M Y', strtotime($c['created_at'])) ?>
                        </div>
                    </td>
                    <td data-label="Sender" class="text-muted">
                        <div><?= esc($c['sender_name']) ?></div>
                        <div class="small"><?= esc($c['sender_email']) ?></div>
                    </td>
                    <td class="text-center">
                        <?php
                            $statusBadge = [
                                'DRAFT' => 'bg-secondary', 'SCHEDULED' => 'bg-orange', 
                                'READY' => 'bg-azure', 'RUNNING' => 'bg-green', 
                                'PAUSED' => 'bg-warning', 'COMPLETED' => 'bg-blue', 'CANCELLED' => 'bg-red'
                            ];
                        ?>
                        <span id="status-badge-<?= $c['id'] ?>" class="badge <?= $statusBadge[$c['status']] ?>-lt">
                            <?php if($c['status'] == 'RUNNING'): ?><span class="status-dot status-dot-animated bg-green me-2"></span><?php endif; ?>
                            <?= esc($c['status']) ?>
                        </span>
                    </td>
                    <td data-label="Progress">
                        <div class="d-flex justify-content-between mb-1 small">
                            <span><?= number_format($c['total_sent'] + $c['total_failed']) ?> / <?= number_format($c['total_targets']) ?></span>
                            <span class="fw-bold"><?= $c['progress_percent'] ?>%</span>
                        </div>
                        <div class="progress progress-sm">
                            <div class="progress-bar bg-primary" style="width: <?= $c['progress_percent'] ?>%"></div>
                        </div>
                    </td>
                    <td class="text-end">
                        <div class="d-flex justify-content-end gap-2">
                            <?php if ($c['status'] == 'DRAFT'): ?>
                                <a href="<?= url_to('app.campaigns.edit_draft', $c['id']) ?>" class="btn btn-icon btn-ghost-primary" title="Lanjutkan Setup Wizard">
                                    <i class="ti ti-wand"></i>
                                </a>
                            <?php elseif ($c['status'] == 'PAUSED'): ?>
                                <form method="POST" action="<?= url_to('app.campaigns.update_status', $c['id'], 'RUNNING') ?>" class="d-inline">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-icon btn-ghost-success" title="Jalankan Kembali">
                                        <i class="ti ti-player-play"></i>
                                    </button>
                                </form>
                            <?php elseif ($c['status'] == 'RUNNING'): ?>
                                <form method="POST" action="<?= url_to('app.campaigns.update_status', $c['id'], 'PAUSED') ?>" class="d-inline">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-icon btn-ghost-warning" title="Pause">
                                        <i class="ti ti-player-pause"></i>
                                    </button>
                                </form>
                            <?php endif; ?>

                            <div class="dropdown">
                                <button class="btn btn-icon btn-ghost-secondary" data-bs-toggle="dropdown"><i class="ti ti-dots-vertical"></i></button>
                                <div class="dropdown-menu dropdown-menu-end">
                                    <a class="dropdown-item fw-bold" href="<?= url_to('app.campaigns.show', $c['id']) ?>">
                                        <i class="ti ti-folder-open me-2"></i> Buka / Kelola
                                    </a>
                                    
                                    <form method="POST" action="<?= url_to('app.campaigns.duplicate', $c['id']) ?>" class="d-inline">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="dropdown-item">
                                            <i class="ti ti-copy me-2"></i> Duplicate
                                        </button>
                                    </form>

                                    <?php if (in_array($c['status'], ['DRAFT', 'SCHEDULED', 'PAUSED', 'COMPLETED', 'CANCELLED'])): ?>
                                    <form method="POST" action="<?= url_to('app.campaigns.delete', $c['id']) ?>" id="form-delete-<?= $c['id'] ?>" class="d-inline">
                                        <?= csrf_field() ?>
                                        <button type="button" class="dropdown-item text-danger"
                                                onclick="confirmAction({
                                                    title: 'Hapus Kampanye?',
                                                    text: 'Seluruh data log dan statistik kampanye ini akan dihapus permanen.',
                                                    onConfirm: () => document.getElementById('form-delete-<?= $c['id'] ?>').submit()
                                                })">
                                            <i class="ti ti-trash me-2"></i> Hapus Permanen
                                        </button>
                                    </form>
                                    <?php endif; ?>

                                    <div class="dropdown-divider"></div>
                                        <?php if (in_array($c['status'], ['RUNNING', 'PAUSED'])): ?>
                                        <form method="POST" action="<?= url_to('app.campaigns.update_status', $c['id'], 'CANCELLED') ?>" id="form-cancel-<?= $c['id'] ?>" class="d-inline">
                                            <?= csrf_field() ?>
                                            <button type="button" class="dropdown-item text-danger"
                                                    onclick="confirmAction({
                                                        title: 'Hentikan Kampanye?',
                                                        text: 'Kampanye akan dihentikan secara paksa dan tidak bisa dilanjutkan lagi.',
                                                        onConfirm: () => document.getElementById('form-cancel-<?= $c['id'] ?>').submit()
                                                    })">
                                                <i class="ti ti-player-stop me-2"></i> Hentikan (Cancel)
                                            </button>
                                        </form>
                                        <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>