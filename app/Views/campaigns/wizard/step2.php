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
    <div class="steps steps-blue steps-counter mb-4"  style="border-left: none !important;">
        <a href="<?= url_to('app.campaigns.wizard', 1) ?>" class="step-item">Info Dasar</a>
        <span class="step-item active">Penerima</span>
        <span class="step-item">Konten</span>
        <span class="step-item">Jadwal</span>
        <span class="step-item">Review</span>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header">
            <ul class="nav nav-tabs card-header-tabs" data-bs-toggle="tabs">
                <li class="nav-item">
                    <a href="#tabs-upload" class="nav-link <?= ($wizard['source_mode'] ?? 'upload') == 'upload' ? 'active' : '' ?>" data-bs-toggle="tab">
                        <i class="ti ti-upload me-2"></i> Upload File Baru
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#tabs-database" class="nav-link <?= ($wizard['source_mode'] ?? '') == 'database' ? 'active' : '' ?>" data-bs-toggle="tab">
                        <i class="ti ti-database me-2"></i> Pilih dari Kontak & Segmen
                    </a>
                </li>
            </ul>
        </div>
        <div class="card-body py-4">
            <div class="tab-content">
                <!-- Tab Upload -->
                <div class="tab-pane fade <?= ($wizard['source_mode'] ?? 'upload') == 'upload' ? 'show active' : '' ?>" id="tabs-upload">
                    <div class="text-center mb-4">
                        <h3 class="mb-3">Langkah 2: Siapa penerimanya?</h3>
                        <p class="text-muted">Upload file CSV atau TXT yang berisi daftar email target Anda.</p>
                        
                        <div class="mb-3 d-flex gap-2 align-items-center justify-content-center" style="max-width: 600px; margin: 0 auto;">
                            <div class="flex-grow-1 text-start">
                                <input type="file" id="contactFile" class="form-control" accept=".csv,.txt">
                            </div>
                            <div class="dropdown">
                                <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="ti ti-download me-2"></i> Template Target
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <a class="dropdown-item" href="<?= url_to('user.contacts.sample') ?>">
                                            <i class="ti ti-file-spreadsheet me-2 text-success"></i> Download format .CSV
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div id="previewArea" style="display:none" class="mt-4 text-start">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="hr-text m-0">Pratinjau & Edit Data</div>
                            <span class="badge bg-azure-lt" id="rowCountBadge">0 Kontak</span>
                        </div>

                        <div class="card-table-container border rounded mb-3" style="max-height: 400px; overflow: auto;">
                            <table class="table table-vcenter card-table table-nowrap" id="previewTable" style="min-width: 800px;">
                                <thead class="sticky-top bg-light">
                                    <tr id="tableHeader"></tr>
                                </thead>
                                <tbody id="tableBody"></tbody>
                            </table>
                        </div>

                        <!-- Opsi Simpan ke Master -->
                        <div class="card bg-blue-lt border-0 shadow-none mb-3">
                            <div class="card-body">
                                <label class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" name="save_to_master" id="saveToMaster" value="1">
                                    <span class="form-check-label font-weight-bold">Simpan ke Daftar Kontak Master?</span>
                                </label>
                                <div id="tagSelectArea" style="display:none">
                                    <label class="form-label small">Pilih Tag untuk Kontak Baru Ini (Opsional):</label>
                                    <select name="tag_id" class="form-select form-select-sm">
                                        <option value="">-- Tanpa Tag --</option>
                                        <?php foreach($tags as $t): ?>
                                            <option value="<?= $t['id'] ?>"><?= esc($t['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab Database -->
                <div class="tab-pane fade <?= ($wizard['source_mode'] ?? '') == 'database' ? 'show active' : '' ?>" id="tabs-database">
                    <div class="text-center py-4">
                        <h3>Pilih Segmentasi (Tag)</h3>
                        <p class="text-muted">Kirim email ke kontak yang sudah ada di database berdasarkan Tag.</p>
                        
                        <div class="row justify-content-center">
                            <div class="col-md-6 text-start">
                                <label class="form-label">Pilih Satu atau Lebih Tag:</label>
                                <div class="form-selectgroup form-selectgroup-boxes d-flex flex-column">
                                    <?php 
                                        $savedTags = explode(',', $wizard['db_tags'] ?? '');
                                    ?>
                                    <?php foreach($tags as $t): ?>
                                    <label class="form-selectgroup-item flex-fill">
                                        <input type="checkbox" name="selected_tags[]" value="<?= $t['id'] ?>" 
                                               class="form-selectgroup-input tag-checkbox" 
                                               <?= in_array($t['id'], $savedTags) ? 'checked' : '' ?>
                                               onchange="toggleDatabaseMode()">
                                        <div class="form-selectgroup-label d-flex align-items-center p-3">
                                            <div class="me-3">
                                                <span class="form-selectgroup-check"></span>
                                            </div>
                                            <div>
                                                <div class="font-weight-bold"><?= esc($t['name']) ?></div>
                                                <div class="text-muted small">Kirim ke semua kontak dengan tag ini.</div>
                                            </div>
                                        </div>
                                    </label>
                                    <?php endforeach; ?>
                                </div>
                                <?php if(empty($tags)): ?>
                                    <div class="alert alert-warning text-center">
                                        Belum ada Tag di database. Silakan buat di menu <a href="<?= url_to('user.contacts') ?>">Kontak & Segmen</a>.
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <form action="<?= url_to('app.campaigns.wizard.process', 2) ?>" method="POST" id="formStep2">
            <?= csrf_field() ?>
            <input type="hidden" name="contacts_json" id="contacts_json" value="<?= esc($wizard['contacts_json'] ?? '', 'attr') ?>">
            <input type="hidden" name="source_mode" id="source_mode" value="upload">
            <input type="hidden" name="db_tags" id="db_tags" value="">
            
            <!-- Elemen ini dipindah agar masuk form -->
            <div style="display:none">
                <input type="checkbox" name="save_to_master_form" id="saveToMasterForm" value="1">
                <input type="hidden" name="tag_id_form" id="tagIdForm" value="">
            </div>

            <div class="card-footer d-flex justify-content-between">
                <a href="<?= url_to('app.campaigns.wizard', 1) ?>" class="btn btn-link">Kembali</a>
                <div>
                    <?php if (($wizard['max_step'] ?? 1) >= 5): ?>
                        <button type="submit" name="jump_to_review" value="yes" class="btn btn-success me-2">
                            Simpan & Kembali ke Review <i class="ti ti-check ms-1"></i>
                        </button>
                    <?php endif; ?>
                    
                    <button type="submit" class="btn btn-primary" id="btnNext" disabled>
                        Lanjut ke Konten <i class="ti ti-arrow-right ms-1"></i>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    // JS Logic tambahan untuk sinkronisasi form
    document.getElementById('saveToMaster')?.addEventListener('change', function(e) {
        document.getElementById('saveToMasterForm').checked = e.target.checked;
        document.getElementById('tagSelectArea').style.display = e.target.checked ? 'block' : 'none';
    });

    document.querySelector('select[name="tag_id"]')?.addEventListener('change', function(e) {
        document.getElementById('tagIdForm').value = e.target.value;
    });

    function toggleDatabaseMode() {
        const checked = document.querySelectorAll('.tag-checkbox:checked');
        const nextBtn = document.getElementById('btnNext');
        const modeInput = document.getElementById('source_mode');
        const tagsInput = document.getElementById('db_tags');

        if (checked.length > 0) {
            nextBtn.disabled = false;
            modeInput.value = 'database';
            
            let selectedIds = [];
            checked.forEach(c => selectedIds.push(c.value));
            tagsInput.value = selectedIds.join(',');
        } else if(globalData.length === 0) {
            nextBtn.disabled = true;
            modeInput.value = 'upload';
        }
    }
</script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/PapaParse/5.3.2/papaparse.min.js"></script>

<script>

    function downloadTemplate(format) {
        // Isi template data dummy
        const header = "email,nama,perusahaan,kota\n";
        const row1 = "budi.santoso@example.com,Budi Santoso,PT Maju Mundur,Jakarta\n";
        const row2 = "siti.aminah@example.com,Siti Aminah,CV Berkah,Bandung\n";
        const row3 = "joko.anwar@example.com,Joko Anwar,Toko Kelontong,Surabaya\n";
        
        const content = header + row1 + row2 + row3;

        // Buat objek Blob (file virtual di browser)
        const blob = new Blob([content], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        
        // Buat elemen link tersembunyi untuk memicu download
        const link = document.createElement("a");
        link.setAttribute("href", url);
        link.setAttribute("download", "template_kontak_kampanye." + format);
        link.style.visibility = 'hidden';
        
        // Eksekusi download lalu hapus linknya
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }


    let globalData = []; 
    let columnMapping = []; // Menyimpan pilihan user: ['email', 'name', 'custom', 'none']

    document.addEventListener('DOMContentLoaded', function() {
        const rawData = document.getElementById('contacts_json').value;
        if (rawData && rawData !== "") {
            try {
                const data = JSON.parse(rawData);
                // Kembalikan data dan mapping dari JSON ke variabel global
                if (data.rows && data.mapping) {
                    globalData = data.rows;
                    columnMapping = data.mapping;
                }
                // Panggil renderTable (Bukan renderPreviewTable)
                renderTable(); 
            } catch (e) { 
                console.error("Data kontak gagal di-parse", e); 
            }
        }
        
        // Jalankan sinkronisasi mode database saat load
        toggleDatabaseMode();
    });

    document.getElementById('contactFile').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            Papa.parse(file, {
                header: false, // Tetap false agar kita bisa kontrol manual array-nya
                skipEmptyLines: true,
                complete: function(results) {
                    let parsedData = results.data;

                    if (parsedData.length > 0) {
                        // 1. Set default mapping (Kolom 1=email, Kolom 2=name)
                        columnMapping = parsedData[0].map((_, i) => i === 0 ? 'email' : (i === 1 ? 'name' : 'custom'));
                        
                        // 2. AUTO-DETECT HEADER
                        // Cek teks di baris pertama, kolom pertama
                        const firstCell = String(parsedData[0][0]).toLowerCase();
                        
                        // Jika tidak ada tanda '@', atau tulisannya persis 'email', kita anggap itu Header
                        if (!firstCell.includes('@') || firstCell === 'email') {
                            parsedData.shift(); // .shift() berfungsi menghapus baris pertama dari array
                        }

                        // 3. Masukkan sisa datanya ke tabel
                        globalData = parsedData;
                        renderTable();
                    }
                }
            });
        }
    });

    function renderTable() {
        const header = document.getElementById('tableHeader');
        const body = document.getElementById('tableBody');
        
        header.innerHTML = '';
        body.innerHTML = '';

        if (globalData.length === 0) return;

        // 1. Render Header Dinamis (Mengikuti columnMapping)
        globalData[0].forEach((_, i) => {
            const mapValue = columnMapping[i] || 'none'; // Ambil nilai mapping saat ini
            
            const th = document.createElement('th');
            th.innerHTML = `
                <select class="form-select mb-2" onchange="changeMapping(${i}, this.value)">
                    <option value="none" ${mapValue === 'none' ? 'selected' : ''}>Abaikan</option>
                    <option value="email" ${mapValue === 'email' ? 'selected' : ''}>Email</option>
                    <option value="name" ${mapValue === 'name' ? 'selected' : ''}>Nama</option>
                    <option value="custom" ${mapValue === 'custom' ? 'selected' : ''}>Custom</option>
                </select>
                <div class="text-muted">Kolom ${i+1}</div>
            `;
            header.appendChild(th);
        });
        header.innerHTML += `<th class="w-1 text-center"><br>Aksi</th>`;

        // 2. Render Body Dinamis
        globalData.forEach((row, rowIndex) => {
            let tr = document.createElement('tr');
            row.forEach((cell, colIndex) => {
                tr.innerHTML += `
                    <td contenteditable="true" oninput="updateData(${rowIndex}, ${colIndex}, this)">
                        ${cell || ''}
                    </td>`;
            });
            
            tr.innerHTML += `
                <td class="text-center">
                    <button type="button" class="btn btn-icon btn-ghost-danger btn-sm" onclick="removeRow(${rowIndex})">
                        <i class="ti ti-trash"></i>
                    </button>
                </td>`;
            body.appendChild(tr);
        });

        // Update Meta Data & Tampilan
        document.getElementById('rowCountBadge').innerText = globalData.length + ' Kontak';
        document.getElementById('btnNext').disabled = false;
        document.getElementById('previewArea').style.display = 'block';
        
        // Simpan setiap kali tabel di-render
        saveToHiddenInput();
    }

    function changeMapping(index, value) {
        columnMapping[index] = value;
        saveToHiddenInput();
    }

    function updateData(rowIndex, colIndex, element) {
        globalData[rowIndex][colIndex] = element.innerText.trim();
        saveToHiddenInput();
    }

    function removeRow(index) {
        globalData.splice(index, 1);
        renderTable();
    }

    function saveToHiddenInput() {
        // Kita simpan Data dan Mapping-nya sekaligus
        const finalPayload = {
            mapping: columnMapping,
            rows: globalData
        };
        document.getElementById('contacts_json').value = JSON.stringify(finalPayload);
    }
</script>

<style>
/* Header tetap di atas saat scroll vertikal */
.sticky-top {
    position: sticky;
    top: 0;
    z-index: 10;
    background-color: var(--tblr-bg-surface) !important; /* Adaptasi Dark/Light Mode */
    box-shadow: inset 0 -1px 0 var(--tblr-border-color);
}

/* Pastikan container mendukung scroll horizontal dengan smooth */
.card-table-container {
    overflow-x: auto; /* Scroll samping */
    overflow-y: auto; /* Scroll bawah */
    -webkit-overflow-scrolling: touch;
}

/* Memaksa cell untuk tidak "bungkus" teks (no-wrap) */
#previewTable td, #previewTable th {
    white-space: nowrap; 
    min-width: 150px; /* Jarak aman minimal per kolom */
    padding: 10px;
}

/* Efek khusus untuk kolom drop-down di header */
#tableHeader th select {
    min-width: 120px;
}

/* Base style untuk sel yang bisa diedit */
[contenteditable="true"] {
    padding: 4px 8px;
    border: 1px solid transparent;
    border-radius: 4px;
    transition: border-color .15s ease-in-out, box-shadow .15s ease-in-out;
    cursor: text;
    outline: none;
}

/* Style saat diklik (Fokus) - Hanya Border & Glow */
[contenteditable="true"]:focus {
    /* Menggunakan variabel warna primer Tabler agar otomatis ganti warna jika tema berubah */
    border-color: var(--tblr-primary); 
    /* Efek Glow tipis yang transparan agar aman di dark mode */
    box-shadow: 0 0 0 0.25rem rgba(var(--tblr-primary-rgb), .25);
}

/* Efek hover halus */
[contenteditable="true"]:hover {
    border-color: var(--tblr-border-color);
    background-color: rgba(var(--tblr-primary-rgb), 0.03); /* Highlight tipis banget */
}
</style>
<?= $this->endSection() ?>