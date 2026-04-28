<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<div class="container-xl">
    <div class="page-header d-print-none mb-4">
        <div class="row align-items-center">
            <div class="col">
                <div class="page-pretitle">Sistem & Keamanan</div>
                <h2 class="page-title text-uppercase">
                    <i class="ti ti-shield-off me-2 text-danger"></i> Daftar Hitam (Suppression)
                </h2>
                <div class="text-muted mt-1">Daftar email yang ditolak atau berhenti berlangganan. Sistem tidak akan mengirim pesan ke alamat ini.</div>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <div class="btn-list">
                    <a href="#" class="btn btn-primary d-none d-sm-inline-block" data-bs-toggle="modal" data-bs-target="#modal-add-blacklist">
                        <i class="ti ti-plus me-1"></i> Tambah Manual
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Alert Messages -->
    <?php if (session()->getFlashdata('success')) : ?>
        <div class="alert alert-success alert-dismissible bg-success-lt" role="alert">
            <div class="d-flex">
                <div><i class="ti ti-check fs-2 me-2"></i></div>
                <div><?= session()->getFlashdata('success') ?></div>
            </div>
            <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
        </div>
    <?php endif ?>

    <?php if (session()->getFlashdata('error')) : ?>
        <div class="alert alert-danger alert-dismissible bg-danger-lt" role="alert">
            <div class="d-flex">
                <div><i class="ti ti-alert-triangle fs-2 me-2"></i></div>
                <div><?= session()->getFlashdata('error') ?></div>
            </div>
            <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
        </div>
    <?php endif ?>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header border-0 pb-0 d-flex justify-content-between align-items-center">
            <h3 class="card-title text-muted fw-bold">Monitoring Blacklist</h3>
            <form action="" method="get" class="d-flex">
                <div class="input-icon">
                    <span class="input-icon-addon">
                        <i class="ti ti-search"></i>
                    </span>
                    <input type="text" name="search" class="form-control form-control-rounded" placeholder="Cari email..." value="<?= esc($search) ?>">
                </div>
            </form>
        </div>
        
        <div class="table-responsive mt-3">
            <table class="table card-table table-vcenter text-nowrap datatable">
                <thead>
                    <tr>
                        <th class="w-1">No</th>
                        <th>Alamat Email</th>
                        <th>Alasan Diblokir</th>
                        <th>Tanggal Masuk</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($suppressions)): ?>
                        <tr><td colspan="5" class="text-center py-5 text-muted"><i class="ti ti-shield-check fs-2 d-block mb-2"></i> Daftar hitam kosong. Reputasi pengiriman Anda aman!</td></tr>
                    <?php endif; ?>

                    <?php 
                        $i = 1 + (20 * ($pager->getCurrentPage() - 1)); 
                        foreach ($suppressions as $row): 
                    ?>
                    <tr>
                        <td><span class="text-muted"><?= $i++ ?></span></td>
                        <td>
                            <div class="d-flex align-items-center">
                                <span class="avatar avatar-sm bg-danger-lt me-2"><i class="ti ti-mail-off"></i></span>
                                <span class="fw-bold"><?= esc($row['email']) ?></span>
                            </div>
                        </td>
                        <td>
                            <?php if (stripos($row['reason'], 'UNSUBSCRIBE') !== false): ?>
                                <span class="badge bg-warning-lt"><i class="ti ti-user-x me-1"></i> Unsubscribed</span>
                            <?php elseif (stripos($row['reason'], 'Bounce') !== false): ?>
                                <span class="badge bg-danger-lt"><i class="ti ti-mail-forward me-1"></i> Hard Bounce</span>
                            <?php else: ?>
                                <span class="badge bg-secondary-lt"><i class="ti ti-ban me-1"></i> <?= esc($row['reason']) ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="text-muted">
                            <?= date('d M Y, H:i', strtotime($row['created_at'])) ?>
                        </td>
                        <td class="text-end">
                            <form action="<?= url_to('app.suppressions.delete', $row['id']) ?>" method="post" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin memutihkan (whitelist) email ini? Sistem akan bisa mengirim email kembali ke alamat ini.');">
                                <?= csrf_field() ?>
                                <input type="hidden" name="_method" value="DELETE">
                                <button type="submit" class="btn btn-sm btn-outline-success btn-pill" title="Putihkan Email (Whitelist)">
                                    <i class="ti ti-shield-check me-1"></i> Whitelist
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="card-footer d-flex align-items-center justify-content-between">
            <p class="m-0 text-muted">Menampilkan log daftar hitam</p>
            <?= $pager->links('default', 'default_full') ?>
        </div>
    </div>
</div>

<!-- Modal Tambah Blacklist -->
<div class="modal modal-blur fade" id="modal-add-blacklist" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content shadow-lg border-0">
            <form action="<?= url_to('app.suppressions.store') ?>" method="post">
                <?= csrf_field() ?>
                <div class="modal-header bg-danger-lt">
                    <h5 class="modal-title"><i class="ti ti-ban me-2"></i> Tambah ke Daftar Hitam</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <strong>Perhatian:</strong> Email yang dimasukkan ke sini tidak akan pernah menerima pesan dari kampanye apapun.
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">Alamat Email</label>
                        <input type="email" name="email" class="form-control" placeholder="contoh@domain.com" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Alasan Diblokir</label>
                        <select name="reason" class="form-select">
                            <option value="Permintaan Klien (Manual)">Permintaan Klien (Manual)</option>
                            <option value="Spam Trap / Banned">Spam Trap / Akun Fiktif</option>
                            <option value="Unsubscribed (Manual)">Berhenti Berlangganan (Manual)</option>
                            <option value="Lainnya">Lainnya</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-link link-secondary me-auto" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger"><i class="ti ti-shield-off me-1"></i> Blokir Sekarang</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
