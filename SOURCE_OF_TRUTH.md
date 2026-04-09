# SOURCE_OF_TRUTH — Mass Email Campaign System (CI4)

This document is strict and normative. If any other file conflicts with this, this document wins.

## 1) Architecture (Final)
- Framework: CodeIgniter 4 (PHP), modular monolith.
- Components:
  - HTTP app (`app/Controllers`, `app/Views`) for internal AdminLTE UI.
  - CLI worker (`app/Commands`) triggered by cron every 1 minute.
  - MySQL/MariaDB as source of truth.
  - SMTP delivery via CI4 email service and sender account config.
- Code organization:
  - Controllers are thin.
  - Business logic lives in `app/Services`.
  - Access control is enforced via filters and service-level ownership checks.

## 2) Feature Scope (Strict)
### Included
- Roles: exactly `admin` and `user`.
- Non-admin can access only owned data.
- Sender account management.
- Campaign CRUD and lifecycle actions.
- Exactly one template per campaign (1:1).
- Recipient import (streamed, line-by-line).
- Delivery worker with locking, retry, and idempotency.
- Operational delivery logs.

### Excluded (Never Implement)
- Open tracking
- Click tracking
- Unsubscribe flow
- Scheduled future sending
- Attachments

## 3) Authentication (Final)
- Use CodeIgniter Shield with session-based authentication only.
- Do not use JWT/refresh-token architecture for this project.

## 4) Canonical Status Definitions
### Campaign Statuses
- `DRAFT`
- `READY`
- `RUNNING`
- `PAUSED`
- `CANCELED`
- `COMPLETED`
- `FAILED`

Allowed transitions:
- `DRAFT -> READY -> RUNNING -> (PAUSED | CANCELED | COMPLETED | FAILED)`
- `PAUSED -> RUNNING | CANCELED`
- `READY -> CANCELED`
- `FAILED -> READY` after explicit correction.

### Recipient Statuses
- `PENDING`
- `SENT`
- `FAILED`

Implementation note:
- Internal worker claim fields are allowed for concurrency safety, but user-facing statuses remain only the three statuses above.

## 5) Deprecated / Non-Authoritative Docs
- `LOCKED_ARCHITECTURE.md` (deleted; deprecated)
- `BACKEND_INTEGRATION.md` (deleted; deprecated)
- `ADMINLTE_UI_UX_SPEC.md` is UI guidance only and must not override this document.

## 6) Conflict Priority
1. `SOURCE_OF_TRUTH.md`
2. `AGENTS.md`
3. Current CI4 codebase
4. Other docs