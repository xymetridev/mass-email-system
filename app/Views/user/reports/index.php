<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<div class="page-header d-print-none mb-4">
    <div class="row g-2 align-items-center">
        <div class="col">
            <h2 class="page-title">
                Laporan Pengiriman
            </h2>
            <div class="text-muted mt-1">Rekapitulasi performa pengiriman email</div>
        </div>
        <div class="col-auto ms-auto d-print-none">
            <button type="button" class="btn btn-primary" onclick="javascript:window.print();">
                <i class="ti ti-printer me-2"></i> Cetak Laporan
            </button>
        </div>
    </div>
</div>

<div class="row row-cards mb-4">
    <div class="col-md-6 col-lg-4">
        <div class="card card-sm shadow-sm">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-auto"><span class="bg-success text-white avatar"><i class="ti ti-mail-check"></i></span></div>
                    <div class="col">
                        <div class="font-weight-medium">Berhasil Terkirim</div>
                        <div class="h2 mb-0"><?= number_format(array_sum(array_column($daily_stats, 'total'))) ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 col-lg-4">
        <div class="card card-sm shadow-sm">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-auto"><span class="bg-blue text-white avatar"><i class="ti ti-chart-line"></i></span></div>
                    <div class="col">
                        <div class="font-weight-medium">Rata-rata Harian</div>
                        <div class="h2 mb-0">
                            <?= count($daily_stats) > 0 ? number_format(array_sum(array_column($daily_stats, 'total')) / count($daily_stats), 1) : 0 ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header border-0">
        <h3 class="card-title">Tren Pengiriman 7 Hari Terakhir</h3>
    </div>
    <div class="table-responsive">
        <table class="table table-vcenter card-table table-hover">
            <thead>
                <tr>
                    <th>Tanggal Pengiriman</th>
                    <th class="text-center">Jumlah Email Berhasil</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($daily_stats)): ?>
                <tr>
                    <td colspan="3" class="text-center py-4 text-muted">Belum ada aktivitas pengiriman dalam 7 hari terakhir.</td>
                </tr>
                <?php endif; ?>

                <?php foreach ($daily_stats as $stat): ?>
                <tr>
                    <td class="font-weight-medium"><?= date('l, d F Y', strtotime($stat->date)) ?></td>
                    <td class="text-center">
                        <span class="h3 mb-0"><?= number_format($stat->total) ?></span>
                    </td>
                    <td>
                        <span class="badge bg-success-lt">Stable</span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="card-footer text-muted small">
        Data ini diperbarui secara real-time berdasarkan log database pengiriman.
    </div>
</div>

<?= $this->endSection() ?>