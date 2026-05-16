# Multi-Tenant SaaS Platform

A production-ready **multi-tenant SaaS project management platform** built with Laravel 11. Each tenant (company) runs in a completely isolated MySQL database. The platform supports team collaboration with projects, tasks, comments, attachments, real-time updates, and a full subscription billing cycle powered by Stripe.

---

## Features

| Feature | Details |
|---|---|
| **Multi-Tenancy** | Per-database isolation via `stancl/tenancy` v3.10 |
| **Authentication** | Laravel Sanctum — token-based API + session-based Web |
| **Authorization** | Role-based access control via Spatie Permission + Policies |
| **Billing** | Stripe Checkout — 3 plans (Starter / Professional / Enterprise), monthly & yearly cycles |
| **Project Management** | Projects → Tasks → Comments / Attachments, with status transitions and immutable audit history |
| **Real-time** | Laravel Reverb (WebSocket) — live task assignments, comments, and subscription events |
| **PDF Invoices** | DomPDF-generated invoice PDFs emailed on each payment |
| **Docker** | Full `docker-compose` stack (PHP-FPM + Nginx + MySQL 8 + Redis 7) |

---

## Tech Stack

- **Backend:** Laravel 11, PHP 8.2
- **Database:** MySQL 8 (central DB + per-tenant DB), Redis (cache + queues)
- **Auth:** Laravel Sanctum, Spatie Permission
- **Billing:** Stripe PHP SDK, DomPDF
- **Real-time:** Laravel Reverb (self-hosted WebSocket server)
- **Frontend:** Blade templates, Tailwind CSS, Vite
- **Architecture:** Domain-Driven Design — `app/Domain/`, Actions, DTOs, Repositories

---

## Subscription Plans

| Plan | Monthly | Yearly |
|---|---|---|
| Starter | Free | Free |
| Professional | $29 / mo | $290 / yr |
| Enterprise | $99 / mo | $990 / yr |

---

## Architecture Overview

```
app/
├── Actions/          # Single-responsibility use cases (RegisterTenant, HandleStripeWebhook…)
├── DataTransferObjects/
├── Domain/           # Domain events and aggregates
├── Http/
│   ├── Controllers/Api/
│   ├── Controllers/Web/
│   ├── Middleware/   # CheckSubscriptionActive, EnforceTenantScope…
│   ├── Requests/
│   └── Resources/
├── Infrastructure/   # Cache, Queue helpers, Eloquent global scopes
├── Listeners/        # Queued event listeners (afterCommit)
├── Models/           # Tenant-aware Eloquent models
├── Notifications/    # Email notifications (welcome, invoice, expiry…)
├── Providers/
├── Repositories/
└── Services/         # ProjectService, TaskService, ActivityLogService…

database/
├── migrations/        # Central DB migrations
└── migrations/tenant/ # Per-tenant DB migrations (run via Tenancy)
```

---

## Real-time Channel Topology

```
private-tenant.{tenantId}                     ← subscription lifecycle events
private-tenant.{tenantId}.project.{projectId} ← project mutations
private-tenant.{tenantId}.task.{taskId}       ← comment threads
private-tenant.{tenantId}.user.{userId}       ← task assignments
```

---

## Task Status Lifecycle

```
[todo] → [in_progress] → [in_review] → [done]
               │                          ▲
               └──────► [blocked] ────────┘
               │
               └──────► [cancelled]
```

---

## Getting Started

### Prerequisites

- PHP 8.2+
- Composer
- Node.js 18+
- MySQL 8
- Redis
- A [Stripe](https://stripe.com) account (for paid plans)

### Local Setup

```bash
# 1. Clone the repository
git clone <repo-url>
cd multi-tenant-saas

# 2. Install dependencies
composer install
npm install

# 3. Configure environment
cp .env.example .env
php artisan key:generate

# 4. Configure .env
#    Set DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD
#    Set APP_BASE_DOMAIN (e.g. app.test)
#    Set STRIPE_KEY, STRIPE_SECRET, STRIPE_WEBHOOK_SECRET
#    Set Stripe Price IDs for each plan/cycle

# 5. Run central database migrations and seed plans
php artisan migrate
php artisan db:seed --class=PlanSeeder

# 6. Build frontend assets
npm run build

# 7. Start all services (server + queue + reverb + vite)
composer run dev
```

The app will be available at `http://localhost:8000`. Tenants access their workspace via subdomain: `app.{subdomain}.com`.

---

### Docker Setup

```bash
# Copy and configure environment
cp .env.example .env
# Edit .env with your Stripe keys

# Build and start all containers
docker compose up -d --build

# Run migrations inside the container
docker compose exec app php artisan migrate
docker compose exec app php artisan db:seed --class=PlanSeeder
```

The app will be available at `http://localhost:8001`.

---

## Stripe Webhook Setup

```bash
# Using the Stripe CLI (for local development)
stripe listen --forward-to http://localhost:8001/api/stripe/webhook

# Copy the webhook signing secret and set in .env:
STRIPE_WEBHOOK_SECRET=whsec_...
```

---

## Environment Variables Reference

| Variable | Description |
|---|---|
| `APP_BASE_DOMAIN` | Base domain for tenant subdomains (e.g. `app.test`) |
| `STRIPE_KEY` | Stripe publishable key |
| `STRIPE_SECRET` | Stripe secret key |
| `STRIPE_WEBHOOK_SECRET` | Stripe webhook signing secret |
| `STRIPE_*_MONTHLY_PRICE_ID` | Stripe Price IDs for each plan/cycle |
| `BILLING_TRIAL_DAYS` | Trial period in days (default: 14) |
| `BILLING_GRACE_PERIOD_DAYS` | Grace period after subscription expires (default: 3) |
| `REVERB_APP_KEY` | Laravel Reverb app key |
| `REVERB_APP_SECRET` | Laravel Reverb app secret |
| `REVERB_PORT` | WebSocket port (default: 8080) |

---

## Running Tests

```bash
php artisan test
```

The test suite covers tenant isolation, authentication flows, role/permission enforcement, token management, password reset, and failed login tracking.

---

## Key Artisan Commands

| Command | Description |
|---|---|
| `php artisan tenants:migrate` | Run tenant migrations across all tenant databases |
| `php artisan billing:check-expired` | Check and update expired subscriptions (runs daily via scheduler) |
| `php artisan reverb:start` | Start the WebSocket server |
| `php artisan queue:listen` | Process queued jobs (broadcasts, emails, tenant provisioning) |

---

## Security

- **Tenant isolation:** three independent layers — separate MySQL database per tenant, `EnforceTenantScope` global Eloquent scope, and `CheckSubscriptionActive` middleware gate.
- **API auth:** Sanctum bearer tokens with rotation (`/api/v1/auth/refresh`) and bulk revocation (`/api/v1/auth/logout-all`).
- **File uploads:** MIME whitelist enforced server-side, 20 MB cap, tenant-scoped storage paths.
- **Stripe webhooks:** signature verification via `STRIPE_WEBHOOK_SECRET` on every inbound webhook.

---

## License

MIT
