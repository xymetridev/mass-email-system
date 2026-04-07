# Backend Integration Layer (CI4)

## 1) Routes
Create `app/Config/Routes.php` with grouped, maintainable route blocks:

```php
<?php

use CodeIgn\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->setDefaultNamespace('App\\Controllers');
$routes->setDefaultController('Home');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
$routes->setAutoRoute(false);

// Public/auth endpoints
$routes->group('api', ['namespace' => 'App\\Controllers\\Api\\V1'], static function ($routes) {
    $routes->post('auth/login', 'AuthController::login');
    $routes->post('auth/refresh', 'AuthController::refresh');
});

// Protected API endpoints (JWT/session filter)
$routes->group('api/v1', [
    'namespace' => 'App\\Controllers\\Api\\V1',
    'filter'    => 'auth'
], static function ($routes) {

    // Current user
    $routes->get('me', 'UserController::me');

    // Contacts (owned resources)
    $routes->group('contacts', static function ($routes) {
        $routes->get('/', 'ContactController::index');
        $routes->post('/', 'ContactController::store');
        $routes->get('(:num)', 'ContactController::show/$1');
        $routes->put('(:num)', 'ContactController::update/$1');
        $routes->delete('(:num)', 'ContactController::delete/$1');
    });

    // Templates (owned resources)
    $routes->group('templates', static function ($routes) {
        $routes->get('/', 'TemplateController::index');
        $routes->post('/', 'TemplateController::store');
        $routes->get('(:num)', 'TemplateController::show/$1');
        $routes->put('(:num)', 'TemplateController::update/$1');
        $routes->delete('(:num)', 'TemplateController::delete/$1');
    });

    // Campaigns + scheduling/send actions
    $routes->group('campaigns', static function ($routes) {
        $routes->get('/', 'CampaignController::index');
        $routes->post('/', 'CampaignController::store');
        $routes->get('(:num)', 'CampaignController::show/$1');
        $routes->put('(:num)', 'CampaignController::update/$1');
        $routes->delete('(:num)', 'CampaignController::delete/$1');

        $routes->post('(:num)/schedule', 'CampaignController::schedule/$1');
        $routes->post('(:num)/cancel', 'CampaignController::cancel/$1');
    });

    // Audit/log views for owner
    $routes->get('campaigns/(:num)/logs', 'CampaignLogController::index/$1');
});

// Admin-only maintenance routes (optional)
$routes->group('api/v1/admin', [
    'namespace' => 'App\\Controllers\\Api\\V1\\Admin',
    'filter'    => 'auth,role:admin'
], static function ($routes) {
    $routes->get('health', 'HealthController::index');
});
```

**Ownership enforcement pattern**
- Keep auth on route group (`filter => auth`).
- Add ownership checks in controller/service before read/update/delete.
- For shared/reusable ownership checks, use one base API controller method (`assertOwnership($ownerId, $resourceOwnerId)`).

---

## 2) Base controllers / shared controller logic
Create `app/Controllers/Api/V1/BaseApiController.php`:

```php
<?php

namespace App\Controllers\Api\V1;

use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;

abstract class BaseApiController extends ResourceController
{
    protected array $helpers = ['auth'];

    protected function userId(): int
    {
        // depends on your auth filter/helper implementation
        return (int) auth_user_id();
    }

    protected function ok(array $data = [], string $message = 'OK'): ResponseInterface
    {
        return $this->respond([
            'status'  => 'success',
            'message' => $message,
            'data'    => $data,
        ]);
    }

    protected function failValidation(array $errors): ResponseInterface
    {
        return $this->respond([
            'status' => 'error',
            'errors' => $errors,
        ], 422);
    }

    protected function assertOwnership(int $resourceOwnerId): void
    {
        if ($this->userId() !== $resourceOwnerId) {
            throw \CodeIgniter\Exceptions\PageForbiddenException::forPageForbidden('Not your resource.');
        }
    }
}
```

Use this in feature controllers (`CampaignController`, `TemplateController`, etc.) so each `show/update/delete` path validates owner before action. Keep role-based checks in filter/middleware (`role:admin`) and ownership in service/controller level.

---

## 3) Seeders (useful baseline)
Create `app/Database/Seeds/InitialDataSeeder.php`:

```php
<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class InitialDataSeeder extends Seeder
{
    public function run()
    {
        // Roles
        $this->db->table('roles')->insertBatch([
            ['name' => 'admin'],
            ['name' => 'user'],
        ]);

        // Admin user (replace with hashed password from your auth package)
        $this->db->table('users')->insert([
            'name'       => 'System Admin',
            'email'      => 'admin@example.com',
            'password'   => password_hash('ChangeMe123!', PASSWORD_DEFAULT),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $adminId = $this->db->insertID();

        $this->db->table('user_roles')->insert([
            'user_id' => $adminId,
            'role_id' => 1,
        ]);
    }
}
```

Optional dev seeder chain in `app/Database/Seeds/DatabaseSeeder.php`:

```php
public function run()
{
    $this->call('InitialDataSeeder');
}
```

---

## 4) Environment / config notes
In `.env` (or environment-specific secret manager), define at minimum:

```dotenv
CI_ENVIRONMENT = development
app.baseURL = 'http://localhost:8080/'

database.default.hostname = localhost
database.default.database = mass_email
database.default.username = root
database.default.password =
database.default.DBDriver = MySQLi
database.default.DBPrefix =
database.default.port = 3306

# mail transport for queue/cron send
email.fromEmail = no-reply@example.com
email.fromName = Mass Email System
email.protocol = smtp
email.SMTPHost = smtp.mailtrap.io
email.SMTPUser = your_user
email.SMTPPass = your_pass
email.SMTPPort = 2525
email.SMTPCrypto = tls

# auth/JWT
jwt.secret = REPLACE_WITH_LONG_RANDOM_SECRET
jwt.ttl = 3600
```

Also update:
- `app/Config/Filters.php`: register `auth` and optional `role` filters.
- `app/Config/CORS.php` (if SPA/client is separate domain).
- `app/Config/Email.php` if not using env mapping only.

**Required Composer packages (typical):**
- `composer require codeigniter4/shield` (auth)
- `composer require firebase/php-jwt` (if JWT custom integration)
- `composer require codeigniter4/tasks` (scheduled/background tasks)
- `composer require codeigniter4/settings` (optional app-level runtime settings)

---

## 5) Setup steps (fresh CI4 -> runnable backend)
From a clean machine/container:

1. **Create project**
   ```bash
   composer create-project codeigniter4/appstarter mass-email-system
   cd mass-email-system
   ```

2. **Install backend dependencies**
   ```bash
   composer require codeigniter4/shield firebase/php-jwt codeigniter4/tasks
   ```

3. **Configure environment**
   ```bash
   cp env .env
   php spark key:generate
   ```
   - Edit `.env` for database, email, and jwt secret.

4. **Create migrations** (users/roles/templates/contacts/campaigns/campaign_logs/queue tables).

5. **Run migrations**
   ```bash
   php spark migrate
   ```

6. **Run seeders**
   ```bash
   php spark db:seed DatabaseSeeder
   ```
   (or `php spark db:seed InitialDataSeeder` if calling directly)

7. **Start API locally**
   ```bash
   php spark serve --host=0.0.0.0 --port=8080
   ```

8. **Run cron/task command manually for testing**
   - If using CI Tasks package:
     ```bash
     php spark tasks:run
     ```
   - If using custom command (example):
     ```bash
     php spark campaigns:dispatch
     ```

9. **Production cron example**
   ```cron
   * * * * * /usr/bin/php /var/www/html/spark tasks:run >> /var/log/mass-email-cron.log 2>&1
   ```

This is backend-only and excludes AdminLTE UI by design.
