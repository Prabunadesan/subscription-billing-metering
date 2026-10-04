# Laravel — Subscription Billing & Usage Metering

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
## Installation

Clone the repository:

https://github.com/Prabunadesan/subscription-billing-metering.git

Go to the project:

cd subscription-billing-metering

Install PHP dependencies:

composer install

Copy environment file:

cp .env.example .env

Generate application key:

php artisan key:generate

Configure database in '.env'.

Run migrations:

php artisan migrate

Start Laravel:

php artisan serve

Application:

http://127.0.0.1:8000


## Author

Prabu Nadesan
