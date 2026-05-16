---
name: project-enterprise-modules
description: Enterprise support modules built in Phase 3 — comments, attachments, activity logs, audit logs, task history, queue jobs, archival
metadata:
  type: project
---

Phase 3 enterprise support modules are complete. All files follow the existing conventions: `declare(strict_types=1)`, `final` classes, UUID PKs, repository pattern, service layer, DTOs, Resources, Policies, Events, Listeners, Jobs.

**Why:** Requested to build full enterprise-grade support infrastructure on top of the existing Phase 1/2 multi-tenancy, auth, RBAC, projects/tasks foundation.

**How to apply:** When continuing work, check these are wired before adding new features. All new queue workers need `activity-logs`, `attachments`, `maintenance` queues configured.

## New Migrations (tenant/)
- `2026_05_13_300001_create_audit_logs_table.php` — immutable compliance audit log
- `2026_05_13_300002_add_parent_id_to_task_comments.php` — threaded comments (depth ≤ 3)
- `2026_05_13_300003_add_composite_indexes_to_activity_logs.php` — query optimization
- `2026_05_13_300004_create_activity_log_archives_table.php` — log archival table

## New Models
- `AuditLog` — security/compliance events (separate from ActivityLog)

## New Services
- `ActivityLogService` — async queue-based logging via `LogActivityJob` on `activity-logs` queue
- `AuditLogService` — synchronous compliance logging (never queued — cannot afford loss)
- `CommentService` — extracted from TaskService, handles threaded comments (max depth 3)
- `AttachmentService` — extracted from TaskService, adds finfo MIME validation + blocked extension list

## New Repositories + Interfaces
- `ActivityLogRepository` / `ActivityLogRepositoryInterface` — includes `archiveOlderThan()`
- `AuditLogRepository` / `AuditLogRepositoryInterface` — includes `pruneOlderThan()`
- `CommentRepository` / `CommentRepositoryInterface`
- `AttachmentRepository` / `AttachmentRepositoryInterface`

## New Events
- `CommentUpdated`, `CommentDeleted`, `AttachmentUploaded`, `AttachmentDeleted`

## New Listeners
- `HandleTaskCommentAdded` — notifies assignee via `TaskCommentAddedNotification`
- `RecordAuditEvent` — maps `Login`, `Logout`, `Failed`, `PasswordReset` → audit_logs

## New Jobs
- `LogActivityJob` — async write to activity_logs (5 tries, 10s backoff)
- `ArchiveActivityLogsJob` — bulk move old logs to archive table
- `PruneAuditLogsJob` — delete audit logs past retention period (default 365d)

## New Policies
- `TaskAttachmentPolicy` — upload/download/delete; download blocked if not virus-scanned clean
- `AuditLogPolicy` — company-admin+ only
- `TaskHistoryPolicy` — anyone who can view the task

## New Controllers
- `TaskHistoryController` — GET /api/v1/tasks/{task}/history
- `AuditLogController` (Admin) — GET /api/v1/admin/audit-logs[/{log}]

## New Resources
- `Admin/AuditLogResource`

## New Artisan Commands
- `logs:archive-activity` — triggers `ArchiveActivityLogsJob`
- `logs:prune-audit` — triggers `PruneAuditLogsJob` (requires --confirm or --confirm flag)

## Updated Files
- `TaskService` — now injects ActivityLogService, CommentService, AttachmentService; delegates accordingly
- `TaskComment` model — adds parent_id, depth, replies() HasMany, parent() BelongsTo
- `TaskCommentResource` — includes parent_id, depth, replies (nested)
- `StoreCommentRequest` — added parent_id validation
- `CreateCommentDTO` — added optional parentId
- `EventServiceProvider` — registered new events + auth audit listeners
- `RepositoryServiceProvider` — registered 4 new repository bindings
- `AuthorizationServiceProvider` — registered AuditLogPolicy, TaskAttachmentPolicy
- `routes/api.php` — added ALL task, comment, attachment, history routes + admin audit-log routes

## Queue Configuration Needed
Workers must process these queues: `activity-logs`, `attachments`, `maintenance`
