# Production Readiness

Date: 2026-08-08

## Database

- MySQL is now the production default in config and `.env.example`.
- Local `.env` was switched from SQLite to MySQL using the existing configured values.
- MySQL/MariaDB connection was verified.
- Configured MySQL database initially had 0 tables.
- All migrations ran successfully.
- New tables include businesses, API tokens, restaurant tables, rooms, orders, order items, payments, offers, coupons, device tokens, notifications, and audit logs.
- Existing menu/settings/service-point tables were extended through additive migrations.

## API

- Versioned API lives under `/api/v1`.
- 85 API routes are registered.
- Bearer-token authentication is implemented with hashed token storage.
- Public QR menu endpoints do not require staff login.
- Staff/admin operational endpoints require `api.token` middleware.
- API responses use `{ success, message, data }`.
- Validation errors use `{ success, message, errors }`.

## Security

- API tokens are stored as SHA-256 hashes and returned only once.
- Tenant isolation checks are implemented on business-owned API resources.
- Order totals are calculated server-side.
- Payment gateway response data and Razorpay key secret are not returned through API Resources.
- Stored Razorpay secret is no longer rendered into the Blade form.
- API exceptions return clean JSON responses without stack traces.
- API rate limiting is enabled at 120 requests/minute per user or IP.

## Performance

- API list endpoints paginate.
- Order detail endpoints eager-load order items/payments.
- Dashboard aggregates order counts and revenue in grouped queries.
- Indexes were added for tenant/status/date lookups, QR lookup, coupon validation, notification unread counts, and reports.
- Push notification dispatch is queued through a job handoff instead of blocking order creation.

## Notifications

- `device_tokens` stores Expo/mobile device tokens.
- `notifications` stores in-app notifications with read/unread state.
- APIs exist for token registration, token deactivation, notification list, unread count, mark read, and mark all read.
- `NotificationService` decouples business events from push provider delivery.
- `SendExpoPushNotification` is a queue-ready provider handoff point.

## Mobile

- React Native can authenticate with bearer tokens.
- QR menu browse/order flow is unauthenticated for customers.
- Menu, category, order, kitchen, waiter, payment, promotion, notification, dashboard, and report APIs are versioned.
- Financial calculations do not trust client-submitted totals or prices.

## Testing

Tests created:

- Auth registration/login/auth-required flow
- Business isolation
- Category/menu item creation
- Public QR menu and order creation
- Invalid/inactive QR handling
- Coupon validation/expiration
- Device token and notification read flow

Verification:

```bash
php artisan route:list --path=api/v1
php artisan test
php artisan migrate:status
php artisan config:clear
php artisan cache:clear
php artisan optimize:clear
```

Result:

- `php artisan test`: 31 passed, 113 assertions.
- MySQL `migrate:status`: all migrations ran.

## Production

Required environment variables:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.example
DB_CONNECTION=mysql
DB_HOST=your-mysql-host
DB_PORT=3306
DB_DATABASE=your-database
DB_USERNAME=your-database-user
DB_PASSWORD=your-database-password
QUEUE_CONNECTION=database
CACHE_STORE=database
SESSION_DRIVER=database
```

Recommended before public launch:

- Move queues to Redis or another production worker backend.
- Add HTTPS-only deployment with secure cookies.
- Add role policies for owner/admin/manager/waiter/kitchen scopes.
- Add real Expo provider delivery and retry handling.
- Add Razorpay service integration and webhook verification.
- Configure backups and monitoring.
