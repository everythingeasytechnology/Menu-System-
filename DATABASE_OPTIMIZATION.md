# Database Optimization

Date: 2026-08-08

## Index Strategy

Indexes were added for high-frequency API access patterns, tenant isolation, status dashboards, QR lookup, and financial reporting. The goal is to support growth without indexing every column.

## Businesses

- `businesses(status, type)`
  - Supports admin filtering by active businesses and business type.
- `businesses(owner_user_id)`
  - Supports owner profile and tenant lookup.

## Users

- `users(business_id, role)`
  - Supports staff listing and future permission screens.
- `users(business_id, status)`
  - Supports active staff lookup and notification fan-out.

## Tokens

- `personal_access_tokens(token)` unique
  - Required for fast bearer-token authentication by hashed token.
- `personal_access_tokens(user_id, revoked_at)`
  - Supports logout/revocation management.
- `personal_access_tokens(expires_at)`
  - Supports cleanup of expired mobile tokens.

## Menu Categories

- `menu_categories(business_id, active, sort_order)`
  - Supports public QR menu category listing.
- `menu_categories(business_id, status)`
  - Supports admin filters.
- `menu_categories(business_id, name)` unique
  - Allows same category names across different businesses while preventing duplicates inside a tenant.
- `menu_categories(business_id, code)` unique
  - Same tenant-safe behavior for category codes.

## Menu Items

- `menu_items(business_id, menu_category_id)`
  - Supports category filtering in mobile menu APIs.
- `menu_items(business_id, status, availability, stock)`
  - Supports public menu and availability filtering.
- `menu_items(business_id, category)`
  - Preserves efficient access for legacy category-name queries.
- `menu_item_variants(menu_item_id, price)`
  - Supports item/variant lookup and price ordering.

## Tables And Rooms

- `restaurant_tables(qr_identifier)` unique
  - Supports public QR scan lookup without exposing internal IDs.
- `restaurant_tables(business_id, name)` unique
  - Prevents duplicate table names per business.
- `restaurant_tables(business_id, status, is_active)`
  - Supports floor/table status dashboards.
- `rooms(qr_identifier)` unique
  - Supports public room QR scan lookup.
- `rooms(business_id, name)` unique
  - Prevents duplicate room names per business.
- `rooms(business_id, status, is_active)`
  - Supports room status dashboards.

## Orders

- `orders(order_number)` unique
  - Supports public order status checks without exposing internal IDs.
- `orders(business_id, order_status, created_at)`
  - Supports active/pending/preparing/ready/completed order screens.
- `orders(business_id, payment_status, created_at)`
  - Supports payment dashboards and revenue views.
- `orders(business_id, created_at)`
  - Supports date-range reports.
- `orders(table_id, order_status)`
  - Supports table active-order lookup.
- `orders(room_id, order_status)`
  - Supports room service active-order lookup.
- `orders(user_id)`
  - Supports staff/customer history.
- `orders(coupon_id)`
  - Supports coupon usage reporting.

## Order Items

- `order_items(order_id, menu_item_id)`
  - Supports order detail retrieval and item sales reports.
- `order_items(menu_item_variant_id)`
  - Supports variant-level analytics.

## Payments

- `payments(business_id, status, created_at)`
  - Supports payment history and status filters.
- `payments(order_id, status)`
  - Supports order payment status checks.
- `payments(transaction_id)`
  - Supports gateway reconciliation.

## Offers And Coupons

- `offers(business_id, is_active, starts_at, ends_at)`
  - Supports active offer lookup by tenant and date.
- `coupons(business_id, code)` unique
  - Supports coupon validation and prevents duplicate tenant codes.
- `coupons(business_id, is_active, expires_at)`
  - Supports active coupon filtering.

## Notifications

- `device_tokens(device_token)` unique
  - Prevents duplicate device token registrations.
- `device_tokens(user_id, is_active)`
  - Supports push fan-out for a user.
- `device_tokens(business_id, is_active)`
  - Supports future business-wide notification fan-out.
- `notifications(user_id, read_at, created_at)`
  - Supports unread count and notification inbox.
- `notifications(business_id, type, created_at)`
  - Supports operational notification dashboards.

## Audit Logs

- `audit_logs(business_id, action, created_at)`
  - Supports tenant audit timelines.
- `audit_logs(entity_type, entity_id)`
  - Supports entity history lookup.
- `audit_logs(user_id)`
  - Supports user action investigation.

## Avoided Indexes

Indexes were not added to every text or numeric field. Free-text menu search currently uses `LIKE`; if catalogs become large, the next step should be MySQL FULLTEXT indexes or a search service.
