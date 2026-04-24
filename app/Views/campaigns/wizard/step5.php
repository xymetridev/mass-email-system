<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-end mb-4">
    <div>
        <h2 class="page-title">Setup Kampanye</h2>
    </div>
    <div>
        <button type="button" class="btn btn-outline-danger btn-sm shadow-sm" 
                onclick="confirmAction({
                    title: 'Batalkan Edit?',
                    text: 'Perubahan yang belum disimpan akan hilang. Kembali ke dashboard?',
                    onConfirm: () => window.location.href = '<?= url_to('app.campaigns.wizard.cancel') ?>'
                })">
            <i class="ti ti-x me-1"></i> Batal & Keluar
        </button>
    </div>
</div>
<div class="container-xl">
    <div class="steps steps-blue steps-counter mb-4" style="border-left: none !important;">
        <a href="<?= url_to('app.campaigns.wizard', 1) ?>" class="step-item">Info Dasar</a>
        <a href="<?= url_to('app.campaigns.wizard', 2) ?>" class="step-item">Penerima</a>
        <a href="<?= url_to('app.campaigns.wizard', 3) ?>" class="step-item">Konten</a>
        <a href="<?= url_to('app.campaigns.wizard', 4) ?>" class="step-item">Jadwal</a>
        <span class="step-item active">Review</span>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-12">
            <div class="card shadow-sm border-0">
                <div class="card-status-top bg-success"></div>
                <div class="card-header">
                    <div>
                        <h3 class="card-title">Final Check</h3>
                        <p class="card-subtitle">Periksa kembali detail kampanye sebelum dikirim ke antrian.</p>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="datagrid">
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Nama Kampanye</div>
                                    <div class="datagrid-content fw-bold text-primary"><?= esc($wizard['campaign_name']) ?></div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Subjek Email</div>
                                    <div class="datagrid-content"><?= esc($wizard['subject']) ?></div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Estimasi Penerima</div>
                                    <div class="datagrid-content">
                                        <?php 
                                            $contacts = json_decode($wizard['contacts_json'] ?? '{"rows":[]}', true);
                                            $count = count($contacts['rows'] ?? []);
                                        ?>
                                        <span class="status status-green">
                                            <span class="status-dot status-dot-animated"></span>
                                            <?= $count ?> Orang
                                        </span>
                                    </div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Waktu Eksekusi</div>
                                    <div class="datagrid-content">
                                        <?php if ($wizard['send_mode'] == 'now'): ?>
                                            <span class="badge bg-blue-lt">🚀 Segera Dikirim</span>
                                        <?php else: ?>
                                            <?php 
                                                // Mengubah format menjadi: "20 Apr 2026, 14:30"
                                                $formattedDate = date('d M Y, H:i', strtotime($wizard['scheduled_at']));
                                            ?>
                                            <span class="badge bg-orange-lt">
                                                <i class="ti ti-calendar me-1"></i> <?= esc($formattedDate) ?> WIB
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-8 border-start">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="form-label mb-0">Pratinjau Konten</label>
                                <a href="<?= url_to('app.campaigns.wizard', 3) ?>" class="btn btn-sm btn-ghost-secondary">Edit Desain</a>
                            </div>
                            <div class="preview-frame border rounded bg-white shadow-inset" style="height: 350px; overflow: hidden;">
                                <iframe id="mail-preview" style="width: 100%; height: 100%; border: none;"></iframe>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer bg-transparent d-flex justify-content-between py-3">
                    <a href="<?= url_to('app.campaigns.wizard', 4) ?>" class="btn btn-ghost-secondary px-4">
                        <i class="ti ti-arrow-left me-2"></i> Revisi Jadwal
                    </a>
                    <form action="<?= url_to('app.campaigns.wizard.finish') ?>" method="POST" id="form-launch">
                        <?= csrf_field() ?>
                        <button type="button" class="btn btn-success px-5 fw-bold shadow" data-bs-toggle="modal" data-bs-target="#modal-confirm">
                            Luncurkan Kampanye <i class="ti ti-rocket ms-2"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="modal modal-blur fade" id="modal-confirm" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
        <div class="modal-content shadow-lg">
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            <div class="modal-status bg-success"></div> 
            
            <div class="modal-body text-center py-4">
                <span class="avatar avatar-xl rounded bg-success-lt mb-3">
                    <i class="ti ti-rocket fs-1 text-success"></i>
                </span>
                
                <h3>Siap Meluncur?</h3>
                <div class="text-muted">
                    Kampanye <strong><?= esc($wizard['campaign_name'] ?? 'ini') ?></strong> akan segera diproses masuk ke antrean pengiriman. Pastikan semua data sudah benar.
                </div>
            </div>
            
            <div class="modal-footer border-0 pt-0">
                <div class="w-100">
                    <div class="row">
                        <div class="col">
                            <a href="#" class="btn btn-ghost-secondary w-100" data-bs-dismiss="modal">
                                Batal
                            </a>
                        </div>
                        <div class="col">
                            <button type="button" class="btn btn-success w-100" onclick="document.getElementById('form-launch').submit();">
                                Ya, Luncurkan!
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Memasukkan HTML ke dalam iframe agar renderingnya akurat
    const emailHtml = <?= json_encode($wizard['email_html'] ?? '') ?>;
    const iframe = document.getElementById('mail-preview');
    const doc = iframe.contentWindow.document;
    doc.open();
    doc.write(emailHtml);
    doc.close();
</script>

<style>
    .shadow-inset {
        box-shadow: inset 0 2px 4px 0 rgba(0, 0, 0, 0.06);
    }
</style>
<?= $this->endSection() ?>