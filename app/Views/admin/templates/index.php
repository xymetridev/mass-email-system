<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<div class="page-header d-print-none mb-4">
    <div class="row align-items-center">
        <div class="col">
            <h2 class="page-title">Pustaka Template Master</h2>
            <div class="text-muted mt-1">Kelola desain email yang bisa digunakan berulang kali untuk berbagai kampanye.</div>
        </div>
        <div class="col-auto ms-auto">
            <a href="<?= url_to('admin.templates.create') ?>" class="btn btn-primary">
                <i class="ti ti-plus me-2"></i> Buat Template Baru
            </a>
        </div>
    </div>
</div>

<div class="row row-cards">
    <?php if (empty($templates)): ?>
        <div class="col-12 text-center py-5">
            <div class="empty">
                <div class="empty-icon text-muted"><i class="ti ti-template fs-1"></i></div>
                <p class="empty-title">Pustaka Kosong</p>
                <p class="empty-subtitle">Anda belum menyimpan template apapun ke dalam pustaka master.</p>
            </div>
        </div>
    <?php endif; ?>

    <?php foreach($templates as $t): ?>
    <?php $t = (object)$t; // Safety cast ?>
    <div class="col-md-6 col-lg-4">
        <div class="card shadow-sm d-flex flex-column h-100 border-0">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <span class="avatar bg-purple-lt shadow-sm"><i class="ti ti-layout"></i></span>
                    <div class="ms-3">
                        <div class="fw-bold fs-3"><?= esc($t->name) ?></div>
                        <div class="text-muted small">Disimpan: <?= date('d M Y', strtotime($t->created_at)) ?></div>
                    </div>
                </div>
                <div class="border rounded bg-light p-0 position-relative" style="height: 200px; overflow: hidden;">
                    <div class="p-3" style="transform: scale(0.5); transform-origin: top left; width: 200%;">
                        <?= $t->content ?>
                    </div>
                    <div style="position: absolute; inset: 0; background: linear-gradient(transparent 70%, rgba(0,0,0,0.05));"></div>
                </div>
            </div>
            <div class="card-footer bg-transparent d-flex justify-content-between align-items-center">
                <span class="badge bg-<?= is_null($t->campaign_id) ? 'azure' : 'orange' ?>-lt">
                    <?= is_null($t->campaign_id) ? 'Global Master' : 'Campaign Template' ?>
                </span>
                <div class="btn-list">
                    <a href="<?= url_to('admin.templates.edit', $t->id) ?>" class="btn btn-sm btn-outline-info">
                        <i class="ti ti-edit me-1"></i> Edit
                    </a>
                    <button class="btn btn-sm btn-outline-primary" onclick="previewTemplate(<?= htmlspecialchars(json_encode($t->content)) ?>, '<?= esc($t->name) ?>')">
                        <i class="ti ti-eye me-1"></i> Preview
                    </button>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Modal Preview -->
<div class="modal modal-blur fade" id="modal-preview-template" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content shadow-lg">
            <div class="modal-header">
                <h5 class="modal-title" id="preview-title">Preview Template</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <iframe id="preview-frame" style="width: 100%; height: 600px; border: none;"></iframe>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
    function previewTemplate(html, name) {
        document.getElementById('preview-title').innerText = 'Preview: ' + name;
        const iframe = document.getElementById('preview-frame');
        iframe.srcdoc = html;
        const modal = new bootstrap.Modal(document.getElementById('modal-preview-template'));
        modal.show();
    }
</script>
<?= $this->endSection() ?>