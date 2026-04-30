# ECommerce Marketplace Platform

Modern PHP (OOP + MVC) marketplace with production-focused checkout, Stripe payments, Google login, admin management, and advanced commerce features.

## Core Features

- Professional storefront with responsive product listing (PLP) and product details (PDP)
- Advanced search with filtering, sorting, and pagination
- Secure checkout with Stripe Elements and webhook processing
- Google social login and classic email/password authentication
- Password reset flow (forgot password + token reset)
- Wishlist system and coupon engine
- Shipping tracking integration (DHL redirect by tracking number)
- Order lifecycle with status tracking UI
- Admin dashboards for orders, payments, coupons, and wishlist analytics
- Security baseline: CSRF protection, security headers, prepared statements
- Performance baseline: query optimization and file-based cache for key listings

## Technology Stack

- PHP 7.4+
- MySQL 5.7+
- Bootstrap 5
- PDO
- PHPUnit

## Quick Setup

```bash
git clone https://github.com/yourusername/ecommerce-script.git
cd ecommerce-script
cp .env.example .env
```

Update `.env` values for database and providers:

- Stripe: `STRIPE_PUBLISHABLE_KEY`, `STRIPE_SECRET_KEY`, `STRIPE_WEBHOOK_SECRET`
- Google OAuth: `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET`, `GOOGLE_REDIRECT_URI`

Run database scripts:

- Legacy seed: `database/shop.sql` (if needed)
- New features and upgrades: `database/migrations.sql`

## Main Routes

### Public

- `GET /`
- `GET /search`
- `GET /advanced-search`
- `GET /items/{id}`
- `GET /about-us`
- `GET /privacy-policy`
- `GET /terms-of-use`

### Auth

- `GET /login`, `POST /login`
- `GET /register`, `POST /register`
- `POST /logout`
- `GET /auth/google/redirect`, `GET /auth/google/callback`
- `GET /password/forgot`, `POST /password/forgot`
- `GET /password/reset/{token}`, `POST /password/reset/{token}`

### Commerce

- `GET /cart`
- `POST /cart/add`, `POST /cart/update`, `POST /cart/remove`, `POST /cart/clear`
- `GET /checkout`, `POST /orders`
- `GET /orders`, `GET /orders/{id}`, `POST /orders/{id}/cancel`
- `GET /orders/{id}/track`
- `POST /payments/stripe/intent`
- `POST /payments/stripe/webhook`
- `GET /wishlist`, `POST /wishlist/add`, `POST /wishlist/remove`
- `GET /coupons/validate`

### Admin

- `GET /admin`
- `GET /admin/orders`
- `GET /admin/payments`
- `GET /admin/coupons`, `POST /admin/coupons`
- `GET /admin/analytics/wishlist`

## Testing

```bash
composer test
```

Current tests include integration-style existence checks for checkout/payment/password reset stack and token/cache utilities.

## Production Recommendations

- Serve behind HTTPS
- Disable debug in production
- Configure real SMTP for password reset emails
- Rotate secrets and restrict webhook endpoints
- Add Redis cache and queue worker for high-traffic stores

## License

MIT License. See `LICENSE`.
