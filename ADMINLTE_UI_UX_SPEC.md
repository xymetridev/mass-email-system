# AdminLTE UI/UX Specification (Mass Email System)

## Scope & Principles
- **Purpose:** Internal operational UI for creating, managing, sending, and monitoring email campaigns.
- **Framework/UI:** AdminLTE (Bootstrap-based), desktop-first but responsive for tablet/mobile.
- **Roles:**
  - **Admin:** Full visibility and control across all users, sender accounts, recipients, campaigns, logs.
  - **User:** Access only own entities (own sender accounts, templates, recipients, campaigns, logs, profile).
- **Workflow goals:** Fast campaign execution, minimal clicks, clear statuses, and predictable actions.
- **Status consistency:** Use one global badge system across Campaign + Recipient contexts.

---

## Global Status Badge System (Must Be Reused Everywhere)

### Campaign Statuses
- `Draft` → **secondary (gray)**
- `Scheduled` → **info (blue)**
- `Running` → **primary (indigo/blue)**
- `Paused` → **warning (yellow/orange)**
- `Completed` → **success (green)**
- `Failed` → **danger (red)**
- `Cancelled` → **dark (charcoal)**

### Recipient Delivery Statuses
- `Pending` → **secondary (gray)**
- `Queued` → **info (blue)**
- `Sent` → **success (green)**
- `Delivered` → **primary (indigo/blue)**
- `Opened` → **teal/cyan**
- `Clicked` → **purple**
- `Bounced` → **warning (yellow/orange)**
- `Rejected` → **danger (red)**
- `Unsubscribed` → **dark (charcoal)**
- `Complained` → **danger (red, outlined variant)**

> Keep labels identical in all tables, cards, filters, and logs.

---

## 1) Sidebar / Menu Structure

### Main Navigation (left sidebar)
1. **Dashboard**
2. **Campaigns**
   - All Campaigns
   - Create Campaign
   - Campaign Logs
3. **Recipients**
   - Upload Recipients
   - Recipient Lists / Status
4. **Templates**
5. **Sender Accounts**
6. **Users** *(Admin only)*
7. **Profile / Account**

### Visibility by Role
- **Admin:** sees all menu items.
- **User:** same menu except **Users** hidden; data filtered to current user ownership.

### Sidebar UX details
- Collapsible sidebar, remembers last state.
- Active route highlighted.
- Section badges optional (e.g., running campaigns count).
- Mobile: converts to off-canvas drawer.

---

## 2) Layout Structure

### Base AdminLTE layout
- **Top Navbar:**
  - Brand/logo + app name
  - Search (optional quick campaign/list search)
  - Notifications (system events)
  - User dropdown (Profile, Logout)
- **Left Sidebar:** primary menu above.
- **Content Wrapper:**
  - Page header (title + breadcrumb)
  - Action toolbar (contextual buttons)
  - Main content grid/cards/tables/forms
- **Footer:** version + environment label.

### Responsive behavior
- Breakpoints:
  - `lg+`: full sidebar + multi-column cards.
  - `md`: compact cards/tables with horizontal scroll.
  - `sm`: stacked cards, fixed bottom primary actions where useful.
- Tables must support:
  - horizontal scroll,
  - sticky header where possible,
  - condensed row mode for high-volume lists.

---

## 3) Dashboard Page

### Purpose
Single-glance operational status + quick actions.

### Components
1. **KPI Cards (top row)**
   - Total Campaigns
   - Running Campaigns
   - Today Sent
   - Delivery Rate (today/7d)
   - Bounce Rate
   - Active Sender Accounts
2. **Campaign Status Distribution** (donut or stacked bar)
3. **Recent Campaigns Table**
   - Name, Owner, Sender, Scheduled/Started, Status badge, Quick actions
4. **Delivery Trend Chart** (last 7/30 days)
5. **Recent Errors/Alerts Panel**
6. **Quick Actions**
   - New Campaign
   - Upload Recipients
   - Test Send

### Role behavior
- Admin: global metrics.
- User: own metrics only.

---

## 4) User Management Pages (Admin)

### 4.1 User List
- Columns: Name, Email, Role (`admin`/`user`), Status (active/inactive), Created At, Last Login, Actions.
- Filters: role, status, date created.
- Actions: Create, Edit, Activate/Deactivate, Reset Password.

### 4.2 Create/Edit User
- Fields: Name, Email, Password (create/reset), Role, Status.
- Validation inline + summary alert.

### 4.3 User Detail (optional)
- Profile basics + owned resources summary:
  - sender accounts count,
  - campaign count,
  - latest activity.

---

## 5) Sender Account Pages

### 5.1 Sender Account List
- Columns: Sender Name, Email/From Address, Provider/SMTP, Daily Limit, Health, Owner, Updated, Actions.
- Filters: owner (admin only), provider, health.
- Actions: Add Sender, Edit, Disable/Enable, Test Connection.

### 5.2 Add/Edit Sender Account
- Fields (generic):
  - Display Name
  - From Email
  - Reply-To (optional)
  - Transport Type (SMTP/API)
  - Credentials/config fields
  - Daily sending cap
  - Active toggle
- Security UX: mask secrets after save, explicit “update credential” action.

### 5.3 Sender Health View
- Last successful send, failure count (24h), throttling warning.

---

## 6) Campaign Pages

### 6.1 Campaign List
- Columns: Campaign Name, Owner, Template, Sender, Target List, Scheduled At, Sent/Total, Status, Actions.
- Top actions: Create Campaign.
- Filters: status, owner (admin), sender, date range.
- Row actions:
  - View
  - Edit (if Draft/Scheduled)
  - Pause/Resume (if Running/Paused)
  - Cancel
  - Duplicate

### 6.2 Create/Edit Campaign (Wizard-style recommended)
**Step 1:** Basic info (name, description, owner)
**Step 2:** Select sender account
**Step 3:** Select template
**Step 4:** Select recipients/list
**Step 5:** Schedule (now or datetime)
**Step 6:** Review + confirm

- Persistent summary sidebar on desktop.
- Validation blocks progression until required data is valid.

### 6.3 Campaign Detail
- Header: name + status badge + key actions.
- Sections:
  - Configuration snapshot
  - Execution progress bar
  - Delivery metrics cards
  - Recent recipient events table
  - Logs preview (link to full logs)

---

## 7) Template Page

### Template List
- Columns: Name, Subject, Owner, Updated At, Usage Count, Actions.
- Actions: New Template, Edit, Clone, Delete (with confirm).

### Template Editor
- Fields:
  - Template Name
  - Subject
  - HTML/Text body editor (tabs)
  - Variable helper panel (`{{first_name}}`, etc.)
- Features:
  - Live preview
  - Send test (opens test send modal)
  - Validation for missing subject/body

---

## 8) Recipient Upload Page

### Purpose
Fast bulk upload with clear mapping and validation.

### Flow
1. Download sample CSV format.
2. Upload file (drag/drop + browse).
3. Column mapping UI.
4. Preview first N rows.
5. Validation results summary:
   - valid count,
   - duplicates,
   - invalid emails,
   - missing required fields.
6. Confirm import.

### Required fields
- Email (required)
- Optional: first_name, last_name, phone, custom fields.

### Result
- Success card + link to corresponding recipient list/status page.

---

## 9) Recipient List / Status Page

### Recipient Lists view
- Columns: List Name, Owner, Total, Created, Last Used, Actions.
- Actions: View recipients, Rename, Archive/Delete.

### Recipient Status Detail (per list/campaign)
- Filter chips/tabs by delivery status badges.
- Search by email/name.
- Columns: Email, Name, Last Event, Event Time, Status badge, Error reason.
- Bulk actions: Export filtered rows.

### Role behavior
- Admin sees all lists + owners.
- User sees only own lists.

---

## 10) Test Send Page or Modal

### Option A (preferred): Reusable Modal
Accessible from Template editor, Sender account page, and Campaign review.

### Fields
- Sender account
- Recipient test email(s)
- Template selection (or current template prefilled)
- Optional variable JSON/key-values for preview substitution

### Output
- Immediate result state:
  - sent / failed
  - provider response snippet
  - timestamp

---

## 11) Campaign Logs Page

### Purpose
Operational troubleshooting + audit trail.

### Layout
- Filter bar:
  - campaign
  - owner (admin)
  - level (info/warn/error)
  - date/time range
  - status
- Log table:
  - Time, Campaign, Recipient, Event Type, Status badge, Message, Correlation ID
- Detail drawer/modal per log row with full payload metadata.

### Features
- Server-side pagination for large logs.
- Export current filtered view (CSV).

---

## 12) Profile / Account Page

### Sections
1. Profile info (name, email; email read-only if policy requires)
2. Password change
3. Notification preferences (optional)
4. API tokens/session list (optional internal need)

### Admin add-on
- Admin can navigate to user-specific account pages from user management.

---

## 13) Reusable Components

1. **StatusBadge**
   - Single source for campaign + recipient status color mapping.
2. **DataTable wrapper**
   - Search, sort, pagination, row actions, loading/empty states.
3. **FilterBar**
   - Date range, selects, quick-reset.
4. **MetricCard**
   - Title, value, delta, icon.
5. **FormSectionCard**
   - Consistent labeled form groups.
6. **ConfirmModal**
   - Destructive action confirmations.
7. **SideDrawer/DetailPanel**
   - Row detail without full navigation.
8. **UploadDropzone**
   - File drag/drop + validation messaging.
9. **ProgressStepper**
   - Campaign creation wizard navigation.
10. **Toast/Alert system**
   - success/warning/error/info messages with consistent placement.

---

## 14) Validation / Error / Loading / Empty States

### Validation standards
- Inline field errors under inputs.
- Top form summary alert for multi-error forms.
- Disable primary submit until required fields valid where feasible.

### Error states
- API/network error banner with retry action.
- Table load failure placeholder with “Reload”.
- Permission errors show explicit role-based message.

### Loading states
- Skeletons for KPI cards and tables.
- Button spinner + disabled state during submit.
- Progressive upload status for recipient import.

### Empty states
- Campaign list empty: “Create your first campaign” CTA.
- Templates empty: “Create template” CTA.
- Recipients empty: “Upload recipients” CTA.
- Logs empty for filters: “No events match current filters.”

### Consistency rules
- Same badge + same wording for same state.
- Same button hierarchy:
  - Primary = main action
  - Secondary = supportive
  - Danger = destructive

---

## 15) Recommended CI4 View File Structure

```text
app/
  Views/
    layouts/
      base.php
      auth.php
      partials/
        navbar.php
        sidebar.php
        footer.php
        breadcrumbs.php
        alerts.php

    components/
      status_badge.php
      metric_card.php
      data_table.php
      filter_bar.php
      confirm_modal.php
      upload_dropzone.php
      stepper.php
      empty_state.php
      loading_skeleton.php

    pages/
      dashboard/
        index.php

      users/                 (admin only)
        index.php
        create.php
        edit.php
        detail.php

      sender_accounts/
        index.php
        create.php
        edit.php
        health.php

      campaigns/
        index.php
        create.php
        edit.php
        detail.php
        logs.php

      templates/
        index.php
        editor.php

      recipients/
        upload.php
        lists.php
        status.php

      profile/
        index.php

    modals/
      test_send_modal.php
      log_detail_modal.php
      campaign_action_modal.php
```

### Naming conventions
- Use folder-per-domain (`campaigns`, `recipients`, etc.).
- Keep shared UI in `components/` and layout parts in `layouts/partials/`.
- Avoid duplicate markup for badges, table controls, and alerts.

### Access-control rendering notes
- Controller/service enforces data scope (admin=all, user=own).
- View should conditionally hide admin-only actions for non-admin role.

---

## UX Flow Summary (Fast Path)
1. User lands on Dashboard and clicks **Create Campaign**.
2. Completes wizard (sender → template → recipients → schedule).
3. Optional **Test Send** before final confirmation.
4. Campaign starts; user monitors progress in Campaign Detail and Logs.
5. Recipient status and failures are reviewed in Recipient Status + Logs.
