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
        <span class="step-item active">Penerima</span>
        <span class="step-item">Konten</span>
        <span class="step-item">Jadwal</span>
        <span class="step-item">Review</span>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header">
            <h3 class="card-title">1. Pilih Sumber Kontak</h3>
        </div>
        <div class="card-header border-bottom-0 p-0">
            <ul class="nav nav-tabs card-header-tabs m-0" data-bs-toggle="tabs" id="sourceTabs">
                <li class="nav-item">
                    <a href="#tabs-upload" class="nav-link <?= ($wizard['source_mode'] ?? 'upload') == 'upload' ? 'active' : '' ?>" data-bs-toggle="tab" onclick="document.getElementById('source_mode').value='upload'">
                        <i class="ti ti-upload me-2"></i> Upload CSV
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#tabs-database" class="nav-link <?= ($wizard['source_mode'] ?? '') == 'database' ? 'active' : '' ?>" data-bs-toggle="tab" onclick="document.getElementById('source_mode').value='database'">
                        <i class="ti ti-database me-2"></i> Kontak & Segmen
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#tabs-manual" class="nav-link <?= ($wizard['source_mode'] ?? '') == 'manual' ? 'active' : '' ?>" data-bs-toggle="tab" onclick="document.getElementById('source_mode').value='manual'">
                        <i class="ti ti-pencil-plus me-2"></i> Input Manual
                    </a>
                </li>
            </ul>
        </div>
        <div class="card-body py-4">
            <div class="tab-content">

                <!-- ============ TAB UPLOAD CSV ============ -->
                <div class="tab-pane fade <?= ($wizard['source_mode'] ?? 'upload') == 'upload' ? 'show active' : '' ?>" id="tabs-upload">
                    <div class="text-center">
                        <h4 class="mb-2">Tambahkan dari File</h4>
                        <p class="text-muted small mb-3">Upload file CSV/TXT. Data akan otomatis digabungkan ke Daftar Penerima.</p>
                        <div class="d-flex gap-2 justify-content-center align-items-center" style="max-width: 500px; margin: 0 auto;">
                            <input type="file" id="contactFile" class="form-control" accept=".csv,.txt">
                            <a href="<?= url_to('user.contacts.sample') ?>" class="btn btn-outline-secondary" title="Download Template">
                                <i class="ti ti-download"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- ============ TAB DATABASE ============ -->
                <div class="tab-pane fade <?= ($wizard['source_mode'] ?? '') == 'database' ? 'show active' : '' ?>" id="tabs-database">
                    <div class="text-center py-4">
                        <h3 class="mb-1">Pilih Segmentasi (Tag)</h3>
                        <p class="text-muted">Kirim email ke kontak yang sudah ada di database berdasarkan Tag.</p>
                        
                        <div class="row justify-content-center">
                            <div class="col-md-6 text-start">
                                <label class="form-label">Pilih Satu atau Lebih Tag:</label>
                                <div class="form-selectgroup form-selectgroup-boxes d-flex flex-column">
                                    <?php $savedTags = explode(',', $wizard['db_tags'] ?? ''); ?>
                                    <?php foreach($tags as $t): ?>
                                    <label class="form-selectgroup-item flex-fill">
                                        <input type="checkbox" name="selected_tags[]" value="<?= $t['id'] ?>" 
                                               class="form-selectgroup-input tag-checkbox"
                                               data-name="<?= esc($t['name']) ?>"
                                               <?= in_array($t['id'], $savedTags) ? 'checked' : '' ?>
                                               onchange="handleTagChange(this)">
                                        <div class="form-selectgroup-label d-flex align-items-center p-3">
                                            <div class="me-3">
                                                <span class="form-selectgroup-check"></span>
                                            </div>
                                            <div>
                                                <div class="font-weight-bold"><?= esc($t['name']) ?></div>
                                                <div class="text-muted small">Klik untuk menarik semua kontak dengan tag ini ke daftar.</div>
                                            </div>
                                        </div>
                                    </label>
                                    <?php endforeach; ?>
                                </div>

                                <?php if(empty($tags)): ?>
                                    <div class="alert alert-warning text-center mt-3">
                                        Belum ada Tag di database. Silakan buat di menu <a href="<?= url_to('user.contacts') ?>">Kontak & Segmen</a>.
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ============ TAB MANUAL INPUT ============ -->
                <div class="tab-pane fade <?= ($wizard['source_mode'] ?? '') == 'manual' ? 'show active' : '' ?>" id="tabs-manual">
                    <div class="text-center">
                        <h4 class="mb-2">Tambahkan Manual</h4>
                        <div class="row justify-content-center">
                            <div class="col-md-8">
                                <div class="input-group mb-2">
                                    <input type="text" id="manualEmailInput" class="form-control" placeholder="nama@domain.com">
                                    <input type="text" id="manualNameInput" class="form-control" placeholder="Nama (opsional)">
                                    <button class="btn btn-primary" type="button" onclick="addManualEmail()">
                                        <i class="ti ti-plus me-1"></i> Tambah
                                    </button>
                                </div>
                                <div id="manualEmailError" class="text-danger small text-start" style="display:none"></div>
                            </div>
                        </div>
                    </div>
                </div>

            </div><!-- end tab-content -->
        </div>
    </div>

    <!-- ============ DAFTAR PENERIMA (MASTER TABLE) ============ -->
    <div class="card shadow-sm border-0">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title">2. Daftar Penerima Gabungan</h3>
            <div class="d-flex gap-2">
                <span class="badge bg-azure-lt px-2 py-1 fs-5" id="totalBadge">0 Total</span>
                <span class="badge bg-success-lt px-2 py-1 fs-5" id="validBadge" style="display:none;">0 Valid</span>
                <span class="badge bg-danger-lt px-2 py-1 fs-5" id="invalidBadge" style="display:none;">0 Invalid</span>
            </div>
        </div>
        
        <div class="card-body p-0">
            <div id="emptyState" class="text-center py-5 text-muted">
                <i class="ti ti-users fs-1 d-block mb-2 text-gray-300"></i>
                Belum ada kontak yang ditambahkan.<br>
                Silakan gunakan salah satu sumber di atas.
            </div>

            <div id="tableContainer" class="table-responsive" style="display:none; max-height: 400px; overflow-y: auto;">
                <table class="table table-vcenter table-hover table-nowrap mb-0" id="previewTable">
                    <thead class="sticky-top bg-white shadow-sm">
                        <tr>
                            <th class="w-1 text-center">#</th>
                            <th>Alamat Email</th>
                            <th>Nama Lengkap</th>
                            <th>Sumber Data</th>
                            <th class="w-1 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody">
                        <!-- Render via JS -->
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card-footer" id="masterSaveArea" style="display:none;">
            <label class="form-check form-switch mb-2">
                <input class="form-check-input" type="checkbox" id="saveToMasterToggle" onchange="toggleSaveMaster(this.checked)">
                <span class="form-check-label font-weight-bold text-primary">Simpan kontak-kontak baru ini ke Database Master?</span>
            </label>
            <div id="saveMasterTagArea" class="ms-5" style="display:none;">
                <label class="form-label small text-muted mb-1">Pilih Tag untuk kontak baru ini (Opsional):</label>
                <select class="form-select form-select-sm w-auto" onchange="document.getElementById('tagIdForm').value = this.value">
                    <option value="">-- Tanpa Tag --</option>
                    <?php foreach($tags as $t): ?>
                        <option value="<?= $t['id'] ?>"><?= esc($t['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <p class="text-muted small mt-1 mb-0">Sistem akan membuang duplikat otomatis. Kontak yang sudah ada tidak akan digandakan.</p>
            </div>
        </div>

    <!-- FORM SUBMIT -->
    <form action="<?= url_to('app.campaigns.wizard.process', 2) ?>" method="POST" id="formStep2" class="mt-4">
        <?= csrf_field() ?>
        <!-- Nilai Final JSON -->
        <input type="hidden" name="contacts_json" id="contacts_json" value="<?= esc($wizard['contacts_json'] ?? '', 'attr') ?>">
        
        <!-- Untuk State Restoration -->
        <input type="hidden" name="source_mode" id="source_mode" value="<?= $wizard['source_mode'] ?? 'upload' ?>">
        <input type="hidden" name="db_tags" id="db_tags" value="<?= $wizard['db_tags'] ?? '' ?>">
        
        <input type="hidden" name="save_to_master_form" id="saveToMasterForm" value="0">
        <input type="hidden" name="tag_id_form" id="tagIdForm" value="">

        <div class="d-flex justify-content-between align-items-center">
            <a href="<?= url_to('app.campaigns.wizard', 1) ?>" class="btn btn-link text-muted">
                <i class="ti ti-arrow-left me-1"></i> Kembali ke Info Dasar
            </a>
            <div>
                <?php if (($wizard['max_step'] ?? 1) >= 5): ?>
                    <button type="button" class="btn btn-success me-2" onclick="submitStep2(true)" id="btnSaveReview" disabled>
                        Simpan & Ke Review <i class="ti ti-check ms-1"></i>
                    </button>
                <?php endif; ?>
                <button type="button" class="btn btn-primary" id="btnNext" onclick="submitStep2(false)" disabled>
                    Lanjut ke Konten Email <i class="ti ti-arrow-right ms-1"></i>
                </button>
            </div>
        </div>
    </form>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/PapaParse/5.3.2/papaparse.min.js"></script>
<script>
/* =====================================================
   GLOBAL STATE & DATA STRUCTURE
   Data: { email: string, name: string, source: string }
   ===================================================== */
let globalData = []; 

function isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.trim().toLowerCase());
}

function normalizeEmail(email) {
    return email.trim().toLowerCase();
}

document.addEventListener('DOMContentLoaded', function () {
    // 1. Restore Data from Hidden Input (if user pressed 'Back' from Step 3)
    const rawData = document.getElementById('contacts_json').value;
    if (rawData && rawData !== '') {
        try {
            const data = JSON.parse(rawData);
            if (data.rows && data.mapping) {
                const emailIdx = data.mapping.indexOf('email');
                const nameIdx  = data.mapping.indexOf('name');
                if (emailIdx !== -1) {
                    globalData = data.rows.map(r => ({
                        email: r[emailIdx],
                        name: nameIdx !== -1 ? r[nameIdx] : '',
                        source: 'Tersimpan (Draft)'
                    }));
                }
            }
        } catch (e) { console.error('Gagal parse json', e); }
    }

    // 2. Restore Tags if any were checked (Trigger AJAX to ensure freshness, or skip if we trust the JSON)
    // Actually, if we restored from JSON, the tag data is already in globalData as "Tersimpan".
    // We don't need to auto-fetch on load unless we want to refresh. Let's just render what we have.
    renderTable();
});

/* =====================================================
   ADD TO GLOBAL DATA WITH DEDUPLICATION
   ===================================================== */
function addContacts(contactsArray) {
    let added = 0;
    let duplicate = 0;

    contactsArray.forEach(newC => {
        const normEmail = normalizeEmail(newC.email);
        if (!normEmail) return;

        // Cek duplikat di globalData
        const exists = globalData.some(existing => normalizeEmail(existing.email) === normEmail);
        
        if (!exists) {
            globalData.unshift({
                email: normEmail,
                name: newC.name.trim(),
                source: newC.source
            });
            added++;
        } else {
            duplicate++;
        }
    });

    renderTable();
    return { added, duplicate };
}

/* =====================================================
   RENDER TABLE
   ===================================================== */
function renderTable() {
    const tbody = document.getElementById('tableBody');
    tbody.innerHTML = '';

    const emptyState = document.getElementById('emptyState');
    const tableCont  = document.getElementById('tableContainer');
    const btnNext    = document.getElementById('btnNext');
    const btnSaveRev = document.getElementById('btnSaveReview');
    const saveArea   = document.getElementById('masterSaveArea');

    if (globalData.length === 0) {
        emptyState.style.display = 'block';
        tableCont.style.display = 'none';
        saveArea.style.display = 'none';
        btnNext.disabled = true;
        if(btnSaveRev) btnSaveRev.disabled = true;
        updateBadges(0, 0, 0);
        updateHiddenJson();
        return;
    }

    emptyState.style.display = 'none';
    tableCont.style.display = 'block';
    saveArea.style.display = 'block';

    let validCount = 0;
    let invalidCount = 0;

    globalData.forEach((row, index) => {
        const isInvalid = !isValidEmail(row.email);
        if (isInvalid) invalidCount++; else validCount++;

        const trClass = isInvalid ? 'table-danger' : '';
        const sourceBadgeClass = getSourceBadgeClass(row.source);

        const tr = document.createElement('tr');
        tr.className = trClass;
        tr.innerHTML = `
            <td class="text-center text-muted small">${index + 1}</td>
            <td class="fw-medium">
                ${isInvalid ? '<i class="ti ti-alert-triangle text-danger me-1" title="Format tidak valid"></i>' : ''}
                <span contenteditable="true" onblur="updateCell(${index}, 'email', this.innerText)">${row.email}</span>
            </td>
            <td>
                <span contenteditable="true" onblur="updateCell(${index}, 'name', this.innerText)" class="text-muted">${row.name || '-'}</span>
            </td>
            <td><span class="badge ${sourceBadgeClass}">${row.source}</span></td>
            <td class="text-center">
                <button type="button" class="btn btn-icon btn-ghost-danger btn-sm" onclick="removeRow(${index})">
                    <i class="ti ti-x"></i>
                </button>
            </td>
        `;
        tbody.appendChild(tr);
    });

    updateBadges(globalData.length, validCount, invalidCount);
    
    // Disable next if absolutely 0 valid emails
    const canProceed = validCount > 0;
    btnNext.disabled = !canProceed;
    if(btnSaveRev) btnSaveRev.disabled = !canProceed;

    updateHiddenJson();
}

function getSourceBadgeClass(source) {
    if (source === 'Upload CSV') return 'bg-purple-lt';
    if (source === 'Input Manual') return 'bg-orange-lt';
    if (source === 'Tersimpan (Draft)') return 'bg-secondary-lt';
    return 'bg-blue-lt'; // Untuk Tag Database
}

function updateCell(index, key, newValue) {
    if (key === 'email') newValue = normalizeEmail(newValue);
    globalData[index][key] = newValue;
    renderTable(); // Re-render to update validation colors
}

function removeRow(index) {
    globalData.splice(index, 1);
    renderTable();
}

function updateBadges(total, valid, invalid) {
    document.getElementById('totalBadge').innerText = total + ' Total';
    
    const vb = document.getElementById('validBadge');
    vb.innerText = valid + ' Valid';
    vb.style.display = valid > 0 ? 'inline-block' : 'none';

    const ib = document.getElementById('invalidBadge');
    ib.innerText = invalid + ' Invalid';
    ib.style.display = invalid > 0 ? 'inline-block' : 'none';
}

function updateHiddenJson() {
    // Konversi globalData ke format struktur API (mapping & rows)
    const validRows = globalData
        .filter(r => isValidEmail(r.email))
        .map(r => [r.email, r.name]);

    document.getElementById('contacts_json').value = JSON.stringify({
        mapping: ['email', 'name'],
        rows: validRows
    });
}


/* =====================================================
   TAB 1: UPLOAD CSV LOGIC
   ===================================================== */
document.getElementById('contactFile').addEventListener('change', function (e) {
    const file = e.target.files[0];
    if (!file) return;

    Papa.parse(file, {
        header: false,
        skipEmptyLines: true,
        complete: function (results) {
            let data = results.data;
            if (data.length === 0) return;

            // Auto-detect header
            const firstCell = String(data[0][0]).toLowerCase().trim();
            if (!firstCell.includes('@') || firstCell === 'email') {
                data.shift();
            }

            const newContacts = data.map(row => ({
                email: row[0] || '',
                name: row[1] || '',
                source: 'Upload CSV'
            }));

            // Hapus data upload sebelumnya agar tidak double jika user upload 2x file yg beda
            globalData = globalData.filter(r => r.source !== 'Upload CSV');
            
            const stats = addContacts(newContacts);
            showAlert('File Diproses', `${stats.added} kontak berhasil ditambahkan. ${stats.duplicate} duplikat diabaikan.`, 'success');
            
            // Reset input file
            e.target.value = ''; 
        }
    });
});


/* =====================================================
   TAB 2: DATABASE TAG LOGIC (AJAX)
   ===================================================== */
function handleTagChange(checkbox) {
    const tagId = checkbox.value;
    const tagName = checkbox.getAttribute('data-name');
    const sourceName = `Segmen: ${tagName}`;

    // Update hidden input for state
    updateTagsInput();

    if (checkbox.checked) {
        // Tampilkan loading visual di tabel
        document.getElementById('tableBody').insertAdjacentHTML('afterbegin', `<tr id="loading-${tagId}"><td colspan="5" class="text-center text-muted"><div class="spinner-border spinner-border-sm me-2"></div> Menarik kontak dari segmen...</td></tr>`);

        // Fetch via AJAX
        fetch(`<?= base_url('campaigns/wizard/tag-contacts') ?>/${tagId}`)
            .then(res => res.json())
            .then(res => {
                const loadingRow = document.getElementById(`loading-${tagId}`);
                if(loadingRow) loadingRow.remove();

                if (res.status === 'success' && res.data.length > 0) {
                    const newContacts = res.data.map(c => ({
                        email: c.email,
                        name: c.name,
                        source: sourceName
                    }));
                    addContacts(newContacts);
                }
            })
            .catch(err => {
                console.error(err);
                const loadingRow = document.getElementById(`loading-${tagId}`);
                if(loadingRow) loadingRow.remove();
                showAlert('Gagal Ambil Data', 'Tidak dapat mengambil kontak dari server. Silakan coba lagi.', 'danger');
            });
    } else {
        // Hapus kontak yang berasal dari tag ini
        globalData = globalData.filter(r => r.source !== sourceName);
        renderTable();
    }
}

function updateTagsInput() {
    const checked = document.querySelectorAll('.tag-checkbox:checked');
    const ids = Array.from(checked).map(c => c.value);
    document.getElementById('db_tags').value = ids.join(',');
}


/* =====================================================
   TAB 3: MANUAL INPUT LOGIC
   ===================================================== */
document.getElementById('manualEmailInput').addEventListener('keydown', function (e) {
    if (e.key === 'Enter') { e.preventDefault(); addManualEmail(); }
});
document.getElementById('manualNameInput').addEventListener('keydown', function (e) {
    if (e.key === 'Enter') { e.preventDefault(); addManualEmail(); }
});

function addManualEmail() {
    const emailInput = document.getElementById('manualEmailInput');
    const nameInput  = document.getElementById('manualNameInput');
    const errEl      = document.getElementById('manualEmailError');
    const email      = normalizeEmail(emailInput.value);
    const name       = nameInput.value.trim();

    errEl.style.display = 'none';

    if (!email) {
        errEl.innerText = '⚠ Email wajib diisi.';
        errEl.style.display = 'block';
        return;
    }
    if (!isValidEmail(email)) {
        errEl.innerText = '⚠ Format email tidak valid.';
        errEl.style.display = 'block';
        return;
    }

    const stats = addContacts([{ email, name, source: 'Input Manual' }]);
    
    if (stats.duplicate > 0) {
        errEl.innerText = '⚠ Email tersebut sudah ada di tabel bawah.';
        errEl.style.display = 'block';
    } else {
        emailInput.value = '';
        nameInput.value  = '';
        emailInput.focus();
    }
}


/* =====================================================
   SUBMIT ACTION
   ===================================================== */
function submitStep2(jumpToReview) {
    // Karena updateHiddenJson() dipanggil tiap kali renderTable(), 
    // contacts_json sudah berisi data yg bersih dan valid.
    
    const validCount = globalData.filter(r => isValidEmail(r.email)).length;
    if (validCount === 0) {
        showAlert('Daftar Kosong', 'Daftar Penerima tidak boleh kosong atau tidak valid.', 'warning');
        return;
    }

    if (jumpToReview) {
        const inp = document.createElement('input');
        inp.type  = 'hidden';
        inp.name  = 'jump_to_review';
        inp.value = 'yes';
        document.getElementById('formStep2').appendChild(inp);
    }

    document.getElementById('formStep2').submit();
}

function toggleSaveMaster(isChecked) {
    document.getElementById('saveToMasterForm').value = isChecked ? '1' : '0';
    document.getElementById('saveMasterTagArea').style.display = isChecked ? 'block' : 'none';
}
</script>
<?= $this->endSection() ?>