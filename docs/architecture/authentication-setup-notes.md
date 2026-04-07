# 1) Authentication setup notes for CI4 (Shield)

Use [CodeIgniter Shield](https://github.com/codeigniter4/shield) for production-safe authentication and authorization.

## Install and bootstrap

```bash
composer require codeigniter4/shield
php spark shield:setup
php spark migrate --all
```

## Environment and security defaults

Set these in `.env`:

```ini
CI_ENVIRONMENT = production
app.forceGlobalSecureRequests = true
app.CSPEnabled = true
session.driver = 'CodeIgniter\\Session\\Handlers\\FileHandler'
session.cookieSecure = true
session.cookieHTTPOnly = true
session.cookieSameSite = 'Lax'
```

## Use session-based auth for web/API-internal admin panel

Shield defaults are enough for session auth. Keep:
- password hashing enabled (default)
- login throttling enabled
- email verification enabled (recommended)
- remember-me optional (disable unless needed)

## Seed required roles

Create only two groups:
- `admin`
- `user`

Map all non-admin accounts to `user` by default at registration/creation.
