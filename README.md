# Mass Email System (CodeIgniter 4)

This repository is initialized as a fresh, production-safe CodeIgniter 4 project foundation.

## Getting started

1. Copy environment file:
   ```bash
   cp .env.example .env
   ```
2. Install dependencies:
   ```bash
   composer install
   ```
3. Run local server:
   ```bash
   php spark serve
   ```

## Project readiness

The structure is prepared for:
- Database migrations and seeds (`app/Database/Migrations`, `app/Database/Seeds`)
- Domain models (`app/Models`)
- Services (`app/Services`)
- Filters (`app/Filters`)
- Console commands (`app/Commands`)
- AdminLTE layouts/pages (`app/Views/adminlte`)

No business modules are implemented yet.
