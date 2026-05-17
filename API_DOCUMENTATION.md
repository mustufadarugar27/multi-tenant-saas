# API Documentation — Multi-Tenant SaaS

**Base URL:** `https://{tenant}.yourdomain.com/api/v1`
**Version:** v1
**Auth:** Laravel Sanctum (Bearer token)
**Content-Type:** `application/json`

---

## Table of Contents

1. [Overview](#overview)
2. [Authentication](#authentication)
3. [Plans](#plans)
4. [Roles & Permissions (RBAC)](#roles--permissions-rbac)
5. [Activity Logs](#activity-logs)
6. [Projects](#projects)
7. [Tasks](#tasks)
8. [Task Comments](#task-comments)
9. [Task Attachments](#task-attachments)
10. [Admin — Audit Logs](#admin--audit-logs)
11. [Stripe Webhook](#stripe-webhook)
12. [Error Reference](#error-reference)

---

## Overview

### Multi-Tenancy

Each company operates on its own subdomain with an isolated database. All API requests (except plan listing and registration) must be made to the tenant's domain.

```
Central domain:  https://yourdomain.com          → registration, plans
Tenant domain:   https://{slug}.yourdomain.com   → all other API calls
```

### Request Headers

| Header | Value | Required |
|--------|-------|----------|
| `Content-Type` | `application/json` | Yes |
| `Accept` | `application/json` | Yes |
| `Authorization` | `Bearer {token}` | Required on protected routes |

### Standard Response Envelope

**Success**
```json
{
  "data": { ... },
  "message": "Optional success message"
}
```

**Paginated**
```json
{
  "data": [ ... ],
  "meta": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 20,
    "total": 98
  },
  "links": {
    "first": "...",
    "last": "...",
    "prev": null,
    "next": "..."
  }
}
```

**Error**
```json
{
  "message": "Human-readable error",
  "errors": {
    "field": ["Validation message"]
  }
}
```

---

## Authentication

All auth routes require the `IdentifyTenant` middleware — the request must originate from a valid tenant domain.

### Register Company

Creates a new tenant and provisions an isolated database. No auth required.

```
POST /api/v1/auth/register
```

**Request Body**

| Field | Type | Rules |
|-------|------|-------|
| `company_name` | string | required |
| `slug` | string | optional; auto-generated if omitted; alpha_dash, unique |
| `name` | string | required — owner's full name |
| `email` | string | required, email |
| `password` | string | required, min 8, confirmed, mixed case + numbers |
| `plan_slug` | string | required, must be an active plan slug |
| `billing_cycle` | string | required — `monthly` or `yearly` |

**Response `201`**
```json
{
  "message": "Company registered successfully",
  "data": {
    "tenant": { "id": "uuid", "name": "Acme Corp", "slug": "acme" },
    "user": { "id": "uuid", "name": "Jane Doe", "email": "jane@acme.com" },
    "token": "1|abc123..."
  }
}
```

---

### Login

```
POST /api/v1/auth/login
```

Rate limit: **10 requests/minute** per IP.

**Request Body**

| Field | Type | Rules |
|-------|------|-------|
| `email` | string | required |
| `password` | string | required |
| `device_name` | string | optional — labels the token |

**Response `200`**
```json
{
  "data": {
    "token": "1|abc123...",
    "token_type": "Bearer",
    "user": {
      "id": "uuid",
      "name": "Jane Doe",
      "email": "jane@acme.com",
      "role": "admin",
      "email_verified_at": "2024-01-15T10:00:00Z"
    }
  }
}
```

**Error `401`** — invalid credentials
**Error `423`** — account locked after 5 failed attempts (`locked_until` returned in body)

---

### Logout

Revokes the current token.

```
POST /api/v1/auth/logout
Authorization: Bearer {token}
```

**Response `200`**
```json
{ "message": "Logged out successfully" }
```

---

### Logout All Devices

Revokes all tokens for the authenticated user.

```
POST /api/v1/auth/logout-all
Authorization: Bearer {token}
```

**Response `200`**
```json
{ "message": "All sessions terminated" }
```

---

### Refresh Token

```
POST /api/v1/auth/refresh
Authorization: Bearer {token}
```

**Response `200`**
```json
{
  "data": {
    "token": "2|newtoken...",
    "token_type": "Bearer"
  }
}
```

---

### Get Current User

```
GET /api/v1/auth/me
Authorization: Bearer {token}
```

**Response `200`**
```json
{
  "data": {
    "id": "uuid",
    "name": "Jane Doe",
    "email": "jane@acme.com",
    "role": "admin",
    "is_active": true,
    "email_verified_at": "2024-01-15T10:00:00Z",
    "last_login_at": "2024-05-10T08:32:00Z",
    "tenant": {
      "id": "uuid",
      "name": "Acme Corp",
      "slug": "acme",
      "plan": { "name": "Pro", "slug": "pro" },
      "subscription_status": "active"
    }
  }
}
```

---

### Verify Email

Sent in the verification email as a signed link.

```
GET /api/v1/auth/email/verify/{id}/{hash}
Authorization: Bearer {token}
```

**Response `200`**
```json
{ "message": "Email verified successfully" }
```

---

### Resend Verification Email

```
POST /api/v1/auth/email/resend
Authorization: Bearer {token}
```

Rate limit: **6 requests/minute**.

**Response `200`**
```json
{ "message": "Verification email sent" }
```

---

### Forgot Password

Always returns `200` to prevent user enumeration.

```
POST /api/v1/auth/forgot-password
```

Rate limit: **5 requests/minute**.

**Request Body**

| Field | Type | Rules |
|-------|------|-------|
| `email` | string | required, email |

**Response `200`**
```json
{ "message": "If that email is registered, a reset link has been sent." }
```

---

### Reset Password

```
POST /api/v1/auth/reset-password
```

Rate limit: **5 requests/minute**.

**Request Body**

| Field | Type | Rules |
|-------|------|-------|
| `email` | string | required |
| `token` | string | required, 64 chars |
| `password` | string | required, confirmed, min 12, mixed case + numbers + symbols |

**Response `200`**
```json
{ "message": "Password reset successfully" }
```

---

## Plans

Public endpoints — no authentication required.

### List Plans

```
GET /api/v1/plans
```

**Response `200`**
```json
{
  "data": [
    {
      "id": "uuid",
      "name": "Starter",
      "slug": "starter",
      "description": "For small teams",
      "price_monthly": "29.00",
      "price_yearly": "290.00",
      "max_users": 5,
      "storage_gb": 10,
      "features": ["feature_a", "feature_b"],
      "limits": { "projects": 10, "tasks_per_project": 100 },
      "is_free": false,
      "sort_order": 1
    }
  ]
}
```

---

### Get Plan

```
GET /api/v1/plans/{plan}
```

**Response `200`** — same shape as single item above.

---

## Roles & Permissions (RBAC)

All RBAC endpoints require authentication and a verified email.

### List Roles

Returns all roles with their assigned permissions.

```
GET /api/v1/roles
Authorization: Bearer {token}
```

Requires permission: `manage-rbac`

**Response `200`**
```json
{
  "data": [
    {
      "id": 1,
      "name": "admin",
      "permissions": ["projects.create", "tasks.delete", "users.manage"]
    }
  ]
}
```

---

### Assign Role to User

```
POST /api/v1/users/{userId}/roles
Authorization: Bearer {token}
```

Caller cannot assign a role higher than their own.

**Request Body**

| Field | Type | Rules |
|-------|------|-------|
| `role` | string | required, valid role name |

**Response `200`**
```json
{ "message": "Role assigned successfully" }
```

---

### Revoke Role from User

```
DELETE /api/v1/users/{userId}/roles/{role}
Authorization: Bearer {token}
```

**Response `200`**
```json
{ "message": "Role revoked successfully" }
```

---

### List Permissions

Returns all permissions grouped by category.

```
GET /api/v1/permissions
Authorization: Bearer {token}
```

**Response `200`**
```json
{
  "data": {
    "projects": ["projects.view", "projects.create", "projects.update", "projects.delete"],
    "tasks": ["tasks.view", "tasks.create", "tasks.update", "tasks.delete"],
    "users": ["users.manage"],
    "billing": ["billing.view"]
  }
}
```

---

### Get Role Permissions

```
GET /api/v1/roles/{role}/permissions
Authorization: Bearer {token}
```

**Response `200`**
```json
{
  "data": {
    "role": "editor",
    "permissions": ["projects.view", "tasks.create", "tasks.update"]
  }
}
```

---

### Grant Permission to Role

```
POST /api/v1/roles/{role}/permissions/{permission}
Authorization: Bearer {token}
```

Requires permission: `manage`

**Response `200`**
```json
{ "message": "Permission granted" }
```

---

### Revoke Permission from Role

```
DELETE /api/v1/roles/{role}/permissions/{permission}
Authorization: Bearer {token}
```

**Response `200`**
```json
{ "message": "Permission revoked" }
```

---

## Activity Logs

### List All Activity Logs

```
GET /api/v1/activity-logs
Authorization: Bearer {token}
```

Requires permission: `viewAny`

**Query Parameters**

| Param | Type | Description |
|-------|------|-------------|
| `event` | string | Filter by event name |
| `user_id` | uuid | Filter by user |
| `from` | date `Y-m-d` | Start date |
| `to` | date `Y-m-d` | End date |
| `per_page` | int | Default: 25 |

**Response `200`** — paginated list of activity log entries.

---

### My Activity Logs

Returns only the authenticated user's own logs.

```
GET /api/v1/activity-logs/me
Authorization: Bearer {token}
```

Supports the same query parameters as above.

---

## Projects

All project endpoints require authentication and a verified email.

### List Projects

```
GET /api/v1/projects
Authorization: Bearer {token}
```

**Query Parameters**

| Param | Type | Description |
|-------|------|-------------|
| `search` | string | Full-text search on name/description |
| `status` | string | Filter by status |
| `date_from` | date | Filter by start_date |
| `date_to` | date | Filter by end_date |
| `created_by` | uuid | Filter by creator |
| `sort_by` | string | `name`, `status`, `budget`, `start_date`, `end_date`, `created_at` |
| `sort_dir` | string | `asc` or `desc` |
| `per_page` | int | 1–100, default 20 |

**Response `200`** — paginated project list.

---

### Create Project

```
POST /api/v1/projects
Authorization: Bearer {token}
```

**Request Body**

| Field | Type | Rules |
|-------|------|-------|
| `name` | string | required |
| `description` | string | optional |
| `start_date` | date | optional |
| `end_date` | date | optional, after `start_date` |
| `budget` | numeric | optional |
| `status` | string | required, valid status value |

**Response `201`**
```json
{
  "data": {
    "id": "uuid",
    "name": "Website Redesign",
    "description": "...",
    "start_date": "2024-06-01",
    "end_date": "2024-08-31",
    "budget": "15000.00",
    "status": "active",
    "created_by": "uuid",
    "created_at": "2024-05-17T09:00:00Z"
  }
}
```

---

### Get Project

```
GET /api/v1/projects/{project}
Authorization: Bearer {token}
```

**Response `200`** — project object with task count and creator.

---

### Update Project

```
PUT /api/v1/projects/{project}
Authorization: Bearer {token}
```

All fields are optional. Same field rules as Create.

**Response `200`** — updated project object.

---

### Delete Project

Soft-deletes the project. Restorable.

```
DELETE /api/v1/projects/{project}
Authorization: Bearer {token}
```

**Response `200`**
```json
{ "message": "Project deleted successfully" }
```

---

### Restore Project

```
POST /api/v1/projects/{project}/restore
Authorization: Bearer {token}
```

**Response `200`**
```json
{ "message": "Project restored successfully" }
```

---

### Get Project History

Immutable change log for the project.

```
GET /api/v1/projects/{project}/history
Authorization: Bearer {token}
```

**Response `200`**
```json
{
  "data": [
    {
      "id": "uuid",
      "user": { "id": "uuid", "name": "Jane Doe" },
      "field": "status",
      "old_value": "draft",
      "new_value": "active",
      "changed_at": "2024-05-17T10:30:00Z"
    }
  ]
}
```

---

## Tasks

### List Tasks

```
GET /api/v1/tasks
Authorization: Bearer {token}
```

**Query Parameters**

| Param | Type | Description |
|-------|------|-------------|
| `project_id` | uuid | Filter by project |
| `assigned_to` | uuid | Filter by assignee |
| `status` | string | Filter by status |
| `priority` | string | Filter by priority |
| `search` | string | Full-text search on title/description |
| `due_from` | date | Filter by due_date start |
| `due_to` | date | Filter by due_date end |
| `overdue` | boolean | Return only overdue tasks |
| `sort_by` | string | `title`, `status`, `priority`, `due_date`, `created_at` |
| `sort_dir` | string | `asc` or `desc` |
| `per_page` | int | 5–100, default 20 |

---

### Create Task

```
POST /api/v1/tasks
Authorization: Bearer {token}
```

**Request Body**

| Field | Type | Rules |
|-------|------|-------|
| `project_id` | uuid | required, must exist |
| `title` | string | required |
| `description` | string | optional |
| `assigned_to` | uuid | optional, must be a tenant user |
| `priority` | string | required — `low`, `medium`, `high`, `urgent` |
| `status` | string | required — `todo`, `in_progress`, `review`, `done` |
| `due_date` | date | optional |
| `estimated_hours` | numeric | optional |

**Response `201`** — created task object.

---

### Get Task

```
GET /api/v1/tasks/{task}
Authorization: Bearer {token}
```

**Response `200`** — task with comment count and attachment count.

---

### Update Task

```
PUT /api/v1/tasks/{task}
Authorization: Bearer {token}
```

All fields optional. Includes `actual_hours` (not available on create).

**Response `200`** — updated task object.

---

### Delete Task

```
DELETE /api/v1/tasks/{task}
Authorization: Bearer {token}
```

**Response `200`**
```json
{ "message": "Task deleted successfully" }
```

---

### Restore Task

```
POST /api/v1/tasks/{task}/restore
Authorization: Bearer {token}
```

**Response `200`**
```json
{ "message": "Task restored successfully" }
```

---

### Get Task History

```
GET /api/v1/tasks/{task}/history
Authorization: Bearer {token}
```

Same shape as [Project History](#get-project-history).

---

## Task Comments

Threaded comments with unlimited nesting depth.

### List Comments

```
GET /api/v1/tasks/{task}/comments
Authorization: Bearer {token}
```

Returns paginated top-level comments with nested replies.

---

### Add Comment

```
POST /api/v1/tasks/{task}/comments
Authorization: Bearer {token}
```

**Request Body**

| Field | Type | Rules |
|-------|------|-------|
| `content` | string | required, 1–10 000 chars |
| `parent_id` | uuid | optional — ID of parent comment for threading |

**Response `201`**
```json
{
  "data": {
    "id": "uuid",
    "content": "Looks good, approved.",
    "parent_id": null,
    "author": { "id": "uuid", "name": "Jane Doe" },
    "created_at": "2024-05-17T11:00:00Z",
    "replies": []
  }
}
```

---

### Update Comment

Only the comment author may update.

```
PUT /api/v1/tasks/{task}/comments/{comment}
Authorization: Bearer {token}
```

**Request Body**

| Field | Type | Rules |
|-------|------|-------|
| `content` | string | required, 1–10 000 chars |

**Response `200`** — updated comment object.

---

### Delete Comment

```
DELETE /api/v1/tasks/{task}/comments/{comment}
Authorization: Bearer {token}
```

**Response `200`**
```json
{ "message": "Comment deleted" }
```

---

## Task Attachments

### List Attachments

```
GET /api/v1/tasks/{task}/attachments
Authorization: Bearer {token}
```

**Response `200`** — paginated list of attachment metadata (no file bytes).

---

### Upload Attachment

```
POST /api/v1/tasks/{task}/attachments
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

**Form Fields**

| Field | Type | Rules |
|-------|------|-------|
| `file` | file | required, max **20 MB** |

**Allowed MIME types:** `image/*`, `application/pdf`, Word/Excel/PowerPoint (`.doc`, `.docx`, `.xls`, `.xlsx`, `.ppt`, `.pptx`), plain text, `application/zip`

**Response `201`**
```json
{
  "data": {
    "id": "uuid",
    "filename": "design-mockup.pdf",
    "size": 204800,
    "mime_type": "application/pdf",
    "uploaded_by": { "id": "uuid", "name": "Jane Doe" },
    "created_at": "2024-05-17T11:05:00Z"
  }
}
```

---

### Delete Attachment

```
DELETE /api/v1/tasks/{task}/attachments/{attachment}
Authorization: Bearer {token}
```

**Response `200`**
```json
{ "message": "Attachment deleted" }
```

---

## Admin — Audit Logs

Company-level compliance view. Requires admin role.

### List Audit Logs

```
GET /api/v1/admin/audit-logs
Authorization: Bearer {token}
```

**Query Parameters**

| Param | Type | Description |
|-------|------|-------------|
| `actor_id` | uuid | Filter by user who performed the action |
| `action` | string | Filter by action name |
| `resource_type` | string | e.g. `project`, `task`, `user` |
| `resource_id` | uuid | Filter by specific resource |
| `status` | string | `success` or `failure` |
| `from` | date | Start date |
| `to` | date | End date |
| `per_page` | int | Default 25 |

**Response `200`** — paginated audit log list.

---

### Get Audit Log

```
GET /api/v1/admin/audit-logs/{log}
Authorization: Bearer {token}
```

**Response `200`**
```json
{
  "data": {
    "id": "uuid",
    "actor": { "id": "uuid", "name": "Jane Doe" },
    "action": "project.deleted",
    "resource_type": "project",
    "resource_id": "uuid",
    "status": "success",
    "metadata": { "project_name": "Website Redesign" },
    "ip_address": "203.0.113.10",
    "performed_at": "2024-05-17T12:00:00Z"
  }
}
```

---

## Stripe Webhook

Handled by Stripe; do not call manually.

```
POST /stripe/webhook
```

Stripe signature is verified via `STRIPE_WEBHOOK_SECRET`. Processes:
- `checkout.session.completed` — activates subscription
- `invoice.payment_succeeded` — renews subscription period
- `invoice.payment_failed` — marks subscription past-due
- `customer.subscription.deleted` — cancels/suspends tenant

---

## Error Reference

| HTTP Status | Meaning |
|-------------|---------|
| `400` | Bad request — malformed JSON or missing required field |
| `401` | Unauthenticated — missing or invalid Bearer token |
| `403` | Forbidden — authenticated but lacks permission |
| `404` | Resource not found |
| `409` | Conflict — e.g. slug already taken |
| `422` | Validation failed — `errors` object contains field-level messages |
| `423` | Locked — account locked after repeated failed logins |
| `429` | Too Many Requests — rate limit exceeded |
| `500` | Server error |

### Validation Error Example

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": ["The email field is required."],
    "password": ["The password must be at least 8 characters."]
  }
}
```

---

## Rate Limits Summary

| Endpoint | Limit |
|----------|-------|
| `POST /auth/login` | 10 req/min |
| `POST /auth/email/resend` | 6 req/min |
| `POST /auth/forgot-password` | 5 req/min |
| `POST /auth/reset-password` | 5 req/min |

---

## Security Notes

- Tokens are scoped to a tenant — a token from tenant A will be rejected on tenant B's domain.
- Accounts are locked for a time-based window after 5 consecutive failed login attempts.
- Email must be verified before accessing protected resources.
- Password reset tokens are single-use and expire automatically.
- All file uploads are virus-scanned and stored in tenant-isolated storage paths.
