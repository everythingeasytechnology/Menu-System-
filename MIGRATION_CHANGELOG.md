# Migration Changelog

Date: 2026-08-08

## Existing Structure

The project originally had migrations for users, cache, jobs, business/payment settings, menu items, variants, categories, preset images, and service points.

## New Migrations

### `2026_08_08_000000_create_business_api_foundation.php`

Risk level: Low for empty database, Medium for deployed database.

Changes:

- Creates `businesses`.
- Adds `business_id`, `role`, `phone`, and `status` to `users`.
- Creates `personal_access_tokens`.
- Adds nullable `business_id` to business, Razorpay, and cash settings.

Reason:

- Adds multi-business ownership and secure token authentication while keeping existing records valid through nullable tenant fields.

### `2026_08_08_000100_extend_menu_and_service_point_schema.php`

Risk level: Medium.

Changes:

- Adds tenant, description, image, ordering, and status fields to `menu_categories`.
- Replaces global category name/code uniqueness with business-scoped uniqueness.
- Adds tenant, category foreign key, description, price, tax, preparation time, availability, ordering, and status fields to `menu_items`.
- Adds variant lookup index.
- Adds tenant, QR identifier, type, active flag, and indexes to `service_points`.

Reason:

- Makes the existing catalog usable for multi-business APIs without breaking legacy Blade pages.

### `2026_08_08_000200_create_operations_tables.php`

Risk level: Low.

Changes:

- Creates `restaurant_tables`.
- Creates `rooms`.
- Creates `orders`.
- Creates `order_items`.
- Creates `payments`.

Reason:

- Adds core ordering, table/room QR, item snapshot, and payment foundations.

### `2026_08_08_000300_create_promotions_notifications_and_audits.php`

Risk level: Low.

Changes:

- Creates `offers`.
- Creates `coupons`.
- Adds nullable `coupon_id` to `orders`.
- Creates `device_tokens`.
- Creates `notifications`.
- Creates `audit_logs`.

Reason:

- Adds promotions, Expo-ready notification storage, in-app notifications, and operational audit trail.

## Destructive Operations

No `DROP TABLE`, `DROP COLUMN`, `migrate:fresh`, `db:wipe`, or data reset was run.

Historical migrations already contained a menu image column replacement. That historical behavior was left unchanged.

## MySQL Execution

Read-only inspection showed the configured MySQL database had 0 tables before migration. Normal migrations were then applied with:

```bash
php artisan migrate --force
```

Post-migration status shows all migrations ran in batch 1.
