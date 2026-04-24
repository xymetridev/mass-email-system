<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<div class="page-header d-print-none mb-4">
    <div class="row align-items-center">
        <div class="col">
            <h2 class="page-title">Automations (Email Sequences)</h2>
            <div class="text-muted mt-1">Buat alur email otomatis berdasarkan pemicu (Trigger) tertentu.</div>
        </div>
        <div class="col-auto ms-auto">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal-auto">
                <i class="ti ti-plus me-2"></i> Buat Flow Baru
            </button>
        </div>
    </div>
</div>

<div class="row row-cards">
    <?php if(empty($automations)): ?>
        <div class="col-12 text-center py-5">
            <div class="empty">
                <div class="empty-icon text-muted"><i class="ti ti-git-merge fs-1"></i></div>
                <p class="empty-title">Belum ada Automation</p>
                <p class="empty-subtitle">Otomatiskan pengiriman email Anda saat ada kontak baru dengan tag tertentu.</p>
            </div>
        </div>
    <?php endif; ?>

    <?php foreach($automations as $a): ?>
    <div class="col-md-6 col-lg-4">
    <div class="card card-sm shadow-sm h-100">

        <div class="card-header">
            <h3 class="card-title mb-0">
                <?= esc($a['name']) ?>
            </h3>

            <div class="card-actions d-flex align-items-center gap-2">

                <!-- STATUS -->
                <span class="badge bg-<?= $a['status'] == 'ACTIVE' ? 'success' : 'secondary' ?>-lt">
                    <?= $a['status'] ?>
                </span>

                <!-- ACTION -->
                <?php if($a['status'] == 'PAUSED'): ?>
                    <a href="<?= url_to('user.automations.status', $a['id'], 'ACTIVE') ?>" 
                       class="btn btn-icon btn-success btn-sm">
                        <i class="ti ti-player-play"></i>
                    </a>
                <?php else: ?>
                    <a href="<?= url_to('user.automations.status', $a['id'], 'PAUSED') ?>" 
                       class="btn btn-icon btn-secondary btn-sm">
                        <i class="ti ti-player-pause"></i>
                    </a>
                <?php endif; ?>

            </div>
        </div>

        <div class="card-body">

            <!-- TRIGGER -->
            <div class="text-secondary mb-3">
                <i class="ti ti-bolt me-1"></i>
                Trigger:
                <span class="fw-medium">
                    <?= esc($a['trigger_tag_name']) ?>
                </span>
            </div>

            <!-- ACTION UTAMA -->
            <a href="<?= url_to('user.automations.show', $a['id']) ?>" 
               class="btn btn-primary btn-sm btn-pill">
                <i class="ti ti-settings me-1"></i> Atur Steps
            </a>

        </div>
    </div>
</div>
    <?php endforeach; ?>
</div>

<!-- Modal Buat Flow -->
<div class="modal modal-blur fade" id="modal-auto" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <form action="<?= url_to('user.automations.store') ?>" method="POST" class="modal-content">
            <?= csrf_field() ?>
            <div class="modal-header">
                <h5 class="modal-title">Buat Automation Flow</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Nama Automation</label>
                    <input type="text" name="name" class="form-control" placeholder="Contoh: Welcome Sequence" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Trigger (Pemicu)</label>
                    <select name="trigger_tag_id" class="form-select" required>
                        <option value="">-- Pilih Tag Pemicu --</option>
                        <?php foreach($tags as $t): ?>
                            <option value="<?= $t['id'] ?>"><?= esc($t['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <small class="text-muted">Email akan mulai dikirim saat kontak mendapatkan Tag ini.</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-link link-secondary me-auto" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Buat Flow</button>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>
