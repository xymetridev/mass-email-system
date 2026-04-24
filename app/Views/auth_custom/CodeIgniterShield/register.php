<?= $this->extend(config('Auth')->views['layout']) ?>

<?= $this->section('title') ?>Register<?= $this->endSection() ?>

<?= $this->section('main') ?>
<div class="card card-md">
    <div class="card-body p-4 p-sm-5">
        <h2 class="h2 text-center mb-4">Buat akun baru</h2>

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

        <form action="<?= url_to('register') ?>" method="post" autocomplete="off" novalidate>
            <?= csrf_field() ?>
            <div class="mb-3">
                <label class="form-label"><?= lang('Auth.email') ?></label>
                <div class="input-group input-group-flat">
                    <span class="input-group-text"><i class="ti ti-mail"></i></span>
                    <input type="email" class="form-control" name="email" value="<?= old('email') ?>" required autofocus>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label"><?= lang('Auth.username') ?></label>
                <div class="input-group input-group-flat">
                    <span class="input-group-text"><i class="ti ti-user"></i></span>
                    <input type="text" class="form-control" name="username" value="<?= old('username') ?>" required>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label"><?= lang('Auth.password') ?></label>
                <div class="input-group input-group-flat">
                    <span class="input-group-text"><i class="ti ti-lock"></i></span>
                    <input type="password" class="form-control" name="password" required>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label"><?= lang('Auth.passwordConfirm') ?></label>
                <div class="input-group input-group-flat">
                    <span class="input-group-text"><i class="ti ti-lock-check"></i></span>
                    <input type="password" class="form-control" name="password_confirm" required>
                </div>
            </div>
            <div class="form-footer">
                <button type="submit" class="btn btn-primary w-100"><?= lang('Auth.register') ?></button>
            </div>
        </form>
    </div>
</div>

<div class="text-center text-secondary mt-3">
    <?= lang('Auth.haveAccount') ?> <a href="<?= url_to('login') ?>"><?= lang('Auth.login') ?></a>
</div>
<?= $this->endSection() ?>
