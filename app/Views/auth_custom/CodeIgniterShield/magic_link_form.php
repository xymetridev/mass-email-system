<?= $this->extend(config('Auth')->views['layout']) ?>

<?= $this->section('title') ?><?= lang('Auth.useMagicLink') ?><?= $this->endSection() ?>

<?= $this->section('main') ?>
<div class="card card-md shadow-sm">
    <div class="card-body p-4 p-sm-5">
        <h2 class="h2 text-center mb-4">Lupa Password?</h2>
        <p class="text-muted text-center mb-4">
            Masukkan email Anda dan kami akan mengirimkan link untuk masuk ke akun Anda.
        </p>

        <?php if (session('error') !== null) : ?>
            <div class="alert alert-danger" role="alert"><?= esc(session('error')) ?></div>
        <?php elseif (session('errors') !== null) : ?>
            <div class="alert alert-danger" role="alert">
                <?php if (is_array(session('errors'))) : ?>
                    <?php foreach (session('errors') as $error) : ?>
                        <div><?= esc($error) ?></div>
                    <?php endforeach ?>
                <?php else : ?>
                    <div><?= esc(session('errors')) ?></div>
                <?php endif ?>
            </div>
        <?php endif ?>

        <form action="<?= url_to('magic-link') ?>" method="post" autocomplete="off" novalidate>
            <?= csrf_field() ?>
            
            <div class="mb-3">
                <label class="form-label"><?= lang('Auth.email') ?></label>
                <div class="input-group input-group-flat">
                    <span class="input-group-text"><i class="ti ti-mail"></i></span>
                    <input type="email" class="form-control" name="email" autocomplete="email" placeholder="contoh@email.com"
                           value="<?= old('email', auth()->user()->email ?? null) ?>" required autofocus>
                </div>
            </div>

            <div class="form-footer">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="ti ti-send me-2"></i> Kirim Magic Link
                </button>
            </div>
        </form>
    </div>
</div>

<div class="text-center text-secondary mt-3">
    <a href="<?= url_to('login') ?>"><?= lang('Auth.backToLogin') ?></a>
</div>
<?= $this->endSection() ?>
