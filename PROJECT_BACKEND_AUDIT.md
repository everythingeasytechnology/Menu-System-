# Project Backend Audit

Date: 2026-08-08

## Current Architecture

This project is a Laravel 13 restaurant/menu management application with a Blade-based admin dashboard. Before this upgrade it had web routes only in `routes/web.php`, controllers for settings, categories, menu items, and service points, and a small set of Eloquent models around menu and settings data.

The new backend keeps the existing UI/controllers intact and adds a versioned React Native-ready API in `routes/api.php` under `/api/v1`.

## Database Architecture Before Upgrade

Existing tables covered:

- `users`
- `business_settings`, `razorpay_settings`, `cash_settings`
- `menu_categories`
- `menu_items`
- `menu_item_variants`
- `preset_food_images`
- `service_points`
- Laravel cache/session/job tables

Missing production concepts before upgrade:

- Multi-business tenant table
- User role/business ownership fields
- Token-based mobile authentication
- Table and room QR identities
- Orders and order item snapshots
- Payments
- Offers and coupons
- Device push tokens
- Notifications
- Audit logs
- Versioned API resources and Form Request validation

## SQLite Usage

SQLite was active in the local `.env` via `DB_CONNECTION=sqlite`, and the Laravel skeleton still created `database/database.sqlite` during post-create setup. MySQL credentials existed in `.env` but were commented out.

Changes made:

- Production default database connection changed to MySQL in `config/database.php`.
- Queue database fallback changed to MySQL in `config/queue.php`.
- `.env.example` now shows MySQL configuration.
- Local `.env` was switched to `DB_CONNECTION=mysql` with the already-present MySQL values.
- Composer post-create script no longer creates `database/database.sqlite`.
- PHPUnit still uses SQLite `:memory:` for isolated automated tests only.

## MySQL Readiness

Verified:

- MySQL/MariaDB connection reached successfully.
- Configured database initially had 0 tables.
- Normal migrations were applied with `php artisan migrate --force`.
- `php artisan migrate:status` shows all migrations ran.
- JSON, decimal, timestamps, foreign keys, nullable fields, unique constraints, and indexes are defined using Laravel schema builders compatible with MySQL.

## Security Issues Found

- No mobile/API authentication existed.
- No tenant isolation existed for menu/service data.
- Existing Razorpay secret was rendered back into the Blade settings form.
- API error handling was not standardized.
- Existing web settings allowed fallback user behavior to keep local UI functional but does not represent production-grade auth.

Fixes:

- Added hashed bearer token table and middleware.
- Added business ownership fields and tenant checks in API controllers.
- Added API Form Requests and clean JSON errors.
- Removed stored Razorpay secret from the form value and preserve existing secret when blank.
- API Resources hide sensitive fields.

## Performance Issues Found

- Category web page used one count query per category.
- Existing menu page did not paginate.
- No production indexes existed for tenant-scoped mobile queries.
- No order/status/report indexes existed because order tables did not exist.

Fixes:

- Added tenant/status/date indexes across core tables.
- API list endpoints paginate.
- Order/dashboard/report endpoints use scoped queries and eager loading where needed.

## Missing Relationships Before Upgrade

- No `businesses` table or user-to-business ownership.
- `menu_items` related to categories by category name string only.
- `service_points` had no business or QR identity.
- No order/payment/notification relationships.

Fixes:

- Added relationships in models for business, users, menu, tables, rooms, orders, payments, coupons, notifications, and device tokens.
- Kept legacy `category` string for Blade compatibility while adding `menu_category_id` for API use.

## Recommended Architecture Implemented

Controller -> Form Request -> Service -> Eloquent model.

Implemented service layer where it matters:

- `OrderService` owns order creation, server-side totals, order status transitions, payment record creation, notification creation, and audit logging.
- `CouponService` owns coupon validity and discount calculations.
- `NotificationService` owns database notifications and queued push dispatch handoff.
- `AuditLogService` records important actions without logging secrets.

## Exact Files Requiring Modification

Modified core files:

- `bootstrap/app.php`
- `composer.json`
- `.env.example`
- `config/database.php`
- `config/queue.php`
- `app/Providers/AppServiceProvider.php`
- Existing models under `app/Models`
- `app/Http/Controllers/SettingsController.php`
- `resources/views/settings.blade.php`

New backend/API files:

- `routes/api.php`
- `app/Http/Middleware/AuthenticateApiToken.php`
- `app/Http/Controllers/Api/V1/*`
- `app/Http/Requests/Api/V1/*`
- `app/Http/Resources/Api/V1/*`
- `app/Services/*`
- `app/Jobs/SendExpoPushNotification.php`
- New models for businesses, tokens, operations, payments, promotions, notifications, and audit logs
- New migrations dated `2026_08_08_*`
- New API feature tests under `tests/Feature/Api/V1`

## Priority Summary

Priority 1 completed:

- MySQL activation
- Versioned API routing
- Token auth
- Tenant isolation
- Orders with server-side totals
- Public QR menu and ordering
- Notifications/device tokens
- Production error responses
- Critical tests

Priority 2 completed:

- Offers/coupons foundation
- Payments foundation
- Dashboard and reports endpoints
- Audit logging foundation
- Indexing documentation

Priority 3 remaining:

- Full role/permission policy matrix
- Real Expo provider HTTP integration
- Razorpay service/provider implementation
- WebSocket broadcasting
- Redis-backed queue/cache in production
- Advanced reporting exports
