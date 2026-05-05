<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <h2 class="page-title">Selamat Datang, <?= auth()->user()->username ?>!</h2>
                <div class="text-muted small">Berikut adalah ringkasan performa pengiriman email Anda hari ini.</div>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <div class="row row-cards">
            <div class="col-md-6 col-lg-3">
                <div class="card">
                    <div class="card-body">
                        <div class="subheader">Total Kampanye</div>
                        <div class="h1 m-0"><?= $total_campaigns ?></div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="card">
                    <div class="card-body">
                        <div class="subheader text-success">Email Terkirim</div>
                        <div class="h1 m-0 text-success"><?= number_format($total_sent) ?></div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="card">
                    <div class="card-body">
                        <div class="subheader text-danger">Gagal / Bounce</div>
                        <div class="h1 m-0 text-danger"><?= number_format($total_failed) ?></div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="card">
                    <div class="card-body">
                        <div class="subheader">Success Rate</div>
                        <div class="h1 m-0"><?= $success_rate ?>%</div>
                    </div>
                </div>
            </div>

            <!-- CHART: 7 Hari Terakhir -->
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-header">
                        <h3 class="card-title">Tren Performa (7 Hari Terakhir)</h3>
                    </div>
                    <div class="card-body">
                        <div id="chart-performance" style="min-height: 300px;"></div>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header"><h3 class="card-title">Kampanye Terbaru</h3></div>
                    <div class="table-responsive">
                        <table class="table card-table table-vcenter">
                            <thead>
                                <tr>
                                    <th>Nama</th>
                                    <th>Status</th>
                                    <th>Waktu</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($recent_campaigns as $rc): ?>
                                <tr>
                                    <td><?= esc($rc->name) ?></td>
                                    <td><span class="badge bg-<?= $rc->status == 'COMPLETED' ? 'blue' : 'secondary' ?>-lt"><?= $rc->status ?></span></td>
                                    <td class="text-muted small"><?= date('d M Y', strtotime($rc->created_at)) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card bg-primary text-primary-fg">
                    <div class="card-body">
                        <h3 class="card-title">Status Mesin (Cron)</h3>
                        <p>Mesin pengirim sedang aktif. Pastikan server tetap berjalan untuk menjaga antrean tetap lancar.</p>
                        <div class="mt-3">
                            <a href="<?= url_to('app.campaigns') ?>" class="btn btn-primary-light">Lihat Semua Antrean</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const getTheme = () => document.documentElement.getAttribute('data-bs-theme') === 'dark' ? 'dark' : 'light';
        
        const chartPerformance = new ApexCharts(document.getElementById('chart-performance'), {
            chart: {
                type: "area",
                fontFamily: 'inherit',
                height: 300,
                parentHeightOffset: 0,
                toolbar: { show: false },
                animations: { enabled: true },
            },
            theme: {
                mode: getTheme(),
            },
            dataLabels: { enabled: false },
            fill: { opacity: .16, type: 'solid' },
            stroke: { width: 2, lineCap: "round", curve: "smooth" },
            series: [{
                name: "Email Terkirim",
                data: <?= $chartSent ?>
            },{
                name: "Email Dibuka (Opens)",
                data: <?= $chartOpens ?>
            }],
            grid: {
                padding: { top: -20, right: 0, left: -4, bottom: -4 },
                strokeDashArray: 4,
            },
            xaxis: {
                labels: { padding: 0 },
                tooltip: { enabled: false },
                axisBorder: { show: false },
                categories: <?= $chartDates ?>,
            },
            yaxis: {
                labels: { padding: 4 }
            },
            colors: ['#206bc4', '#2fb344'],
            legend: {
                show: true,
                position: 'bottom',
                offsetY: 12,
                markers: { width: 10, height: 10, radius: 100 },
                itemMargin: { horizontal: 8, vertical: 8 },
            },
        });
        
        chartPerformance.render();

        // 🔥 Real-time Theme Switcher untuk Chart
        const observer = new MutationObserver(() => {
            const newTheme = getTheme();
            chartPerformance.updateOptions({
                theme: { mode: newTheme },
                tooltip: { theme: newTheme } // Paksa tooltip ganti tema
            });
        });
        observer.observe(document.documentElement, { attributes: true, attributeFilter: ['data-bs-theme'] });
    });

    function refreshStats() {
        fetch('<?= site_url('user/dashboard/getStats') ?>')
            .then(response => response.json())
            .then(data => {
                if(document.getElementById('stat-sent')) {
                    document.getElementById('stat-sent').innerText = data.total_sent;
                }
                if(document.getElementById('stat-queue')) {
                    document.getElementById('stat-queue').innerText = data.server_queue;
                }
            });
    }

    // Refresh setiap 30 detik
    setInterval(refreshStats, 30000);
</script>
<?= $this->endSection() ?>