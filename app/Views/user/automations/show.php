<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<div class="page-header d-print-none mb-4">
    <div class="row align-items-center">
        <div class="col">
            <h2 class="page-title">Alur Automation: <?= esc($automation['name']) ?></h2>
            <div class="text-muted mt-1">Susun urutan email yang akan dikirimkan.</div>
        </div>
        <div class="col-auto ms-auto">
            <a href="<?= url_to('user.automations') ?>" class="btn btn-outline-secondary">Kembali</a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card shadow-sm border-0">
            <div class="card-body">

                <h3 class="card-title mb-4">Alur Automation</h3>

                <ul class="steps steps-vertical">

                    <!-- TRIGGER -->
                    <li class="step-item">
                        <div class="h4 m-0">Trigger</div>
                        <div class="text-secondary">
                            Saat kontak mendapatkan tag:
                            <span class="badge bg-blue-lt">
                                <?= esc($automation['trigger_tag_name'] ?? 'Tag Terpilih') ?>
                            </span>
                        </div>
                    </li>

                    <!-- STEPS -->
                    <?php foreach($steps as $step): ?>
                    <li class="step-item">
                        <div class="h4 m-0 d-flex align-items-center">
                            <i class="ti ti-mail me-2 text-primary"></i>
                            Langkah <?= $step['step_order'] ?> - Kirim Email
                        </div>
                        <div class="text-secondary mb-2">
                            Template:
                            <span class="fw-semibold text-dark">
                                <?= esc($step['template_name']) ?>
                            </span>
                        </div>
                        <div>
                            <span class="badge bg-orange-lt">
                                Jeda: <?= $step['delay_days'] ?> hari
                            </span>
                        </div>
                    </li>
                    <?php endforeach; ?>

                    <!-- ADD STEP -->
                    <li class="step-item">
                        <div class="mt-2">
                            <button class="btn btn-primary btn-sm"
                                data-bs-toggle="modal"
                                data-bs-target="#modal-step">
                                <i class="ti ti-plus me-1"></i> Tambah Langkah
                            </button>
                        </div>
                    </li>

                </ul>

            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Langkah -->
<div class="modal modal-blur fade" id="modal-step" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <form action="<?= url_to('user.automations.step.store', $automation['id']) ?>" method="POST" class="modal-content">
            <?= csrf_field() ?>
            <div class="modal-header">
                <h5 class="modal-title">Tambah Langkah Email</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Pilih Template Email</label>
                    <select name="template_id" class="form-select" required>
                        <option value="">-- Pilih Template --</option>
                        <?php foreach($templates as $t): ?>
                            <option value="<?= $t['id'] ?>"><?= esc($t['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Kirim setelah jeda berapa hari?</label>
                    <input type="number" name="delay_days" class="form-control" value="0" min="0">
                    <small class="text-muted">0 = Kirim Instan. 1 = Tunggu 1 hari setelah langkah sebelumnya.</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-link link-secondary me-auto" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Tambah Langkah</button>
            </div>
        </form>
    </div>
</div>

<style>
    /* Simple Timeline CSS */
    .vertical-timeline { width: 100%; position: relative; padding: 1.5rem 0 1rem; }
    .vertical-timeline::before { content: ''; position: absolute; top: 0; left: 19px; height: 100%; width: 4px; background: #e9ecef; border-radius: .25rem; }
    .vertical-timeline-item { position: relative; margin-bottom: 1.5rem; }
    .vertical-timeline-element-icon { position: absolute; top: 0; left: 5px; width: 30px; height: 30px; border-radius: 50%; background: #fff; }
    .vertical-timeline-element-content { position: relative; margin-left: 60px; background: #f8f9fa; border-radius: .25rem; padding: 1rem; }
</style>
<?= $this->endSection() ?>
