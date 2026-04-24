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