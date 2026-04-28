<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// 1. REDIRECT HALAMAN DEPAN
$routes->get('/', function () {
    if (auth()->loggedIn()) {
        return auth()->user()->inGroup('admin') 
            ? redirect()->to('/admin/dashboard') 
            : redirect()->to('/user/dashboard');
    }
    return redirect()->to('/login');
});

// 2. AUTHENTICATION (CI4 Shield)
service('auth')->routes($routes, ['except' => ['logout']]);
$routes->post('logout', '\CodeIgniter\Shield\Controllers\LoginController::logoutAction', ['as' => 'logout']);

// ====================================================================
// 3. AREA OPERASIONAL (Bisa diakses Admin & User Biasa)
// ====================================================================
$routes->group('', ['filter' => 'auth'], static function ($routes) {
    
    $routes->get('set-password', 'AuthController::setPasswordView', ['as' => 'set-password-view']);
    $routes->post('set-password', 'AuthController::setPasswordUpdate', ['as' => 'set-password-update']);
    
    // Dashboard User
    $routes->get('user/dashboard', 'User\Dashboard::index', ['as' => 'user.dashboard']);
    $routes->get('user/dashboard/getStats', 'User\Dashboard::getStats');


    $routes->get('templates', 'User\TemplateController::index', ['as' => 'user.templates']);
    $routes->get('templates/new', 'User\TemplateController::create', ['as' => 'user.templates.create']);
    $routes->post('templates/store', 'User\TemplateController::store', ['as' => 'user.templates.store']);
    $routes->get('templates/edit/(:num)', 'User\TemplateController::edit/$1', ['as' => 'user.templates.edit']);
    $routes->post('templates/update/(:num)', 'User\TemplateController::update/$1', ['as' => 'user.templates.update']);
    $routes->get('templates/show/(:num)', 'User\TemplateController::show/$1', ['as' => 'user.templates.show']);
    $routes->get('contacts', 'User\ContactController::index', ['as' => 'user.contacts']);
    $routes->get('contacts/sample', 'User\ContactController::downloadSample', ['as' => 'user.contacts.sample']);
    $routes->post('contacts/store', 'User\ContactController::store', ['as' => 'user.contacts.store']);
    $routes->post('contacts/update/(:num)', 'User\ContactController::update/$1', ['as' => 'user.contacts.update']);
    $routes->post('contacts/delete/(:num)', 'User\ContactController::delete/$1', ['as' => 'user.contacts.delete']);
    $routes->post('contacts/import', 'User\ContactController::import', ['as' => 'user.contacts.import']);
    $routes->post('contacts/tag/store', 'User\ContactController::storeTag', ['as' => 'user.contacts.store_tag']);
    $routes->post('contacts/tag/update/(:num)', 'User\ContactController::updateTag/$1', ['as' => 'user.contacts.update_tag']);
    $routes->post('contacts/tag/delete/(:num)', 'User\ContactController::deleteTag/$1', ['as' => 'user.contacts.delete_tag']);
    
    $routes->get('automations', 'User\AutomationController::index', ['as' => 'user.automations']);
    $routes->post('automations/store', 'User\AutomationController::store', ['as' => 'user.automations.store']);
    $routes->get('automations/show/(:num)', 'User\AutomationController::show/$1', ['as' => 'user.automations.show']);
    $routes->post('automations/step/store/(:num)', 'User\AutomationController::storeStep/$1', ['as' => 'user.automations.step.store']);
    $routes->get('automations/status/(:num)/(:any)', 'User\AutomationController::updateStatus/$1/$2', ['as' => 'user.automations.status']);

    $routes->get('reports', 'User\ReportController::index', ['as' => 'user.reports']);
    
    // Suppression List (Daftar Hitam)
    $routes->get('suppressions', 'SuppressionController::index', ['as' => 'app.suppressions']);
    $routes->post('suppressions/store', 'SuppressionController::store', ['as' => 'app.suppressions.store']);
    $routes->delete('suppressions/delete/(:num)', 'SuppressionController::delete/$1', ['as' => 'app.suppressions.delete']);

    $routes->get('smtp', 'User\SmtpController::index', ['as' => 'app.smtp']);
    $routes->get('smtp/new', 'User\SmtpController::create', ['as' => 'app.smtp.create']);
    $routes->post('smtp/store', 'User\SmtpController::store', ['as' => 'app.smtp.store']);
    $routes->get('smtp/edit/(:num)', 'User\SmtpController::edit/$1', ['as' => 'app.smtp.edit']);
    $routes->post('smtp/update/(:num)', 'User\SmtpController::update/$1', ['as' => 'app.smtp.update']);
    $routes->post('smtp/delete/(:num)', 'User\SmtpController::delete/$1', ['as' => 'app.smtp.delete']);
    $routes->get('smtp/test/(:num)', 'User\SmtpController::testConnection/$1', ['as' => 'app.smtp.test']);

    $routes->get('profile', 'UserController::profile', ['as' => 'app.profile']);
    $routes->post('profile/update', 'UserController::update', ['as' => 'app.profile.update']);
    $routes->post('profile/change-password', 'UserController::changePassword', ['as' => 'app.profile.change_password']);
});


$routes->group('campaigns', ['filter' => 'auth'], static function ($routes) {
    // 1. Halaman Utama
    $routes->get('/', 'CampaignController::index', ['as' => 'app.campaigns']);
    
    // 2. Alur Wizard (Buat & Edit Kampanye)
    $routes->get('wizard/(:num)', 'CampaignWizardController::index/$1', ['as' => 'app.campaigns.wizard']);
    $routes->post('wizard/process/(:num)', 'CampaignWizardController::process/$1', ['as' => 'app.campaigns.wizard.process']);
    $routes->post('wizard/finish', 'CampaignWizardController::finish', ['as' => 'app.campaigns.wizard.finish']);
    $routes->get('wizard/cancel', 'CampaignWizardController::cancel', ['as' => 'app.campaigns.wizard.cancel']);
    $routes->get('wizard/tag-contacts/(:num)', 'CampaignWizardController::getTagContacts/$1');
    
    // 3. Aksi Operasional (POST karena mengubah data)
    $routes->get('campaigns/show/(:num)', 'CampaignController::show/$1', ['as' => 'app.campaigns.show']);
    $routes->get('campaigns/export/(:num)', 'CampaignController::export/$1', ['as' => 'app.campaigns.export']);
    $routes->get('campaigns/duplicate/(:num)', 'CampaignController::duplicate/$1', ['as' => 'app.campaigns.duplicate']);
    $routes->get('show/(:num)', 'CampaignController::show/$1', ['as' => 'app.campaigns.show']);
    $routes->get('edit-draft/(:num)', 'CampaignController::editDraft/$1', ['as' => 'app.campaigns.edit_draft']);
    $routes->post('duplicate/(:num)', 'CampaignController::duplicate/$1', ['as' => 'app.campaigns.duplicate']);
    $routes->get('show/(:num)', 'CampaignController::show/$1', ['as' => 'app.campaigns.show']);
    
    // 4. Update Status (Pause, Running, Cancelled) — POST untuk keamanan
    $routes->post('status/(:num)/(:segment)', 'CampaignController::updateStatus/$1/$2', ['as' => 'app.campaigns.update_status']);
    
    // 5. Endpoint API untuk Auto-Refresh Status
    $routes->get('check-statuses', 'CampaignController::checkStatuses', ['as' => 'app.campaigns.check_statuses']);

    // 6. Delete (POST untuk keamanan)
    $routes->post('delete/(:num)', 'CampaignController::delete/$1', ['as' => 'app.campaigns.delete']);
});



// ====================================================================
// 4. AREA KHUSUS ADMINISTRATOR
// ====================================================================
$routes->group('admin', ['filter' => ['auth', 'group:admin']], static function ($routes) {
    
    $routes->get('dashboard', 'Admin\Dashboard::index', ['as' => 'admin.dashboard']);
    $routes->get('dashboard/getStats', 'Admin\Dashboard::getStats');

    $routes->get('smtp/test/(:num)', 'Admin\SmtpController::testConnection/$1', ['as' => 'admin.smtp.test']);

    $routes->get('users', 'Admin\Users::index', ['as' => 'admin.users']);
    $routes->post('users', 'Admin\Users::store', ['as' => 'admin.users.store']);
    $routes->post('users/toggle/(:num)', 'Admin\Users::toggleBan/$1');
    
    $routes->get('templates', 'Admin\TemplateController::index', ['as' => 'admin.templates']); 
    $routes->get('templates/new', 'Admin\TemplateController::create', ['as' => 'admin.templates.create']); 
    $routes->post('templates/store', 'Admin\TemplateController::store', ['as' => 'admin.templates.store']); 
    $routes->get('templates/edit/(:num)', 'Admin\TemplateController::edit/$1', ['as' => 'admin.templates.edit']);
    $routes->post('templates/update/(:num)', 'Admin\TemplateController::update/$1', ['as' => 'admin.templates.update']);
    $routes->get('templates/show/(:num)', 'Admin\TemplateController::show/$1', ['as' => 'admin.templates.show']); 
    $routes->get('reports', 'Admin\ReportController::index', ['as' => 'admin.reports']);
    
    $routes->get('logs', 'Admin\LogController::index', ['as' => 'admin.logs']);
    $routes->get('logs/view/(:segment)', 'Admin\LogController::view/$1', ['as' => 'admin.logs.view']);
    
    // Pengaturan SMTP
    $routes->get('smtp', 'Admin\SmtpController::index', ['as' => 'admin.smtp.index']);
    $routes->get('smtp/new', 'Admin\SmtpController::create', ['as' => 'admin.smtp.create']);
    $routes->post('smtp/store', 'Admin\SmtpController::store', ['as' => 'admin.smtp.store']);
    $routes->get('smtp/edit/(:num)', 'Admin\SmtpController::edit/$1', ['as' => 'admin.smtp.edit']);
    $routes->post('smtp/update/(:num)', 'Admin\SmtpController::update/$1', ['as' => 'admin.smtp.update']);
    $routes->post('smtp/delete/(:num)', 'Admin\SmtpController::delete/$1', ['as' => 'admin.smtp.delete']);
});


// --- TRACKING ROUTES (PUBLIC ACCESS) ---
$routes->get('track/open/(:num)', 'TrackingController::open/$1');
$routes->get('track/click/(:num)', 'TrackingController::click/$1');
$routes->get('track/unsubscribe/(:num)', 'TrackingController::unsubscribe/$1');
