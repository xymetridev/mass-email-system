<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-end mb-4">
    <div>
        <h2 class="page-title">Setup Kampanye</h2>
    </div>
    <div>
        <a href="<?= url_to('app.campaigns.wizard.cancel') ?>" class="btn btn-outline-danger btn-sm shadow-sm" onclick="return confirm('Yakin ingin membatalkan edit dan kembali ke dashboard?')">
            <i class="ti ti-x me-1"></i> Batal & Keluar
        </a>
    </div>
</div>
<div class="container-xl">
    <div class="steps steps-blue steps-counter mb-4" style="border-left: none !important;">
        <span class="step-item active">Info Dasar</span>
        <span class="step-item">Penerima</span>
        <span class="step-item">Konten</span>
        <span class="step-item">Jadwal</span>
        <span class="step-item">Review</span>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-header">
                    <h3 class="card-title">Langkah 1: Identitas Kampanye</h3>
                </div>
                <form action="<?= url_to('app.campaigns.wizard.process', 1) ?>" method="POST">
                    <?= csrf_field() ?>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label required">Nama Kampanye</label>
                            <input type="text" name="campaign_name" class="form-control" 
                                   value="<?= esc($wizard['campaign_name'] ?? '') ?>" 
                                   placeholder="Contoh: Promo Diskon Lebaran" required>
                        </div>

                        <div class="form-label">Gunakan Akun Pengirim</div>
                        <div class="form-selectgroup form-selectgroup-boxes d-flex mb-3">
                            <label class="form-selectgroup-item flex-fill">
                                <input type="radio" name="sender_type" value="BUSINESS" class="form-selectgroup-input" 
                                       <?= ($wizard['sender_type'] ?? 'BUSINESS') == 'BUSINESS' ? 'checked' : '' ?>
                                       onchange="toggleSender('BUSINESS')">
                                <span class="form-selectgroup-label d-flex align-items-center p-3">
                                    <span class="me-3"><span class="form-selectgroup-check"></span></span>
                                    <span class="form-selectgroup-label-content text-start">
                                        <span class="form-selectgroup-title fw-bold">Bisnis</span>
                                        <span class="d-block text-muted small">Server resmi sistem</span>
                                    </span>
                                </span>
                            </label>
                            <label class="form-selectgroup-item flex-fill">
                                <input type="radio" name="sender_type" value="INDIVIDUAL" class="form-selectgroup-input"
                                       <?= ($wizard['sender_type'] ?? 'BUSINESS') == 'INDIVIDUAL' ? 'checked' : '' ?>
                                       onchange="toggleSender('INDIVIDUAL')">
                                <span class="form-selectgroup-label d-flex align-items-center p-3">
                                    <span class="me-3"><span class="form-selectgroup-check"></span></span>
                                    <span class="form-selectgroup-label-content text-start">
                                        <span class="form-selectgroup-title fw-bold">Individu</span>
                                        <span class="d-block text-muted small">SMTP pribadi Anda</span>
                                    </span>
                                </span>
                            </label>
                        </div>

                        <div class="mb-3">
                            <label class="form-label required">Pilih Server SMTP</label>
                            <select name="sender_id" id="sender_id" class="form-select" required>
                                <option value="">-- Pilih Akun --</option>
                                <optgroup label="Akun Bisnis" id="group-business">
                                    <?php foreach($businessSmtp as $b): ?>
                                        <option value="<?= $b->id ?>" <?= ($wizard['sender_id'] ?? '') == $b->id ? 'selected' : '' ?>>
                                            <?= $b->sender_name ?> (<?= $b->sender_email ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </optgroup>
                                <optgroup label="Akun Pribadi" id="group-individual" style="display:none">
                                    <?php foreach($personalSmtp as $p): ?>
                                        <option value="<?= $p->id ?>" <?= ($wizard['sender_id'] ?? '') == $p->id ? 'selected' : '' ?>>
                                            <?= $p->sender_name ?> (<?= $p->sender_email ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </optgroup>
                            </select>
                        </div>
                    </div>
                    <div class="card-footer text-end">
                        <?php if (($wizard['max_step'] ?? 1) >= 5): ?>
                            <button type="submit" name="jump_to_review" value="yes" class="btn btn-success me-2">
                                Simpan & Kembali ke Review <i class="ti ti-check ms-1"></i>
                            </button>
                        <?php endif; ?>
                        <button type="submit" class="btn btn-primary">
                            Lanjut ke Penerima <i class="ti ti-arrow-right ms-2"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // Tambahkan parameter isLoad untuk membedakan klik user vs load halaman
    function toggleSender(type, isLoad = false) {
        const business = document.getElementById('group-business');
        const individual = document.getElementById('group-individual');
        const select = document.getElementById('sender_id');
        
        // HANYA reset pilihan jika ini hasil klik user, bukan saat page load
        if (!isLoad) {
            select.value = ""; 
        }
        
        if (type === 'BUSINESS') {
            business.style.display = '';
            individual.style.display = 'none';
        } else {
            business.style.display = 'none';
            individual.style.display = '';
        }
    }

    // Jalankan saat load untuk menyesuaikan status awal (terutama saat 'back')
    document.addEventListener('DOMContentLoaded', function() {
        const checkedRadio = document.querySelector('input[name="sender_type"]:checked');
        if (checkedRadio) {
            // Panggil dengan true agar fungsi tahu ini saat load (jangan di-reset)
            toggleSender(checkedRadio.value, true); 
        }
    });
</script>
<?= $this->endSection() ?>