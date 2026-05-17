# Web Documentation — Multi-Tenant SaaS

**Stack:** Laravel Blade + Tailwind CSS  
**Auth:** Session-based (web guard)  
**Multi-tenancy:** Domain-based — central domain for public pages, tenant subdomain for the app

---

## Table of Contents

1. [Domain Structure](#domain-structure)
2. [Central Domain Pages](#central-domain-pages)
3. [Tenant Domain Pages](#tenant-domain-pages)
4. [Authentication](#authentication)
5. [Projects](#projects)
6. [Tasks](#tasks)
7. [Billing](#billing)
8. [Users & Profile](#users--profile)
9. [Notifications](#notifications)
10. [Layouts](#layouts)

---

## Domain Structure

```
Central domain:  https://yourdomain.com          → public pages (home, register)
Tenant domain:   https://{slug}.yourdomain.com   → app pages (login, projects, tasks...)
```

All tenant routes are protected by:
- `InitializeTenancyByDomain` — resolves the tenant from the subdomain
- `PreventAccessFromCentralDomains` — blocks access from the central domain

---

## Central Domain Pages

### Home / Landing Page

```
GET /
```

Displays the marketing/landing page with the active plans list.

**View:** `welcome.blade.php`  
**Auth required:** No

---

### Company Registration

```
GET  /register          → Show registration form
POST /register          → Submit registration
GET  /register/success  → Registration success page
```

**View:** `register/index.blade.php`, `register/success.blade.php`  
**Auth required:** No

**Form Fields**

| Field | Type | Notes |
|-------|------|-------|
| `company_name` | text | Required |
| `slug` | text | Optional — auto-generated from company name |
| `name` | text | Owner's full name |
| `email` | email | Owner's email |
| `password` | password | Min 8 chars, mixed case + numbers |
| `password_confirmation` | password | Must match password |
| `plan_slug` | select | Choose from active plans |
| `billing_cycle` | radio | `monthly` or `yearly` |

On success, the user is redirected to `/register/success` which shows the tenant subdomain URL.

---

### Subscription Expired

```
GET /subscription/expired
```

Shown when a tenant's subscription has lapsed. Prompts to renew.

**View:** `billing/expired.blade.php`  
**Auth required:** No

---

## Tenant Domain Pages

All pages below are served from `https://{slug}.yourdomain.com`.

---

## Authentication

### Login Page

```
GET  /{tenant}/login   → Show login form
POST /{tenant}/login   → Submit credentials  (rate limit: 10/min)
POST /{tenant}/logout  → Logout
```

**View:** `auth/login.blade.php`  
**Auth required:** Guest only (login/show), Auth (logout)

**Login Form Fields**

| Field | Type | Notes |
|-------|------|-------|
| `email` | email | Required |
| `password` | password | Required |

After login, redirects to Projects index.  
After logout, redirects to login page.

---

## Projects

All project pages require authentication and an active subscription.

### Routes

| Method | URI | Page |
|--------|-----|------|
| `GET` | `/projects` | List all projects |
| `GET` | `/projects/create` | Create project form |
| `POST` | `/projects` | Store new project |
| `GET` | `/projects/{project}` | Project detail |
| `GET` | `/projects/{project}/edit` | Edit project form |
| `PUT/PATCH` | `/projects/{project}` | Update project |
| `DELETE` | `/projects/{project}` | Soft-delete project |
| `POST` | `/projects/{project}/restore` | Restore soft-deleted project |

**Views:** `projects/index.blade.php`, `projects/create.blade.php`, `projects/edit.blade.php`, `projects/show.blade.php`, `projects/_form.blade.php`

### Project Form Fields

| Field | Type | Notes |
|-------|------|-------|
| `name` | text | Required |
| `description` | textarea | Optional |
| `start_date` | date | Optional |
| `end_date` | date | Optional, must be after start_date |
| `budget` | number | Optional |
| `status` | select | Required — `draft`, `active`, `on_hold`, `completed`, `cancelled` |

### Status Badge

Rendered via `projects/_status_badge.blade.php`. Color-coded per status value.

---

## Tasks

All task pages require authentication and an active subscription.

### Routes

| Method | URI | Page |
|--------|-----|------|
| `GET` | `/tasks` | List all tasks |
| `GET` | `/tasks/create` | Create task form |
| `POST` | `/tasks` | Store new task |
| `GET` | `/tasks/{task}` | Task detail (with comments & attachments) |
| `GET` | `/tasks/{task}/edit` | Edit task form |
| `PUT/PATCH` | `/tasks/{task}` | Update task |
| `DELETE` | `/tasks/{task}` | Soft-delete task |
| `POST` | `/tasks/{task}/restore` | Restore soft-deleted task |

**Views:** `tasks/index.blade.php`, `tasks/create.blade.php`, `tasks/edit.blade.php`, `tasks/show.blade.php`, `tasks/_form.blade.php`

### Task Form Fields

| Field | Type | Notes |
|-------|------|-------|
| `project_id` | select | Required — must be an existing project |
| `title` | text | Required |
| `description` | textarea | Optional |
| `assigned_to` | select | Optional — tenant user |
| `priority` | select | Required — `low`, `medium`, `high`, `urgent` |
| `status` | select | Required — `todo`, `in_progress`, `review`, `done` |
| `due_date` | date | Optional |
| `estimated_hours` | number | Optional |

### Priority Badge

Rendered via `tasks/_priority_badge.blade.php`. Color-coded per priority.

### Status Badge

Rendered via `tasks/_status_badge.blade.php`. Color-coded per status.

---

### Task Comments (on Task Show Page)

| Method | URI | Action |
|--------|-----|--------|
| `POST` | `/tasks/{task}/comments` | Add a comment |
| `PATCH` | `/tasks/{task}/comments/{comment}` | Edit a comment (author only) |
| `DELETE` | `/tasks/{task}/comments/{comment}` | Delete a comment |

**Comment Form Fields**

| Field | Type | Notes |
|-------|------|-------|
| `content` | textarea | Required, 1–10 000 chars |
| `parent_id` | hidden | Optional — for threaded/nested replies |

---

### Task Attachments (on Task Show Page)

| Method | URI | Action |
|--------|-----|--------|
| `POST` | `/tasks/{task}/attachments` | Upload a file |
| `GET` | `/tasks/{task}/attachments/{attachment}/download` | Download a file |
| `DELETE` | `/tasks/{task}/attachments/{attachment}` | Delete a file |

**Upload Field**

| Field | Type | Notes |
|-------|------|-------|
| `file` | file | Required, max 20 MB |

**Allowed types:** Images, PDF, Word, Excel, PowerPoint, plain text, ZIP

---

## Billing

Requires authentication and active subscription. Tenant admin only.

### Routes

| Method | URI | Page |
|--------|-----|------|
| `GET` | `/billing` | Billing dashboard (plan, invoices) |
| `GET` | `/billing/invoices/{invoice}/download` | Download invoice PDF |

**View:** `billing/index.blade.php`, `billing/invoice-pdf.blade.php`

The billing dashboard shows:
- Current plan name and billing cycle
- Subscription status and renewal date
- Invoice history with download links

---

## Users & Profile

### Routes

| Method | URI | Page |
|--------|-----|------|
| `GET` | `/users/create` | Invite / create new user form |
| `POST` | `/users` | Store new user |
| `GET` | `/profile` | View own profile |
| `PATCH` | `/profile` | Update own profile |

**Views:** `users/create.blade.php`, `users/profile.blade.php`

### Create User Form Fields

| Field | Type | Notes |
|-------|------|-------|
| `name` | text | Required |
| `email` | email | Required, unique within tenant |
| `password` | password | Required, min 8 chars |
| `role` | select | Required — assign a role |

### Profile Form Fields

| Field | Type | Notes |
|-------|------|-------|
| `name` | text | Required |
| `email` | email | Required |
| `password` | password | Optional — leave blank to keep current |
| `password_confirmation` | password | Required if changing password |

---

## Notifications

### Routes

| Method | URI | Action |
|--------|-----|--------|
| `GET` | `/notifications` | List all notifications |
| `POST` | `/notifications/{id}/read` | Mark single notification as read |
| `POST` | `/notifications/read-all` | Mark all notifications as read |

---

## Layouts

### `layouts/app.blade.php`

Main authenticated layout used by all tenant app pages. Includes:
- Top navigation bar with tenant name, user menu, notification bell
- Sidebar navigation (Projects, Tasks, Billing, Profile)
- Flash message display (success / error)

### `layouts/guest.blade.php`

Minimal layout used for login and public pages. No sidebar.

---

## Middleware Summary

| Middleware | Purpose |
|------------|---------|
| `InitializeTenancyByDomain` | Resolves tenant from subdomain |
| `PreventAccessFromCentralDomains` | Blocks app routes on central domain |
| `auth` | Requires session login |
| `guest` | Redirects authenticated users away |
| `CheckSubscriptionActive` | Redirects to `/subscription/expired` if plan is lapsed |
| `throttle:10,1` | Rate-limits login to 10 attempts/minute |
