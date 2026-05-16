# Multi-Tenant SaaS Platform

A **multi-tenant SaaS project management platform** built with Laravel 11. Each tenant (company) runs in a completely isolated MySQL database. The platform supports team collaboration with projects, tasks, comments, attachments, real-time updates, and a full subscription billing cycle powered by Stripe.

---

## Features

| Feature | Details |
|---|---|
| **Multi-Tenancy** | Per-database isolation via `stancl/tenancy` v3.10 |
| **Authentication** | Laravel Sanctum — token-based API + session-based Web |
| **Authorization** | Role-based access control via Policies |
| **Billing** | Stripe Checkout — 3 plans (Starter / Professional / Enterprise), monthly & yearly cycles |
| **Project Management** | Projects → Tasks → Comments / Attachments, with status transitions and immutable audit history |
| **Real-time** | Laravel Reverb (WebSocket) — live task assignments, comments, and subscription events |
| **PDF Invoices** | DomPDF-generated invoice PDFs emailed on each payment |
| **Queue Dashboard** | Laravel Horizon — monitor and retry queued jobs via `/horizon` |

---

## Tech Stack

- **Backend:** Laravel 11, PHP 8.2
- **Database:** MySQL 8 (central DB + per-tenant DB), Redis (cache + queues + sessions)
- **Auth:** Laravel Sanctum, Spatie Permission
- **Billing:** Stripe PHP SDK, DomPDF
- **Real-time:** Laravel Reverb (self-hosted WebSocket server)
- **Queue:** Laravel Horizon
- **Frontend:** Blade templates, Tailwind CSS, Vite

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
├── Domain/           # Domain events and aggregates
├── Http/
│   ├── Controllers/Api/
│   ├── Controllers/Web/
│   ├── Middleware/
│   ├── Requests/
│   └── Resources/
├── Models/           # Tenant-aware Eloquent models
├── Providers/
├── Repositories/
└── Services/

database/
├── migrations/        # Central DB migrations
└── migrations/tenant/ # Per-tenant DB migrations (run via Tenancy)
```

---

## Getting Started

### Prerequisites

- [Docker](https://docs.docker.com/get-docker/) and Docker Compose
- A [Stripe](https://stripe.com) account (for paid plans)

That's it. PHP, Node, MySQL, and Redis all run inside containers.

---

### First-time Setup

```bash
# 1. Clone the repository
git clone <repo-url>
cd multi-tenant-saas

# 2. Run setup — copies .env.example → .env, builds containers,
#    installs dependencies, generates app key, runs migrations
make setup

# 3. Fill in your Stripe keys in .env
#    STRIPE_KEY, STRIPE_SECRET, STRIPE_WEBHOOK_SECRET
#    STRIPE_*_PRICE_ID for each plan/cycle

# 4. Seed the plans
make artisan db:seed --class=PlanSeeder
```

The app is now running at **http://localhost**.

---

### Docker Services

| Service | Description | Port |
|---|---|---|
| `nginx` | Web server | `80` |
| `app` | PHP 8.5-FPM | — |
| `mysql` | MySQL 8 | `3306` |
| `redis` | Redis 7 | `6379` |
| `horizon` | Queue dashboard | `/horizon` |
| `reverb` | WebSocket server | `8080` |
| `node` | Vite dev server (HMR) | `5173` |
| `phpmyadmin` | DB management UI | `8081` |

---

### Make Commands

```bash
make help          # Show all available commands

make up            # Start all containers
make down          # Stop all containers
make logs          # Follow logs from all containers

make artisan <cmd> # Run php artisan inside app container
make composer <cmd># Run composer inside app container
make php <cmd>     # Run php inside app container
make npm <cmd>     # Run npm inside node container
make node <cmd>    # Run node inside node container
```

Examples:

```bash
make artisan migrate
make artisan migrate:fresh --seed
make artisan tinker
make composer require vendor/package
make npm install
```

---

## Tenant Subdomains

Tenants access their workspace via subdomain: `{subdomain}.localhost`.

For local development you may need to add entries to `/etc/hosts`:

```
127.0.0.1  tenant1.localhost
127.0.0.1  tenant2.localhost
```

---

## Stripe Webhook Setup

```bash
# Using the Stripe CLI (local development)
stripe listen --forward-to http://localhost/api/stripe/webhook

# Copy the signing secret shown and set it in .env:
STRIPE_WEBHOOK_SECRET=whsec_...
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

## Environment Variables Reference

| Variable | Description |
|---|---|
| `APP_BASE_DOMAIN` | Central domain — tenants use `{slug}.{APP_BASE_DOMAIN}` |
| `DB_HOST` | MySQL host — `mysql` when using Docker |
| `REDIS_HOST` | Redis host — `redis` when using Docker |
| `STRIPE_KEY` | Stripe publishable key |
| `STRIPE_SECRET` | Stripe secret key |
| `STRIPE_WEBHOOK_SECRET` | Stripe webhook signing secret |
| `STRIPE_*_PRICE_ID` | Stripe Price IDs for each plan/cycle |
| `BILLING_TRIAL_DAYS` | Trial period in days (default: 14) |
| `BILLING_GRACE_PERIOD_DAYS` | Grace period after subscription expires (default: 3) |
| `REVERB_APP_KEY` | Laravel Reverb app key |
| `REVERB_APP_SECRET` | Laravel Reverb app secret |
| `REVERB_PORT` | WebSocket port (default: 8080) |

---

## Running Tests

```bash
make artisan test
```

---

## Key Artisan Commands

| Command | Description |
|---|---|
| `make artisan tenants:migrate` | Run tenant migrations across all tenant databases |
| `make artisan billing:check-expired` | Check and update expired subscriptions |
| `make artisan horizon:snapshot` | Take a Horizon metrics snapshot |
| `make artisan queue:clear` | Clear all pending jobs from the queue |

---

## Security

- **Tenant isolation:** separate MySQL database per tenant + `CheckSubscriptionActive` middleware gate.
- **API auth:** Sanctum bearer tokens with rotation (`/api/v1/auth/refresh`) and bulk revocation (`/api/v1/auth/logout-all`).
- **File uploads:** MIME whitelist enforced server-side, 20 MB cap, tenant-scoped storage paths.
- **Stripe webhooks:** signature verification via `STRIPE_WEBHOOK_SECRET` on every inbound webhook.

---

## License

MIT
