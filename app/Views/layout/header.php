<header class="navbar navbar-expand-md d-none d-lg-flex d-print-none border-bottom">
    <div class="container-xl">
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbar-menu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="navbar-nav flex-row order-md-last ms-auto">
            <div class="nav-item d-none d-md-flex me-3">
                <button id="theme-toggle" class="btn btn-icon btn-ghost-secondary shadow-none border-0" type="button" title="Ganti Tema">
                    <i id="theme-icon" class="ti ti-moon"></i>
                </button>
            </div>
            
            <div class="nav-item dropdown">
                <a href="#" class="nav-link d-flex lh-1 text-reset p-0" data-bs-toggle="dropdown">
                    <span class="avatar avatar-sm bg-primary-lt shadow-sm">
                        <?= strtoupper(substr((string) auth()->user()->username, 0, 1)) ?>
                    </span>
                    <div class="d-none d-xl-block ps-2">
                        <div class="fw-bold"><?= esc(auth()->user()->username) ?></div>
                        <div class="mt-1 small text-secondary">
                            <?= esc(implode('', auth()->user()->getGroups())) ?>
                        </div>
                    </div>
                </a>
                <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                    <a href="<?= url_to('app.profile') ?>" class="dropdown-item">
                        <i class="ti ti-user me-2 text-muted"></i> Profil Saya
                    </a>
                    <div class="dropdown-divider"></div>
                    <form action="<?= url_to('logout') ?>" method="post">
                        <?= csrf_field() ?>
                        <button type="submit" class="dropdown-item text-danger">
                            <i class="ti ti-logout me-2"></i> Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
    </div>
</header>