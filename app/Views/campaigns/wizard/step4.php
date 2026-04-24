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
        <a href="<?= url_to('app.campaigns.wizard', 1) ?>" class="step-item">Info Dasar</a>
        <a href="<?= url_to('app.campaigns.wizard', 2) ?>" class="step-item">Penerima</a>
        <a href="<?= url_to('app.campaigns.wizard', 3) ?>" class="step-item">Konten</a>
        <span class="step-item active">Jadwal</span>
        <span class="step-item">Review</span>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <form action="<?= url_to('app.campaigns.wizard.process', 4) ?>" method="POST" class="card shadow-lg border-0">
                <?= csrf_field() ?>
                
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <div class="mb-3">
                            <span class="avatar avatar-lg rounded-circle bg-blue-lt">
                                <i class="ti ti-calendar-time fs-1"></i>
                            </span>
                        </div>
                        <h2 class="h1">Waktu Pengiriman</h2>
                        <p class="text-muted">Pilih kapan pesan Anda harus sampai ke kotak masuk penerima.</p>
                    </div>

                    <div class="form-selectgroup form-selectgroup-boxes d-flex flex-column gap-2">
                        <label class="form-selectgroup-item">
                            <input type="radio" name="send_mode" value="now" class="form-selectgroup-input" <?= ($wizard['send_mode'] ?? 'now') == 'now' ? 'checked' : '' ?> onchange="toggleSchedule(false)">
                            <span class="form-selectgroup-label d-flex align-items-center p-3">
                                <span class="me-3">
                                    <span class="form-selectgroup-check"></span>
                                </span>
                                <span class="form-selectgroup-label-content text-start">
                                    <span class="d-block fw-bold mb-1">Kirim Sekarang</span>
                                    <span class="d-block text-muted small">Email akan segera diproses masuk antrian utama.</span>
                                </span>
                                <span class="ms-auto">
                                    <i class="ti ti-rocket text-blue fs-2"></i>
                                </span>
                            </span>
                        </label>

                        <label class="form-selectgroup-item">
                            <input type="radio" name="send_mode" value="scheduled" class="form-selectgroup-input" <?= ($wizard['send_mode'] ?? 'now') == 'scheduled' ? 'checked' : '' ?> onchange="toggleSchedule(true)">
                            <span class="form-selectgroup-label d-flex align-items-center p-3">
                                <span class="me-3">
                                    <span class="form-selectgroup-check"></span>
                                </span>
                                <span class="form-selectgroup-label-content text-start">
                                    <span class="d-block fw-bold mb-1">Jadwalkan Waktu</span>
                                    <span class="d-block text-muted small">Pilih tanggal dan jam spesifik untuk mulai mengirim.</span>
                                </span>
                                <span class="ms-auto">
                                    <i class="ti ti-clock-play text-orange fs-2"></i>
                                </span>
                            </span>
                        </label>
                    </div>

                    <div id="schedule_box" style="display: none;" class="mt-4 animate__animated animate__fadeIn">
                        <label class="form-label fw-bold">Pilih Tanggal & Jam Dimulai</label>
                        <div class="input-icon">
                            <span class="input-icon-addon">
                                <i class="ti ti-calendar-event"></i>
                            </span>
                            <?php 
                                // Ambil tanggal dari session, jika tidak ada gunakan waktu saat ini
                                $savedDate = !empty($wizard['scheduled_at']) 
                                            ? date('Y-m-d\TH:i', strtotime($wizard['scheduled_at'])) 
                                            : date('Y-m-d\TH:i'); 
                            ?>
                            <input type="datetime-local" name="scheduled_at" id="scheduled_at" class="form-control" value="<?= $savedDate ?>">
                        </div>
                        <div class="mt-2 text-muted small">
                            <i class="ti ti-info-circle me-1"></i> Pengiriman akan dimulai sesuai dengan waktu lokal server Anda.
                        </div>
                    </div>
                </div>

                <div class="card-footer bg-light-lt d-flex justify-content-between p-3">
                    <a href="<?= url_to('app.campaigns.wizard', 3) ?>" class="btn btn-ghost-secondary px-4">
                        <i class="ti ti-arrow-left me-2"></i> Kembali
                    </a>
                    <div>
                        <?php if (($wizard['max_step'] ?? 1) >= 5): ?>
                            <button type="submit" name="jump_to_review" value="yes" class="btn btn-success me-2">
                                Simpan & Kembali ke Review <i class="ti ti-check ms-1"></i>
                            </button>
                        <?php endif; ?>
                        
                        <button type="submit" class="btn btn-primary px-5 fw-bold shadow-sm">
                            Lanjut ke Review <i class="ti ti-chevron-right ms-2"></i>
                        </button>
                    </div>
                    
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function toggleSchedule(show) {
        const box = document.getElementById('schedule_box');
        const input = document.getElementById('scheduled_at');
        
        if (show) {
            box.style.display = 'block';
            input.required = true;
        } else {
            box.style.display = 'none';
            input.required = false;
        }
    }

    // Tambahkan inisialisasi saat halaman dimuat
    document.addEventListener('DOMContentLoaded', function() {
        // Cari radio button mana yang sedang 'checked' (terpilih oleh PHP/Session)
        const checkedMode = document.querySelector('input[name="send_mode"]:checked');
        
        // Jika yang terpilih adalah 'scheduled', munculkan kotaknya!
        if (checkedMode && checkedMode.value === 'scheduled') {
            toggleSchedule(true);
        } else {
            toggleSchedule(false);
        }
    });
</script>

<style>
    /* Tambahkan efek hover yang halus pada kartu pilihan */
    .form-selectgroup-label {
        transition: all 0.2s ease;
        border-width: 2px;
    }
    .form-selectgroup-input:checked + .form-selectgroup-label {
        background-color: rgba(var(--tblr-primary-rgb), 0.03);
    }
    .animate__animated {
        animation-duration: 0.4s;
    }
</style>
<?= $this->endSection() ?>