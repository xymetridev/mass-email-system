<?php
$uri = service('uri');
$segments = $uri->getSegments();

// Penentuan menu aktif berdasarkan segment URL
$activeMenu = $segments[0] ?? 'dashboard'; // Segmen pertama (dashboard, campaigns, smtp, dll)
$activeSub  = $segments[1] ?? '';           // Segmen kedua (new, edit, dll)

// Jika masuk prefix admin/app/user, geser segmentnya
if (in_array($activeMenu, ['app', 'admin', 'user'])) {
    $activeMenu = $segments[1] ?? 'dashboard';
    $activeSub  = $segments[2] ?? '';
}

$isAdmin = auth()->user()?->inGroup('admin');
?>

<aside class="navbar navbar-vertical navbar-expand-lg">
    <div class="container-fluid">
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#sidebar-menu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <h1 class="navbar-brand navbar-brand-autodark">
            <a href="<?= site_url('/') ?>" class="d-flex align-items-center gap-2 text-decoration-none">
                <i class="ti ti-mail-fast text-primary" style="font-size: 1.5rem;"></i>
                <span class="navbar-brand-text">Mass Email</span>
            </a>
        </h1>
        <div class="navbar-nav flex-row d-lg-none">
            <div class="nav-item me-2">
                <button class="theme-toggle btn btn-icon btn-ghost-secondary shadow-none border-0" type="button" title="Ganti Tema">
                    <i class="ti ti-moon"></i>
                </button>
            </div>
            <div class="nav-item dropdown">
                <a href="#" class="nav-link d-flex lh-1 text-reset p-0" data-bs-toggle="dropdown">
                    <span class="avatar avatar-sm bg-primary-lt shadow-sm">
                        <?= strtoupper(substr((string) auth()->user()->username, 0, 1)) ?>
                    </span>
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
        <div class="collapse navbar-collapse" id="sidebar-menu">
            <ul class="navbar-nav pt-lg-3">
                
                <li class="nav-item <?= $activeMenu == 'dashboard' ? 'active' : '' ?>">
                    <a class="nav-link" href="<?= $isAdmin ? url_to('admin.dashboard') : url_to('user.dashboard') ?>">
                        <span class="nav-link-icon"><i class="ti ti-chart-pie"></i></span>
                        <span class="nav-link-title">Dashboard Utama</span>
                    </a>
                </li>
                
                <div class="hr-text text-uppercase text-muted my-2">Operasional</div>
                
                <li class="nav-item <?= ($activeMenu == 'campaigns') ? 'active' : '' ?>">
                    <a class="nav-link" href="<?= url_to('app.campaigns') ?>">
                        <span class="nav-link-icon"><i class="ti ti-mail"></i></span>
                        <span class="nav-link-title">Kampanye Email</span>
                    </a>
                </li>

                <li class="nav-item <?= ($activeMenu == 'templates') ? 'active' : '' ?>">
                    <a class="nav-link" href="<?= $isAdmin ? url_to('admin.templates') : url_to('user.templates') ?>">
                        <span class="nav-link-icon"><i class="ti ti-file-code"></i></span>
                        <span class="nav-link-title">Pustaka Template</span>
                    </a>
                </li>

                <li class="nav-item <?= ($activeMenu == 'contacts') ? 'active' : '' ?>">
                    <a class="nav-link" href="<?= url_to('user.contacts') ?>">
                        <span class="nav-link-icon"><i class="ti ti-users"></i></span>
                        <span class="nav-link-title">Kontak & Segmen</span>
                    </a>
                </li>

                <li class="nav-item <?= ($activeMenu == 'automations') ? 'active' : '' ?>">
                    <a class="nav-link" href="<?= url_to('user.automations') ?>">
                        <span class="nav-link-icon"><i class="ti ti-git-merge"></i></span>
                        <span class="nav-link-title">Automations</span>
                    </a>
                </li>

                <li class="nav-item <?= ($activeMenu == 'reports') ? 'active' : '' ?>">
                    <a class="nav-link" href="<?= $isAdmin ? url_to('admin.reports') : url_to('user.reports') ?>">
                        <span class="nav-link-icon"><i class="ti ti-report-analytics"></i></span>
                        <span class="nav-link-title">Laporan Pengiriman</span>
                    </a>
                </li>

                <?php if (!$isAdmin) : ?>
                <li class="nav-item <?= ($activeMenu == 'smtp' && !in_array('admin', $segments)) ? 'active' : '' ?>">
                    <a class="nav-link" href="<?= url_to('app.smtp') ?>">
                        <span class="nav-link-icon"><i class="ti ti-server-cog"></i></span>
                        <span class="nav-link-title">Kredensial SMTP</span>
                    </a>
                </li>
                <?php endif ?>

                <?php if ($isAdmin) : ?>
                <div class="hr-text text-uppercase text-muted my-3">Administrator System</div>
                
                <li class="nav-item <?= ($activeMenu == 'smtp' && in_array('admin', $segments)) ? 'active' : '' ?>">
                    <a class="nav-link" href="<?= url_to('admin.smtp.index') ?>">
                        <span class="nav-link-icon"><i class="ti ti-settings-automation"></i></span>
                        <span class="nav-link-title">Master SMTP Bisnis</span>
                    </a>
                </li>

                <li class="nav-item <?= ($activeMenu == 'users') ? 'active' : '' ?>">
                    <a class="nav-link" href="<?= url_to('admin.users') ?>">
                        <span class="nav-link-icon"><i class="ti ti-users-group"></i></span>
                        <span class="nav-link-title">Manajemen User</span>
                    </a>
                </li>

                <li class="nav-item <?= ($activeMenu == 'logs') ? 'active' : '' ?>">
                    <a class="nav-link" href="<?= url_to('admin.logs') ?>">
                        <span class="nav-link-icon"><i class="ti ti-terminal-2"></i></span>
                        <span class="nav-link-title">Log Sistem</span>
                    </a>
                </li>
                <?php endif ?>

            </ul>
        </div>
    </div>
</aside>