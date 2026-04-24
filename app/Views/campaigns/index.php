<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<div class="row mb-3 align-items-center">
    <div class="col">
        <h2 class="page-title">Manajemen Kampanye</h2>
        <div class="text-muted mt-1">Pantau dan kendalikan seluruh aktivitas pengiriman email Anda.</div>
    </div>
    <div class="col-auto ms-auto">
        <a href="<?= url_to('app.campaigns.wizard', 1) ?>" class="btn btn-primary shadow-sm">
            <i class="ti ti-plus me-2"></i> Buat Kampanye Baru
        </a>
    </div>
</div>

<div class="card shadow-sm border-0" id="table-container">
    <?= $this->include('campaigns/_table') ?>
</div>

<script>

    document.addEventListener('click', function(e) {
        // Cari tahu apakah yang diklik adalah link pagination (class .pagination a)
        let pageLink = e.target.closest('.pagination a');
        
        if (pageLink) {
            e.preventDefault(); // Cegah browser melakukan refresh / pindah halaman
            let targetUrl = pageLink.href; // Ambil URL halaman (misal: ?page=2)
            
            // Beri efek loading tipis-tipis di tabel
            document.getElementById('table-container').style.opacity = '0.5';
            
            // Panggil fungsi untuk mengambil data baru
            loadTableData(targetUrl);
        }
    });

    // 2. FUNGSI UNTUK MENGAMBIL DAN MENIMPA KONTEN (SET CONTENT)
    function loadTableData(url) {
        fetch(url, {
            headers: {
                // KUNCI PENTING: Memberi tahu CI4 bahwa ini adalah request AJAX
                'X-Requested-With': 'XMLHttpRequest' 
            }
        })
        .then(response => response.text()) // Ambil responnya sebagai teks HTML
        .then(html => {
            // TIMPA ISI LAMA DENGAN TABEL BARU
            document.getElementById('table-container').innerHTML = html;
            // Kembalikan opacity agar terang kembali
            document.getElementById('table-container').style.opacity = '1';
        })
        .catch(error => {
            console.error('Gagal mengambil data:', error);
            document.getElementById('table-container').style.opacity = '1';
        });
    }
    
    let statusInterval = setInterval(() => {
        fetch('<?= url_to('app.campaigns.check_statuses') ?>')
            .then(res => res.status === 401 ? clearInterval(statusInterval) : res.json())
            .then(data => {
                if (!data) return;
                data.forEach(camp => {
                    const badge = document.getElementById('status-badge-' + camp.id);
                    if (badge && !badge.innerText.includes(camp.status)) location.reload();
                });
            }).catch(e => console.error(e));
    }, 10000);
</script>
<?= $this->endSection() ?>