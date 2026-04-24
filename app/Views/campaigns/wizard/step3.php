<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<link href="https://unpkg.com/grapesjs/dist/css/grapes.min.css" rel="stylesheet">
<script src="https://unpkg.com/grapesjs"></script>
<script src="https://unpkg.com/grapesjs-preset-newsletter"></script>

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
    <div class="steps steps-blue steps-counter mb-4"  style="border-left: none !important;">
        <a href="<?= url_to('app.campaigns.wizard', 1) ?>" class="step-item">Info Dasar</a>
        <a href="<?= url_to('app.campaigns.wizard', 2) ?>" class="step-item">Penerima</a>
        <span class="step-item active">Konten</span>
        <span class="step-item">Jadwal</span>
        <span class="step-item">Review</span>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-8">
                    <label class="form-label required">Subjek Email</label>
                    <input type="text" name="subject" id="subject" class="form-control" 
                         value="<?= esc($wizard['subject'] ?? '') ?>" placeholder="Contoh: Halo {{name}}, ada promo untukmu!">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Tersedia Merge Tags</label>
                    <div class="d-flex flex-wrap gap-1">
                        <?php foreach($availableTags as $tag): if($tag !== 'none'): ?>
                            <span class="badge bg-purple-lt cursor-pointer" onclick="insertTag('{{<?= $tag ?>}}')">{{<?= $tag ?>}}</span>
                        <?php endif; endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-2">
                <label class="form-label mb-0">Desain Email</label>
                <div class="btn-group">
                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modal-load-template">
                        <i class="ti ti-download me-1"></i> Pilih Template
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#modal-save-template">
                        <i class="ti ti-device-floppy me-1"></i> Simpan sbg Template
                    </button>
                </div>
            </div>

            <div id="gjs" style="height: 700px; border: 1px solid var(--tblr-border-color); border-radius: 4px;">
                <?php if (!empty($wizard['email_html'])): ?>
                    <?= $wizard['email_html'] ?>
                <?php else: ?>
                    <table style="width: 100%; padding: 20px; font-family: Helvetica, Arial, sans-serif;">
                        <tr>
                            <td align="center">
                                <h1 style="color: #206bc4;">Halo {{name}}!</h1>
                                <p>Selamat datang di editor email kami. Klik di sini untuk mulai mengedit teks ini.</p>
                                <a href="#" style="background-color: #206bc4; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px;">Klik Disini</a>
                            </td>
                        </tr>
                    </table>
                <?php endif; ?>
            </div>
        </div>

        <form action="<?= url_to('app.campaigns.wizard.process', 3) ?>" method="POST" id="formStep3">
            <?= csrf_field() ?>
            <input type="hidden" name="email_html" id="email_html">
            <input type="hidden" name="subject" id="final_subject">
            
            <div class="card-footer d-flex justify-content-between">
                <a href="<?= url_to('app.campaigns.wizard', 2) ?>" class="btn btn-link">Kembali</a>
                
                <div>
                    <?php if (($wizard['max_step'] ?? 1) >= 5): ?>
                        <button type="button" class="btn btn-success me-2" onclick="submitContent(true)">
                            Simpan & Kembali ke Review <i class="ti ti-check ms-1"></i>
                        </button>
                    <?php endif; ?>
                    
                    <button type="button" class="btn btn-primary" onclick="submitContent(false)">
                        Lanjut ke Penjadwalan <i class="ti ti-arrow-right ms-2"></i>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    function initGrapesJS() {
        // Cek apakah GrapesJS sudah ada
        if (typeof grapesjs === 'undefined') {
            setTimeout(initGrapesJS, 100);
            return;
        }

        // Trik Jitu: Ambil fungsi plugin langsung dari daftar plugin yang tersedia
        // Biasanya terdaftar dengan nama gjs-preset-newsletter
        
        const editor = grapesjs.init({
            container: '#gjs',
            fromElement: true,
            height: '700px',
            width: 'auto',
            storageManager: false,
            // Panggil plugin menggunakan string yang sudah dikenal oleh library-nya
            plugins: ['grapesjs-preset-newsletter', 'grapesjs-plugin-ckeditor'],
            pluginsOpts: {
                'grapesjs-preset-newsletter': {
                    modalLabelImport: 'Paste all your code here below and click import',
                    modalLabelExport: 'Copy the code and use it wherever you want',
                    codeViewerTheme: 'material',
                    importPlaceholder: '<table class="table"><tr><td class="cell">Hello world!</td></tr></table>',
                    cellStyle: {
                    'font-size': '12px',
                    'font-weight': 300,
                    'vertical-align': 'top',
                    color: 'rgb(111, 119, 125)',
                    margin: 0,
                    padding: 0,
                    }
                },
                'grapesjs-plugin-ckeditor': {
                    onToolbar: el => {
                    el.style.minWidth = '350px';
                    },
                    options: {
                    startupFocus: true,
                    extraAllowedContent: '*(*);*{*}', // Allows any class and any inline style
                    allowedContent: true, // Disable auto-formatting, class removing, etc.
                    enterMode: 2, // CKEDITOR.ENTER_BR,
                    extraPlugins: 'sharedspace,justify,colorbutton,panelbutton,font',
                    toolbar: [
                        { name: 'styles', items: ['Font', 'FontSize' ] },
                        ['Bold', 'Italic', 'Underline', 'Strike'],
                        {name: 'paragraph', items : [ 'NumberedList', 'BulletedList']},
                        {name: 'links', items: ['Link', 'Unlink']},
                        {name: 'colors', items: [ 'TextColor', 'BGColor' ]},
                    ],
                    }
                }
            }
        });

        window.editor = editor;

        // Memaksa Panel Blocks Terbuka Otomatis agar kamu bisa lihat isinya
        setTimeout(() => {
            if (editor && editor.Panels) {
                const openBlocksBtn = editor.Panels.getButton('views', 'open-blocks');
                if (openBlocksBtn) {
                    openBlocksBtn.set('active', 1);
                }
            }
        }, 1000);

        console.log('Editor Berhasil Diinisialisasi!');
    }

    // Pastikan urutan load: Window Load -> Init
    window.onload = initGrapesJS;

    function insertTag(tag) {
    if (!window.editor) return;

    // 1. Dapatkan komponen yang sedang dipilih
    const selected = window.editor.getSelected();

    // 2. Cek apakah user sedang dalam mode edit teks (RTE aktif)
    if (window.editor.EditingData && selected && selected.is('text')) {
        const rte = selected.getEl().contentEditable;
        
        if (rte) {
            // Gunakan perintah insertHTML langsung ke canvas GrapesJS
            window.editor.Canvas.getWindow().document.execCommand('insertHTML', false, tag);
            return; // Berhasil nempel, hentikan fungsi
        }
    }

    // 3. Fallback jika tidak sedang fokus di teks
    copyToClipboard(tag);
    showToast('Pilih area teks (sampai muncul kursor) lalu tempel atau klik lagi.');
}

// Fungsi copy cadangan (tetap perlu untuk jaga-jaga)
function copyToClipboard(text) {
    const el = document.createElement('textarea');
    el.value = text;
    document.body.appendChild(el);
    el.select();
    document.execCommand('copy');
    document.body.removeChild(el);
}

function showToast(message) {
    const container = document.getElementById('toast-container') || createToastContainer();
    const toast = document.createElement('div');
    toast.className = 'alert alert-important alert-info shadow-sm mb-2 animate__animated animate__fadeInRight';
    toast.style = 'min-width: 250px; pointer-events: auto;';
    toast.innerHTML = `<div class="d-flex">
        <i class="ti ti-info-circle me-2"></i>
        <div>${message}</div>
    </div>`;
    
    container.appendChild(toast);
    setTimeout(() => {
        toast.classList.replace('animate__fadeInRight', 'animate__fadeOutRight');
        setTimeout(() => toast.remove(), 500);
    }, 3000);
}

function createToastContainer() {
    const div = document.createElement('div');
    div.id = 'toast-container';
    div.style = 'position: fixed; bottom: 20px; right: 20px; z-index: 9999; pointer-events: none;';
    document.body.appendChild(div);
    return div;
}

    function submitContent(jumpToReview = false) {
        if (!window.editor) return;
        
        const html = window.editor.runCommand('gjs-get-inlined-html');
        document.getElementById('email_html').value = html;
        document.getElementById('final_subject').value = document.getElementById('subject').value;
        
        // Jika tombol jalan pintas diklik, tambahkan input tersembunyi
        if (jumpToReview) {
            let jumpInput = document.createElement('input');
            jumpInput.type = 'hidden';
            jumpInput.name = 'jump_to_review';
            jumpInput.value = 'yes';
            document.getElementById('formStep3').appendChild(jumpInput);
        }
        
        document.getElementById('formStep3').submit();
    }

    // Fungsi Load Template via AJAX
    function loadTemplate(id) {
        const url = "<?= url_to('user.templates.show', 999999) ?>".replace('999999', id);
        fetch(url)
            .then(res => res.json())
            .then(res => {
                if(res.status === 'success') {
                    window.editor.setComponents(res.data.content);
                    const modal = bootstrap.Modal.getInstance(document.getElementById('modal-load-template'));
                    modal.hide();
                    showToast('Template berhasil dimuat!');
                } else {
                    showToast('Gagal memuat template: ' + res.message, 'danger');
                }
            });
    }

    // Fungsi Save Template via AJAX
    function saveTemplate() {
        const name = document.getElementById('template_name_input').value;
        if(!name) {
            showToast('Nama template wajib diisi!', 'danger');
            return;
        }

        const html = window.editor.runCommand('gjs-get-inlined-html');
        const formData = new FormData();
        formData.append('name', name);
        formData.append('html', html);

        fetch(`<?= url_to('user.templates.store') ?>`, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': '<?= csrf_hash() ?>',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(res => {
            if(res.status === 'success') {
                const modal = bootstrap.Modal.getInstance(document.getElementById('modal-save-template'));
                modal.hide();
                showToast(res.message);
                // Reset input
                document.getElementById('template_name_input').value = '';
            } else {
                showToast('Gagal menyimpan: ' + res.message, 'danger');
            }
        })
        .catch(err => {
            console.error(err);
            showToast('Terjadi kesalahan sistem.', 'danger');
        });
    }
</script>

<!-- MODAL LOAD TEMPLATE -->
<div class="modal modal-blur fade" id="modal-load-template" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Pustaka Template</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="list-group list-group-flush">
                    <?php if(empty($templates)): ?>
                        <div class="text-center p-3 text-muted">Belum ada template tersimpan.</div>
                    <?php endif; ?>
                    <?php foreach($templates as $tpl): ?>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-bold"><?= esc($tpl['name']) ?></div>
                                <div class="text-muted small"><?= date('d M Y', strtotime($tpl['created_at'])) ?></div>
                            </div>
                            <button class="btn btn-sm btn-primary" onclick="loadTemplate(<?= $tpl['id'] ?>)">Gunakan</button>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL SAVE TEMPLATE -->
<div class="modal modal-blur fade" id="modal-save-template" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Simpan ke Pustaka</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Nama Template</label>
                    <input type="text" id="template_name_input" class="form-control" placeholder="Contoh: Template Promo Ramadhan">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn me-auto" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-success" onclick="saveTemplate()">Simpan</button>
            </div>
        </div>
    </div>
</div>

<!-- Toast Container -->
<div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 9999">
    <div id="saveToast" class="toast align-items-center text-white border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body" id="toastMessage"></div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>

<style>
    /* Agar GrapesJS menyesuaikan dengan Dark Mode Tabler sedikit */
    .gjs-one-bg { background-color: var(--tblr-bg-surface); }
    .gjs-two-color { color: var(--tblr-body-color); }
    .badge { cursor: pointer; }
</style>
<?= $this->endSection() ?>