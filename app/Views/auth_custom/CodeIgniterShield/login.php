<?= $this->extend(config('Auth')->views['layout']) ?>

<?= $this->section('title') ?>Login<?= $this->endSection() ?>

<?= $this->section('main') ?>
<div class="card card-md">
    <div class="card-body p-4 p-sm-5">
        <h2 class="h2 text-center mb-4">Masuk ke akun Anda</h2>

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

        <?php if (session('message') !== null) : ?>
            <div class="alert alert-success" role="alert"><?= esc(session('message')) ?></div>
        <?php endif ?>

        <form action="<?= url_to('login') ?>" method="post" autocomplete="off" novalidate>
            <?= csrf_field() ?>
            <div class="mb-3">
                <label class="form-label"><?= lang('Auth.email') ?></label>
                <div class="input-group input-group-flat">
                    <span class="input-group-text"><i class="ti ti-mail"></i></span>
                    <input type="email" class="form-control" name="email" value="<?= old('email') ?>" required autofocus>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label"><?= lang('Auth.password') ?></label>
                <div class="input-group input-group-flat">
                    <span class="input-group-text"><i class="ti ti-lock"></i></span>
                    <input type="password" class="form-control" name="password" required>
                </div>
            </div>
            <?php if (setting('Auth.sessionConfig')['allowRemembering']): ?>
                <label class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" name="remember" <?php if (old('remember')): ?>checked<?php endif ?>>
                    <span class="form-check-label"><?= lang('Auth.rememberMe') ?></span>
                </label>
            <?php endif; ?>
            <div class="form-footer">
                <button type="submit" class="btn btn-primary w-100"><?= lang('Auth.login') ?></button>
            </div>
        </form>
    </div>
</div>

<?php if (setting('Auth.allowMagicLinkLogins')) : ?>
    <div class="text-center text-secondary mt-2">
        <?= lang('Auth.forgotPassword') ?> <a href="<?= url_to('magic-link') ?>"><?= lang('Auth.useMagicLink') ?></a>
    </div>
<?php endif ?>

<?php if (setting('Auth.allowRegistration')) : ?>
    <div class="text-center text-secondary mt-3">
        <?= lang('Auth.needAccount') ?> <a href="<?= url_to('register') ?>"><?= lang('Auth.register') ?></a>
    </div>
<?php endif ?>
<?= $this->endSection() ?>
