<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<div class="page-header d-print-none mb-4">
    <div class="row align-items-center">
        <div class="col">
            <h2 class="page-title">Pustaka Template</h2>
            <div class="text-muted mt-1">Kumpulan desain email Anda dan template global dari Admin.</div>
        </div>
        <div class="col-auto ms-auto">
            <a href="<?= url_to('user.templates.create') ?>" class="btn btn-primary">
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
                <p class="empty-title">Pustaka Masih Kosong</p>
                <p class="empty-subtitle">Anda belum memiliki template pribadi. Mulailah dengan membuat template baru atau gunakan template dari Admin.</p>
            </div>
        </div>
    <?php endif; ?>

    <?php foreach($templates as $t): ?>
    <?php $t = (object)$t; ?>
    <div class="col-md-6 col-lg-4">
        <div class="card shadow-sm d-flex flex-column h-100 border-0">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <span class="avatar bg-blue-lt shadow-sm"><i class="ti ti-layout"></i></span>
                    <div class="ms-3">
                        <div class="fw-bold fs-3"><?= esc($t->name) ?></div>
                        <div class="text-muted small">Disimpan: <?= date('d M Y', strtotime($t->created_at)) ?></div>
                    </div>
                </div>
                <div class="border rounded bg-light p-0 position-relative" style="height: 180px; overflow: hidden;">
                    <div class="p-3" style="transform: scale(0.4); transform-origin: top left; width: 250%;">
                        <?= $t->content ?>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-transparent d-flex justify-content-between align-items-center">
                <?php 
                    // Logika sederhana untuk cek apakah ini template global (milik admin)
                    // (Asumsi: Jika campaign_id NULL dan user_id bukan milik saya, berarti Global)
                    $isGlobal = is_null($t->campaign_id) && $t->user_id != auth()->id();
                ?>
                <span class="badge bg-<?= $isGlobal ? 'azure' : 'green' ?>-lt">
                    <?= $isGlobal ? 'Admin Global' : 'Milik Saya' ?>
                </span>
                <div class="btn-list">
                    <?php if (!$isGlobal): ?>
                    <a href="<?= url_to('user.templates.edit', $t->id) ?>" class="btn btn-sm btn-pill">
                        <i class="ti ti-edit me-1"></i> Edit
                    </a>
                    <?php endif; ?>
                    <button class="btn btn-sm btn-pill" onclick="previewTemplate(<?= htmlspecialchars(json_encode($t->content)) ?>, '<?= esc($t->name) ?>')">
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