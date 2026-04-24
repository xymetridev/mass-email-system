<?= $this->extend(config('Auth')->views['layout']) ?>

<?= $this->section('title') ?>Cek Email Anda<?= $this->endSection() ?>

<?= $this->section('main') ?>
<div class="card card-md shadow-sm text-center">
    <div class="card-body p-4 p-sm-5">
        <span class="avatar avatar-xl rounded bg-success-lt mb-4">
            <i class="ti ti-mail-check fs-1 text-success"></i>
        </span>
        <h2 class="h2 mb-4"><?= lang('Auth.checkYourEmail') ?></h2>
        
        <p class="text-muted">
            Kami telah mengirimkan instruksi beserta <strong>Magic Link</strong> ke alamat email Anda.
            <br>
            <?= lang('Auth.magicLinkDetails', [setting('Auth.magicLinkLifetime') / 60]) ?>
        </p>
        
        <div class="mt-4">
            <a href="<?= url_to('login') ?>" class="btn btn-outline-primary">
                Kembali ke Halaman Login
            </a>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
