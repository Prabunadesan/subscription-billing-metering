# Laravel 13 — Subscription Billing & Usage Metering

A multi-tenant SaaS subscription billing and usage-metering backend built with Laravel. The application supports merchants, customers, plans, subscriptions, usage ingestion, aggregation, billing, overage calculation, mid-cycle plan changes, dashboard reporting, caching, rate limiting, queues, and automated tests.

The application supports:

- Multi-tenant merchants
- Customers and subscriptions
- Usage event ingestion
- Idempotent usage recording
- Daily usage aggregation
- Plan pricing and overage calculation
- Mid-cycle plan upgrades/downgrades
- Prorated billing
- Invoice generation
- Usage-based dashboard
- Usage drop detection
- Projected overage revenue
- Queue-based processing
- Plan pricing cache
- API rate limiting
- Automated tests

---

# 1. Technology Stack

- PHP 8.5
- Laravel 13
- MySQL
- Redis-ready architecture
- Laravel Queue
- Laravel Cache
- Blade
- CSS
- PHPUnit

---

# 2. Architecture

The application follows a service-oriented Laravel architecture.

                         ┌─────────────────────┐
                         │      API Client      │
                         └──────────┬──────────┘
                                    │
                                    ▼
                         ┌─────────────────────┐
                         │ Laravel Controllers │
                         └──────────┬──────────┘
                                    │
                    ┌───────────────┼────────────────┐
                    │               │                │
                    ▼               ▼                ▼
             Usage Service   Plan Change Service   Dashboard
                    │               │                │
                    ▼               ▼                ▼
             usage_events     subscriptions      daily_usage
                    │                                  │
                    ▼                                  │
             Queue / Job                               │
                    │                                  │
                    ▼                                  │
             daily_usage ◄────────────────────────────┘
                    │
                    ▼
              Billing Service
                    │
                    ▼
          invoices / invoice_items
