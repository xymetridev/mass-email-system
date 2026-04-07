# Mass Email Campaign System — Architecture & Implementation Plan (CI4)

## 1) Final Architecture Summary

### 1.1 System Type
A **modular monolith** built with CodeIgniter 4 (CI4), MySQL/MariaDB, and SMTP providers. This keeps development fast and maintenance simple while still being production-ready.

### 1.2 Runtime Components
- **Web App (CI4 HTTP):** admin/user UI and APIs for campaign management.
- **Background Worker (CI4 CLI command via cron):** sends campaign emails in batches every 1 minute.
- **Database (MySQL/MariaDB):** transactional source of truth for users, campaigns, recipients, logs.
- **SMTP Providers:** configured per sender account.

### 1.3 Core Modules
- Authentication & user management
- Sender accounts
- Campaign lifecycle
- Template management (exactly one template per campaign)
- Recipient import (.txt, streaming parser)
- Test send
- Delivery engine (cron + locking + retry)
- Monitoring/logging dashboard

### 1.4 Data/Flow Overview
1. User creates campaign in **DRAFT**.
2. User selects sender account, configures batch size/default name, creates one template, imports recipients.
3. System de-duplicates recipients per campaign and marks campaign **READY** when minimum requirements met.
4. User starts campaign → **RUNNING**.
5. Cron (every minute) processes each RUNNING campaign with lock; sends up to `campaign.batch_size` recipients in `PENDING`/retry-eligible state.
6. Recipient outcomes recorded independently (`SENT` or `FAILED` + error + retry_count).
7. Campaign auto-transitions to **COMPLETED** when all recipients terminal (`SENT` or max retry `FAILED`).

### 1.5 Status Transition Rules
- `DRAFT -> READY -> RUNNING -> (PAUSED|CANCELED|COMPLETED|FAILED)`
- `PAUSED -> RUNNING|CANCELED`
- `READY -> CANCELED`
- `FAILED` is system-level (e.g., persistent sender/config issue) and recoverable only via explicit admin/user action to move back to `READY` after correction.

---

## 2) Final Folder Structure for CI4

```text
app/
  Config/
    Filters.php
    Routes.php
    Validation.php
  Controllers/
    Admin/
      UsersController.php
      DashboardController.php
    User/
      DashboardController.php
      SenderAccountsController.php
      CampaignsController.php
      CampaignTemplatesController.php
      CampaignRecipientsController.php
      CampaignActionsController.php
      CampaignLogsController.php
      TestSendController.php
    Auth/
      AuthController.php
  Database/
    Migrations/
      2026-xx-xx-000001_CreateUsers.php
      2026-xx-xx-000002_CreateSenderAccounts.php
      2026-xx-xx-000003_CreateCampaigns.php
      2026-xx-xx-000004_CreateTemplates.php
      2026-xx-xx-000005_CreateRecipients.php
      2026-xx-xx-000006_CreateCampaignLogs.php
      2026-xx-xx-000007_AddOperationalColumnsAndIndexes.php
    Seeds/
      AdminUserSeeder.php
  Filters/
    AuthFilter.php
    RoleFilter.php
    OwnershipFilter.php
  Models/
    UserModel.php
    SenderAccountModel.php
    CampaignModel.php
    TemplateModel.php
    RecipientModel.php
    CampaignLogModel.php
  Services/
    AuthService.php
    CampaignService.php
    CampaignStateService.php
    RecipientImportService.php
    CampaignDeliveryService.php
    SmtpMailerService.php
    TestSendService.php
    LockService.php
    DashboardService.php
  Commands/
    RunCampaignDelivery.php
  Libraries/
    TenantScope.php
  Validation/
    CampaignRules.php
    SenderAccountRules.php
    RecipientImportRules.php
  Views/
    admin/...
    user/...
    auth/...
writable/
  logs/
  uploads/
    recipients/
```

Notes:
- Keep business logic in `Services`, not controllers.
- CLI command triggers delivery orchestration; reuse same services as HTTP actions.

---

## 3) Database Schema Review (Constraints, FKs, Uniques, Indexes)

Below keeps your table list as source of truth and adds production-safe constraints/indexing.

### 3.1 `users`
Columns (as given):
- id (PK, bigint unsigned)
- name (varchar)
- email (varchar)
- role (enum/string: `admin|user`)
- password (varchar, hashed)
- created_at, updated_at

Constraints/Indexes:
- `UNIQUE(email)`
- `INDEX(role)`
- `CHECK(role IN ('admin','user'))` (or app validation if DB lacks check support)

### 3.2 `sender_accounts`
Columns:
- id (PK)
- user_id (FK -> users.id)
- sender_name
- sender_email
- smtp_host
- smtp_port
- smtp_user
- smtp_pass (encrypted at rest)
- encryption (`tls|ssl|none`)
- created_at, updated_at

Constraints/Indexes:
- `FK(user_id) REFERENCES users(id) ON DELETE CASCADE`
- `INDEX(user_id)`
- `INDEX(sender_email)`
- Optional uniqueness per owner: `UNIQUE(user_id, sender_email, smtp_host, smtp_port, smtp_user)`
- `CHECK(smtp_port > 0)`

### 3.3 `campaigns`
Columns:
- id (PK)
- user_id (FK -> users.id)
- sender_account_id (FK -> sender_accounts.id)
- name
- batch_size
- default_name
- status (`DRAFT|READY|RUNNING|PAUSED|COMPLETED|CANCELED|FAILED`)
- created_at, updated_at

Constraints/Indexes:
- `FK(user_id) REFERENCES users(id) ON DELETE CASCADE`
- `FK(sender_account_id) REFERENCES sender_accounts(id) ON DELETE RESTRICT`
- `INDEX(user_id, status)`
- `INDEX(status)`
- `CHECK(batch_size BETWEEN 1 AND 1000)` (recommended app cap, e.g. default 100)
- `CHECK(status IN (...))`

Ownership integrity rule (enforced in app/service): `campaign.user_id` must match `sender_accounts.user_id`.

### 3.4 `templates`
Columns:
- id (PK)
- campaign_id (FK -> campaigns.id)
- subject
- body_html
- created_at, updated_at

Constraints/Indexes:
- `FK(campaign_id) REFERENCES campaigns(id) ON DELETE CASCADE`
- **One template per campaign:** `UNIQUE(campaign_id)`

### 3.5 `recipients`
Columns:
- id (PK)
- campaign_id (FK -> campaigns.id)
- email
- name (nullable)
- status (`PENDING|SENT|FAILED`)
- retry_count (default 0)
- last_error (nullable)
- sent_at (nullable)
- created_at, updated_at

Constraints/Indexes:
- `FK(campaign_id) REFERENCES campaigns(id) ON DELETE CASCADE`
- `UNIQUE(campaign_id, email)` (required duplicate removal guard)
- `INDEX(campaign_id, status)`
- `INDEX(campaign_id, status, retry_count)`
- `INDEX(campaign_id, id)` (chunk scanning)
- `CHECK(status IN ('PENDING','SENT','FAILED'))`
- `CHECK(retry_count >= 0)`

### 3.6 `campaign_logs`
Columns:
- id (PK)
- campaign_id (FK -> campaigns.id)
- action
- user_id (nullable FK -> users.id)
- created_at

Constraints/Indexes:
- `FK(campaign_id) REFERENCES campaigns(id) ON DELETE CASCADE`
- `FK(user_id) REFERENCES users(id) ON DELETE SET NULL`
- `INDEX(campaign_id, created_at)`
- `INDEX(user_id, created_at)`

### 3.7 Minimal Operational Additions (strongly recommended)
To fully satisfy locking/idempotency/retry at production level, add these columns via migration:

**campaigns**
- `locked_at` (nullable datetime)
- `locked_by` (nullable varchar)
- `last_run_at` (nullable datetime)
- `completed_at` (nullable datetime)
- `canceled_at` (nullable datetime)
- `paused_at` (nullable datetime)

**recipients**
- `next_retry_at` (nullable datetime)
- `provider_message_id` (nullable varchar)

These are scope-safe improvements; they do not change core features.

---

## 4) Role/Access Control Design

### 4.1 Roles
- `admin`: full access to all tenants/users/data.
- `user`: access only own data.

### 4.2 Enforcement Layers
1. **Auth Filter**: blocks unauthenticated access.
2. **Role Filter**: protects admin-only routes.
3. **Ownership Filter / Tenant Scope**: for user routes, all resource IDs must belong to current `user_id`.
4. **Service-level checks**: never trust controllers only.

### 4.3 Route Segmentation
- `/admin/*` => admin only.
- `/app/*` => authenticated users; data scoped by ownership unless admin.

---

## 5) Tenant Isolation Strategy

Strict row-level isolation (single DB, shared schema):
- Every tenant-bound table uses `user_id` directly (`sender_accounts`, `campaigns`) or indirectly via join (`templates`, `recipients`, `campaign_logs`).
- All queries in models/services apply tenant scoping:
  - user role => `WHERE campaigns.user_id = :current_user_id`
  - admin role => no restriction.
- On mutating operations, verify ownership before update/delete.
- Never accept `user_id` from client payload for scoped resources.

Implementation pattern:
- `TenantScope` helper used centrally by services/models.
- Shared query builders like `CampaignModel::forUser($user)`.

---

## 6) Cron Locking Strategy

### 6.1 Scheduler
- System cron entry every minute:
  - `* * * * * php /path/to/project/spark campaign:run`

### 6.2 Two-Level Locking
1. **Global command lock** (prevents overlapping cron invocations).
   - Use DB advisory lock (preferred) or cache/file lock with TTL.
2. **Per-campaign lock** (prevents same campaign being processed concurrently).
   - Atomic update pattern:
     - acquire if `status='RUNNING'` and (`locked_at IS NULL` or lock expired)
     - set `locked_at=NOW(), locked_by=<hostname:pid>`
   - release lock after batch.

### 6.3 Expired Lock Recovery
- If worker crashes, lock considered stale after TTL (e.g., 5 minutes).
- Next run can safely steal stale lock.

---

## 7) Retry and Idempotency Strategy

### 7.1 Recipient-level Isolation
- Each recipient send is independent transactionally/logically.
- One failure does not stop campaign.

### 7.2 Retry Policy
- Configurable `MAX_RETRY` (e.g., 3).
- For temporary SMTP errors:
  - increment `retry_count`
  - set `next_retry_at` with exponential backoff (e.g., +1m, +5m, +15m)
  - keep `status='PENDING'`
- For permanent errors (invalid mailbox/domain hard fail):
  - set `status='FAILED'`, record `last_error`.

### 7.3 Idempotency Controls
- Select recipients for sending using lock-aware query and status guard:
  - `status='PENDING'`
  - `next_retry_at IS NULL OR next_retry_at <= NOW()`
- Update recipient to `SENT` only after provider success.
- Never reset `SENT` back to pending.
- `UNIQUE(campaign_id,email)` prevents duplicate recipient jobs.
- Optional: store `provider_message_id` to diagnose duplicate provider submissions.

### 7.4 Campaign Completion Rule
After batch, if no `PENDING` recipients remain, campaign becomes:
- `COMPLETED` if any `SENT` or `FAILED` terminal records exist and no pending retries.
- `FAILED` only for campaign-level unrecoverable setup/runtime issues (not per-recipient failures).

---

## 8) Recipient Import Strategy for Large Files

### 8.1 File Expectations
- `.txt` file, one recipient per line.
- Supported line formats (simple):
  - `email@example.com`
  - `Full Name <email@example.com>` (optional)

### 8.2 Safe Parsing
- Use streamed file reading (`SplFileObject` or line-by-line fgets), **not** full-file load.
- Validate each email syntax.
- Normalize emails to lowercase/trim.
- Batch insert in chunks (e.g., 1000 rows).
- Use `INSERT IGNORE` or upsert semantics against `UNIQUE(campaign_id,email)`.

### 8.3 Import Result Metrics
Return counts for UI/logging:
- total lines
- valid parsed
- inserted
- duplicates skipped
- invalid skipped

### 8.4 Campaign State Interaction
- Import allowed in `DRAFT`/`READY`/`PAUSED` (team choice; safest is DRAFT+READY only).
- If recipient count > 0 and template exists and sender set, auto-mark `READY` (unless canceled/completed).

---

## 9) Recommended CI4 Components to Use

### 9.1 Migrations
- Create all six required tables.
- Add operational columns and indexes in a dedicated migration.

### 9.2 Models
- One model per table with allowedFields, casts, timestamps.
- Add reusable scoped query methods (`forUser`, `forCampaign`, etc.).

### 9.3 Services (business logic)
- `CampaignService`: create/update campaign.
- `CampaignStateService`: run/pause/resume/cancel transitions + validation.
- `RecipientImportService`: streamed parsing + dedup insert + stats.
- `CampaignDeliveryService`: cron orchestration + per-recipient send loop.
- `SmtpMailerService`: SMTP transport abstraction.
- `TestSendService`: isolated single send, no recipient mutation.
- `LockService`: global/per-campaign locking.
- `DashboardService`: aggregated metrics.

### 9.4 Filters
- `AuthFilter`, `RoleFilter`, `OwnershipFilter` for route/data protection.

### 9.5 Auth
- CI4 session-based auth for internal app.
- Password hashed with PHP `password_hash`.
- Optional forced password rotation/2FA for admin in production hardening.

### 9.6 CLI vs Controller for cron
- **Recommended:** CLI command `spark campaign:run`.
- Avoid HTTP cron endpoint for security and stability.
- Cron triggers CLI every minute.

---

## 10) Exact List of Files to Generate in Next Step (with exact paths)

### 10.1 Config
- `app/Config/Routes.php`
- `app/Config/Filters.php`
- `app/Config/Validation.php`

### 10.2 Migrations
- `app/Database/Migrations/2026-04-07-000001_CreateUsers.php`
- `app/Database/Migrations/2026-04-07-000002_CreateSenderAccounts.php`
- `app/Database/Migrations/2026-04-07-000003_CreateCampaigns.php`
- `app/Database/Migrations/2026-04-07-000004_CreateTemplates.php`
- `app/Database/Migrations/2026-04-07-000005_CreateRecipients.php`
- `app/Database/Migrations/2026-04-07-000006_CreateCampaignLogs.php`
- `app/Database/Migrations/2026-04-07-000007_AddOperationalColumnsAndIndexes.php`

### 10.3 Seeders
- `app/Database/Seeds/AdminUserSeeder.php`

### 10.4 Models
- `app/Models/UserModel.php`
- `app/Models/SenderAccountModel.php`
- `app/Models/CampaignModel.php`
- `app/Models/TemplateModel.php`
- `app/Models/RecipientModel.php`
- `app/Models/CampaignLogModel.php`

### 10.5 Filters
- `app/Filters/AuthFilter.php`
- `app/Filters/RoleFilter.php`
- `app/Filters/OwnershipFilter.php`

### 10.6 Services
- `app/Services/AuthService.php`
- `app/Services/CampaignService.php`
- `app/Services/CampaignStateService.php`
- `app/Services/RecipientImportService.php`
- `app/Services/CampaignDeliveryService.php`
- `app/Services/SmtpMailerService.php`
- `app/Services/TestSendService.php`
- `app/Services/LockService.php`
- `app/Services/DashboardService.php`

### 10.7 Commands
- `app/Commands/RunCampaignDelivery.php`

### 10.8 Controllers
- `app/Controllers/Auth/AuthController.php`
- `app/Controllers/Admin/UsersController.php`
- `app/Controllers/Admin/DashboardController.php`
- `app/Controllers/User/DashboardController.php`
- `app/Controllers/User/SenderAccountsController.php`
- `app/Controllers/User/CampaignsController.php`
- `app/Controllers/User/CampaignTemplatesController.php`
- `app/Controllers/User/CampaignRecipientsController.php`
- `app/Controllers/User/CampaignActionsController.php`
- `app/Controllers/User/CampaignLogsController.php`
- `app/Controllers/User/TestSendController.php`

### 10.9 Libraries/Validation
- `app/Libraries/TenantScope.php`
- `app/Validation/CampaignRules.php`
- `app/Validation/SenderAccountRules.php`
- `app/Validation/RecipientImportRules.php`

---

## Production Improvements (Scope-Safe)

1. Encrypt `smtp_pass` at rest with app key-managed encryption service.
2. Add rate-limits for test send and campaign actions.
3. Add audit logs for auth and critical actions (resume/cancel/user updates).
4. Add health checks/alerts for cron freshness (last successful run timestamp).
5. Add SMTP timeout/retry tuning per sender.
6. Add input sanitization for template HTML to reduce XSS risk in preview/admin UI.

These improvements keep your original scope unchanged while making production behavior safer.
