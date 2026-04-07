# Locked Architecture (Source of Truth)

## Final folder structure

```text
mass-email-system/
  docs/
    LOCKED_ARCHITECTURE.md
  src/
    config/
      env.ts
      database.ts
      logger.ts
    modules/
      auth/
        auth.controller.ts
        auth.service.ts
        auth.middleware.ts
        auth.types.ts
      tenant/
        tenant.middleware.ts
        tenant.types.ts
      user/
        user.controller.ts
        user.service.ts
        user.repository.ts
        user.types.ts
      campaign/
        campaign.controller.ts
        campaign.service.ts
        campaign.repository.ts
        campaign.validator.ts
        campaign.types.ts
      template/
        template.repository.ts
        template.types.ts
      recipient/
        recipient.controller.ts
        recipient.service.ts
        recipient.repository.ts
        recipient.validator.ts
        recipient.types.ts
      import/
        import.controller.ts
        import.service.ts
        import.parser.ts
        import.validator.ts
        import.types.ts
      sender/
        sender.client.ts
        sender.service.ts
      cron/
        campaign-dispatch.cron.ts
        cron.runner.ts
    db/
      migrations/
        0001_init.sql
      queries/
        campaign.sql
        recipient.sql
      seed/
        admin-user.sql
    shared/
      constants/
        roles.ts
        statuses.ts
      errors/
        app-error.ts
      http/
        response.ts
      utils/
        csv.ts
        idempotency.ts
    app.ts
    server.ts
  tests/
    integration/
      campaign.test.ts
      recipient-import.test.ts
      cron-dispatch.test.ts
      tenant-isolation.test.ts
    unit/
      campaign.service.test.ts
      import.service.test.ts
      cron.runner.test.ts
  package.json
  tsconfig.json
  .env.example
  README.md
```

## Final database schema

### tenants
- id (uuid, pk)
- name (varchar(120), not null)
- created_at (timestamptz, not null, default now())
- updated_at (timestamptz, not null, default now())

### users
- id (uuid, pk)
- tenant_id (uuid, not null, fk -> tenants.id)
- email (varchar(255), not null)
- password_hash (varchar(255), not null)
- role (enum: ADMIN, USER, not null)
- is_active (boolean, not null, default true)
- created_at (timestamptz, not null, default now())
- updated_at (timestamptz, not null, default now())

### campaigns
- id (uuid, pk)
- tenant_id (uuid, not null, fk -> tenants.id)
- created_by_user_id (uuid, not null, fk -> users.id)
- name (varchar(150), not null)
- subject (varchar(255), not null)
- status (enum: DRAFT, READY, RUNNING, PAUSED, COMPLETED, CANCELED, FAILED, not null, default DRAFT)
- scheduled_at (timestamptz, null)
- started_at (timestamptz, null)
- completed_at (timestamptz, null)
- canceled_at (timestamptz, null)
- failed_at (timestamptz, null)
- failure_reason (text, null)
- created_at (timestamptz, not null, default now())
- updated_at (timestamptz, not null, default now())

### templates
- id (uuid, pk)
- campaign_id (uuid, not null, fk -> campaigns.id)
- tenant_id (uuid, not null, fk -> tenants.id)
- content_html (text, not null)
- content_text (text, null)
- created_at (timestamptz, not null, default now())
- updated_at (timestamptz, not null, default now())

### recipients
- id (uuid, pk)
- campaign_id (uuid, not null, fk -> campaigns.id)
- tenant_id (uuid, not null, fk -> tenants.id)
- email (varchar(255), not null)
- full_name (varchar(150), null)
- variables_json (jsonb, not null, default '{}'::jsonb)
- status (enum: PENDING, SENT, FAILED, not null, default PENDING)
- attempts_count (int, not null, default 0)
- last_attempt_at (timestamptz, null)
- sent_at (timestamptz, null)
- failure_reason (text, null)
- provider_message_id (varchar(255), null)
- created_at (timestamptz, not null, default now())
- updated_at (timestamptz, not null, default now())

### send_attempts
- id (uuid, pk)
- campaign_id (uuid, not null, fk -> campaigns.id)
- recipient_id (uuid, not null, fk -> recipients.id)
- tenant_id (uuid, not null, fk -> tenants.id)
- idempotency_key (varchar(255), not null)
- provider_message_id (varchar(255), null)
- status (enum: SENT, FAILED, not null)
- error_message (text, null)
- attempted_at (timestamptz, not null, default now())

### import_jobs
- id (uuid, pk)
- campaign_id (uuid, not null, fk -> campaigns.id)
- tenant_id (uuid, not null, fk -> tenants.id)
- created_by_user_id (uuid, not null, fk -> users.id)
- source_filename (varchar(255), not null)
- total_rows (int, not null, default 0)
- success_rows (int, not null, default 0)
- failed_rows (int, not null, default 0)
- status (enum: PENDING, RUNNING, COMPLETED, FAILED, not null, default PENDING)
- error_summary (text, null)
- created_at (timestamptz, not null, default now())
- completed_at (timestamptz, null)

## Final foreign keys, unique constraints, and indexes

### Foreign keys
- users.tenant_id -> tenants.id (on delete restrict)
- campaigns.tenant_id -> tenants.id (on delete restrict)
- campaigns.created_by_user_id -> users.id (on delete restrict)
- templates.campaign_id -> campaigns.id (on delete cascade)
- templates.tenant_id -> tenants.id (on delete restrict)
- recipients.campaign_id -> campaigns.id (on delete cascade)
- recipients.tenant_id -> tenants.id (on delete restrict)
- send_attempts.campaign_id -> campaigns.id (on delete cascade)
- send_attempts.recipient_id -> recipients.id (on delete cascade)
- send_attempts.tenant_id -> tenants.id (on delete restrict)
- import_jobs.campaign_id -> campaigns.id (on delete cascade)
- import_jobs.tenant_id -> tenants.id (on delete restrict)
- import_jobs.created_by_user_id -> users.id (on delete restrict)

### Unique constraints
- users(email) unique
- templates(campaign_id) unique  -- enforces one template per campaign
- recipients(campaign_id, email) unique  -- dedupe inside campaign
- send_attempts(idempotency_key) unique  -- strong idempotency guard

### Indexes
- campaigns(tenant_id, status)
- campaigns(tenant_id, scheduled_at)
- recipients(campaign_id, status)
- recipients(tenant_id, campaign_id, status)
- recipients(tenant_id, email)
- send_attempts(recipient_id, attempted_at desc)
- import_jobs(tenant_id, created_at desc)

## Final role/access matrix

| Resource / Action | admin | user |
|---|---|---|
| View tenants | all tenants | own tenant only |
| Manage users | create/update/deactivate any tenant user | no |
| Create campaign | yes (any tenant) | yes (own tenant) |
| Read campaign | all campaigns | own tenant campaigns only |
| Update campaign (name/subject/template/schedule) | all campaigns | own tenant campaigns only |
| Change campaign status | all campaigns | own tenant campaigns only |
| Upload/import recipients | all campaigns | own tenant campaigns only |
| View recipients | all campaigns | own tenant campaigns only |
| Retry failed recipients | all campaigns | own tenant campaigns only |
| Run cron dispatcher | yes | no |
| View send attempts/import jobs | all tenants | own tenant only |

Tenant isolation rule: every non-admin query must include `tenant_id = current_user.tenant_id` at repository/query layer.

## Final cron strategy

- Frequency: every 1 minute (`* * * * *`).
- Single dispatcher process with DB transaction batches.
- Flow per tick:
  1. Select campaigns eligible for dispatch: status in (READY, RUNNING), scheduled_at <= now() (or null for immediate), not canceled/completed.
  2. Transition READY -> RUNNING atomically when first picked.
  3. For each campaign, lock a batch of PENDING recipients using `FOR UPDATE SKIP LOCKED`.
  4. For each recipient, compute deterministic idempotency key: `campaign_id + recipient_id`.
  5. Insert into send_attempts first; rely on unique `idempotency_key` to prevent duplicate processing.
  6. If insert conflicts, skip send (already processed).
  7. If inserted, send email via provider, then update recipient status:
     - success => SENT, set sent_at, provider_message_id
     - failure => FAILED, set failure_reason, attempts_count + 1
  8. When no PENDING recipients remain, mark campaign COMPLETED unless paused/canceled/failed manually.
- Safe concurrency: multiple workers allowed because `SKIP LOCKED` + unique idempotency key prevent duplicates.

## Final import strategy

- Input: CSV upload for one target campaign.
- Validation before persist:
  - required: email
  - optional: full_name, variables_json columns
  - normalize email lower-case + trim
  - reject invalid email rows
- Import execution:
  1. Create import_jobs row with status PENDING -> RUNNING.
  2. Stream CSV rows (no full file in memory).
  3. Upsert recipients by unique (campaign_id, email):
     - new row => status PENDING
     - existing row:
       - if status SENT: keep SENT (do not regress)
       - if status FAILED/PENDING: update profile fields, keep existing status
  4. Track row-level errors and counts.
  5. Mark import_jobs COMPLETED (or FAILED on fatal parser/storage errors).
- Scope control:
  - import only into DRAFT, READY, or PAUSED campaigns
  - deny import into COMPLETED/CANCELED/FAILED campaigns
- Tenant safety:
  - campaign must belong to requester tenant unless admin.

## Final list of files to generate next with exact file paths

- `docs/LOCKED_ARCHITECTURE.md`
- `src/shared/constants/roles.ts`
- `src/shared/constants/statuses.ts`
- `src/config/env.ts`
- `src/config/database.ts`
- `src/db/migrations/0001_init.sql`
- `src/modules/auth/auth.types.ts`
- `src/modules/auth/auth.middleware.ts`
- `src/modules/tenant/tenant.middleware.ts`
- `src/modules/campaign/campaign.types.ts`
- `src/modules/campaign/campaign.validator.ts`
- `src/modules/campaign/campaign.repository.ts`
- `src/modules/campaign/campaign.service.ts`
- `src/modules/campaign/campaign.controller.ts`
- `src/modules/template/template.repository.ts`
- `src/modules/recipient/recipient.types.ts`
- `src/modules/recipient/recipient.validator.ts`
- `src/modules/recipient/recipient.repository.ts`
- `src/modules/recipient/recipient.service.ts`
- `src/modules/recipient/recipient.controller.ts`
- `src/modules/import/import.types.ts`
- `src/modules/import/import.parser.ts`
- `src/modules/import/import.validator.ts`
- `src/modules/import/import.service.ts`
- `src/modules/import/import.controller.ts`
- `src/modules/sender/sender.client.ts`
- `src/modules/sender/sender.service.ts`
- `src/modules/cron/campaign-dispatch.cron.ts`
- `src/modules/cron/cron.runner.ts`
- `tests/integration/tenant-isolation.test.ts`
- `tests/integration/recipient-import.test.ts`
- `tests/integration/cron-dispatch.test.ts`
