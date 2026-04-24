<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<!-- GrapesJS Styles -->
<link rel="stylesheet" href="https://unpkg.com/grapesjs/dist/css/grapes.min.css">
<link rel="stylesheet" href="https://unpkg.com/grapesjs-preset-newsletter/dist/grapesjs-preset-newsletter.css">

<div class="page-header d-print-none mb-4">
    <div class="row align-items-center">
        <div class="col">
            <h2 class="page-title">Edit Template Pribadi</h2>
            <div class="text-muted mt-1">Mengedit: <strong><?= esc($template->name) ?></strong></div>
        </div>
        <div class="col-auto ms-auto">
            <a href="<?= url_to('user.templates') ?>" class="btn btn-outline-secondary">Batal</a>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal-save-template">
                <i class="ti ti-device-floppy me-2"></i> Simpan Perubahan
            </button>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="p-2 border-bottom bg-light d-flex justify-content-center">
            <span class="badge bg-blue-lt">
                <i class="ti ti-info-circle me-1"></i> Merge Tags: 
                <?php foreach($availableTags as $tag): ?>
                    <code>{{<?= $tag ?>}}</code>
                <?php endforeach; ?>
            </span>
        </div>
        <div id="gjs" style="height: 750px;"></div>
    </div>
</div>

<!-- Modal Simpan (Update) -->
<div class="modal modal-blur fade" id="modal-save-template" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content shadow-lg">
            <div class="modal-header">
                <h5 class="modal-title">Perbarui Template Pribadi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label required">Nama Template</label>
                    <input type="text" id="template_name" class="form-control" value="<?= esc($template->name) ?>" placeholder="Contoh: Desain Promo Mingguan">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-link link-secondary me-auto" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="btn-do-save" onclick="updateTemplate()">
                    <i class="ti ti-check me-2"></i> Perbarui Sekarang
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Toast Container -->
<div class="toast-container position-fixed bottom-0 end-0 p-3">
    <div id="saveToast" class="toast align-items-center text-white border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body" id="toastMessage"></div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>

<script src="https://unpkg.com/grapesjs"></script>
<script src="https://unpkg.com/grapesjs-preset-newsletter"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        window.editor = grapesjs.init({
            container: '#gjs',
            height: '750px',
            storageManager: false,
            plugins: ['grapesjs-preset-newsletter'],
            pluginsOpts: {
                'grapesjs-preset-newsletter': {}
            }
        });

        // Load Content Existing
        window.editor.setComponents(<?= json_encode($template->content) ?>);
    });

    function showToast(message, type = 'success') {
        const toastEl = document.getElementById('saveToast');
        const toastMsg = document.getElementById('toastMessage');
        toastEl.classList.remove('bg-success', 'bg-danger');
        toastEl.classList.add(type === 'success' ? 'bg-success' : 'bg-danger');
        toastMsg.innerText = message;
        const toast = new bootstrap.Toast(toastEl);
        toast.show();
    }

    function updateTemplate() {
        const name = document.getElementById('template_name').value;
        const btn = document.getElementById('btn-do-save');

        if(!name) {
            showToast('Nama template wajib diisi!', 'danger');
            return;
        }

        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Memperbarui...';

        const html = window.editor.runCommand('gjs-get-inlined-html');
        
        const formData = new FormData();
        formData.append('name', name);
        formData.append('html', html);

        fetch('<?= url_to('user.templates.update', $template->id) ?>', {
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
                showToast(res.message);
                setTimeout(() => {
                    window.location.href = '<?= url_to('user.templates') ?>';
                }, 1500);
            } else {
                showToast('Gagal: ' + res.message, 'danger');
                btn.disabled = false;
                btn.innerHTML = '<i class="ti ti-check me-2"></i> Perbarui Sekarang';
            }
        })
        .catch(err => {
            console.error(err);
            showToast('Terjadi kesalahan sistem.', 'danger');
            btn.disabled = false;
            btn.innerHTML = '<i class="ti ti-check me-2"></i> Perbarui Sekarang';
        });
    }
</script>

<style>
    .gjs-one-bg { background-color: #f8f9fa; }
    .gjs-two-bg { background-color: #ffffff; }
    .gjs-three-bg { background-color: #e9ecef; }
    .gjs-four-color { color: #206bc4; }
</style>
<?= $this->endSection() ?>
