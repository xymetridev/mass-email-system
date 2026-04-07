# Mass Email Campaign System — Project Control Rules

## Project Profile
- **Project name:** Mass Email Campaign System
- **Stack:** CodeIgniter 4, MySQL/MariaDB, AdminLTE, Bootstrap 5

## Core Domain Rules
- Use **campaign** as the main entity name across schema, code, docs, and UI.
- Roles are limited to exactly: **admin**, **user**.
- Non-admin users must only access and operate on their own data.
- One campaign has exactly one template (1:1 relationship).

## Processing & Delivery Rules
- Cron runs every **1 minute**.
- Recipient import must be processed line-by-line in a memory-efficient way.
- Prevent duplicate sending using both:
  - database constraints, and
  - safe, idempotent cron logic.
- Never expose SMTP passwords in logs, API responses, UI responses, or error traces.

## Out of Scope (Do Not Implement)
- Open tracking
- Click tracking
- Unsubscribe flow
- Scheduled future sending
- Attachments

## UI Reference
- Keep and use `ADMINLTE_UI_UX_SPEC.md` as the UI reference document.

## Implementation Guardrail
- Do not generate business code until explicitly requested.
