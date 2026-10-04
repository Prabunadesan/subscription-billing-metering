##Subscription Billing & Usage Metering

A multi-tenant SaaS subscription billing and usage-metering backend built with Laravel. The application supports merchants, customers, plans, subscriptions, usage ingestion, aggregation, billing, overage calculation, mid-cycle plan changes, dashboard reporting, caching, rate limiting, queues, and automated tests.

##Technology Stack
•	PHP 8.5
•	Laravel 13
•	MySQL
•	Laravel Queue
•	Laravel Cache
•	Redis-ready architecture
•	Blade
•	CSS
## Installation

Clone the repository:

gh repo clone Prabunadesan/subscription-billing-metering

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

http://127.0.0.1:8000/dashboard/1
