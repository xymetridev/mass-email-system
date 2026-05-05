<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<style>
    @keyframes highlight {
    0% { background-color: #2fb34433; } /* Hijau transparan */
    100% { background-color: transparent; }
    }
    .stat-update {
    animation: highlight 1s ease-out;
    }
</style>
<div class="page-header d-print-none mb-3">
    <div class="row g-2 align-items-center">
        <div class="col">
            <h2 class="page-title text-danger">
                <i class="ti ti-shield-check me-2"></i> Administrator Dashboard
            </h2>
            <div class="text-muted mt-1">Pantau beban server, antrean email, dan aktivitas pengguna.</div>
        </div>
    </div>
</div>

<div class="row row-cards mb-4">
    <div class="col-md-6 col-lg-3">
    <div class="card card-sm border-bottom-danger shadow-sm">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-auto"><span class="bg-danger text-white avatar"><i class="ti ti-loader"></i></span></div>
                <div class="col">
                    <div class="font-weight-medium text-danger">Beban Antrean Server</div>
                    <div class="text-muted"><span id="stat-global-queue"><?= number_format($server_queue) ?></span> Email Pending</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="col-md-6 col-lg-3">
    <div class="card card-sm shadow-sm">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-auto"><span class="bg-blue text-white avatar"><i class="ti ti-mail-check"></i></span></div>
                <div class="col">
                    <div class="font-weight-medium">Terkirim Hari Ini</div>
                    <div class="text-muted"><span id="stat-sent-today">0</span> Email Terkirim</div>
                </div>
            </div>
        </div>
    </div>
</div>
    
    <div class="col-md-6 col-lg-3">
        <div class="card card-sm shadow-sm">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-auto"><span class="bg-primary text-white avatar"><i class="ti ti-users"></i></span></div>
                    <div class="col">
                        <div class="font-weight-medium">Pengguna Terdaftar</div>
                        <div class="text-muted"><?= $total_users ?> Akun Aktif</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-lg-3">
        <div class="card card-sm shadow-sm">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-auto"><span class="bg-success text-white avatar"><i class="ti ti-server-cog"></i></span></div>
                    <div class="col">
                        <div class="font-weight-medium">Jalur SMTP Server</div>
                        <div class="text-muted"><?= $total_smtp ?> Server Terhubung</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-lg-3">
        <div class="card card-sm shadow-sm">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-auto"><span class="bg-blue text-white avatar"><i class="ti ti-speakerphone"></i></span></div>
                    <div class="col">
                        <div class="font-weight-medium">Total Kampanye Global</div>
                        <div class="text-muted"><?= number_format($global_campaigns) ?> Kampanye</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistik Baru (Marketing Pulse) -->
    <div class="col-md-6 col-lg-3">
        <div class="card card-sm shadow-sm border-bottom-info">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-auto"><span class="bg-info text-white avatar"><i class="ti ti-mail-opened"></i></span></div>
                    <div class="col">
                        <div class="font-weight-medium">Total Opens (Global)</div>
                        <div class="text-muted"><?= number_format($total_opens) ?> Pembukaan</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="card card-sm shadow-sm border-bottom-purple">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-auto"><span class="bg-purple text-white avatar"><i class="ti ti-click"></i></span></div>
                    <div class="col">
                        <div class="font-weight-medium">Total Clicks (Global)</div>
                        <div class="text-muted"><?= number_format($total_clicks) ?> Klik Link</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- CHART: Performa Sistem 7 Hari -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-header">
                <h3 class="card-title">Performa Sistem (7 Hari Terakhir)</h3>
            </div>
            <div class="card-body">
                <div id="chart-global-performance" style="min-height: 350px;"></div>
            </div>
        </div>
    </div>
</div>

<div class="row row-cards">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header border-0">
                <h3 class="card-title">Live: Kampanye yang Sedang Berjalan (RUNNING)</h3>
            </div>
            <div class="table-responsive">
                <table class="table card-table table-vcenter text-nowrap table-hover">
                    <thead>
                        <tr>
                            <th>Nama Kampanye</th>
                            <th>Jalur Pengirim (SMTP)</th>
                            <th>Status Server</th>
                            <th>Dibuat Pada</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($active_campaigns)): ?>
                        <tr>
                            <td colspan="4" class="text-center py-4 text-muted">Mesin sedang istirahat. Tidak ada antrean yang sedang dieksekusi.</td>
                        </tr>
                        <?php endif; ?>

                        <?php foreach($active_campaigns as $ac): ?>
                        <tr>
                            <td class="font-weight-medium"><?= esc($ac->name) ?></td>
                            <td class="text-muted"><?= esc($ac->sender_name) ?></td>
                            <td>
                                <span class="status status-green">
                                    <span class="status-dot status-dot-animated"></span> Menyala (Active)
                                </span>
                            </td>
                            <td class="text-muted"><?= date('d M Y, H:i', strtotime($ac->created_at)) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const getTheme = () => document.documentElement.getAttribute('data-bs-theme') === 'dark' ? 'dark' : 'light';

        // Init Chart Global
        const chartGlobal = new ApexCharts(document.getElementById('chart-global-performance'), {
            chart: {
                type: "area",
                fontFamily: 'inherit',
                height: 350,
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
                name: "Total Terkirim",
                data: <?= $chartSent ?>
            },{
                name: "Total Dibuka (Opens)",
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
            colors: ['#d63939', '#2fb344'],
            legend: {
                show: true,
                position: 'bottom',
                offsetY: 12,
                markers: { width: 10, height: 10, radius: 100 },
                itemMargin: { horizontal: 8, vertical: 8 },
            },
        });
        
        chartGlobal.render();

        // 🔥 Real-time Theme Switcher untuk Chart Admin
        const observer = new MutationObserver(() => {
            const newTheme = getTheme();
            chartGlobal.updateOptions({
                theme: { mode: newTheme },
                tooltip: { theme: newTheme } // Paksa tooltip ganti tema
            });
        });
        observer.observe(document.documentElement, { attributes: true, attributeFilter: ['data-bs-theme'] });
    });

    function refreshAdminStats() {
        fetch('<?= site_url('admin/dashboard/getStats') ?>')
            .then(response => response.json())
            .then(data => {
                // Update Angka & Tambah Animasi untuk Antrean
                const elQueue = document.getElementById('stat-global-queue');
                if (elQueue && elQueue.innerText !== data.queue) {
                    elQueue.innerText = data.queue;
                    elQueue.parentElement.classList.add('stat-update');
                    setTimeout(() => elQueue.parentElement.classList.remove('stat-update'), 1000);
                }

                // Update Angka & Tambah Animasi untuk Terkirim Hari Ini
                const elSent = document.getElementById('stat-sent-today');
                if (elSent && elSent.innerText !== data.sent_today) {
                    elSent.innerText = data.sent_today;
                    elSent.parentElement.classList.add('stat-update');
                    setTimeout(() => elSent.parentElement.classList.remove('stat-update'), 1000);
                }
            })
            .catch(error => console.error('Error fetching stats:', error));
    }

    // Jalankan setiap 15 detik
    setInterval(refreshAdminStats, 15000);
</script>
<?= $this->endSection() ?>