# Mass Email Campaign System (CodeIgniter 4)

This is an internal mass email campaign system built as a **CodeIgniter 4 modular monolith**:
- **CI4 HTTP app**: AdminLTE UI for admins/users to manage campaigns
- **CI4 CLI worker**: cron-driven delivery worker (runs every 1 minute)
- **MySQL/MariaDB**: single source of truth
- **SMTP**: configured per sender account

> **Source of truth:** `SOURCE_OF_TRUTH.md` is the only normative spec for future decisions.

## Quick start (dev)
- Copy env: `cp .env.example .env`
- Install: `composer install`
- Run: `php spark serve`

## Cron (required)
Run the delivery worker every minute:

```cron
* * * * * php /path/to/project/spark cron:deliver-campaigns