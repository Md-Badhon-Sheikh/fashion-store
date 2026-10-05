# YOUR BRAND — Fashion Store (static design export)

Single-vendor fashion e-commerce + in-store POS, exported from the approved design.

| Folder | What | Stack |
|---|---|---|
| `frontend/` | Customer website (desktop + mobile, one responsive codebase) | React 19, Vite, React Router 7, plain CSS |
| `backend/` | Admin panel (dashboard, POS, orders, catalog, inventory, marketing, finance, system) + placeholder JSON API | Laravel 13, Blade, Alpine.js, plain CSS |

This is a **static export**: every page uses realistic sample data from local files, and all
UI interactions work (filters, cart, checkout validation, POS cart, tabs, toggles…), but nothing
is saved to a database and there is no real login, payment, SMS or courier integration yet.

## Run it

Frontend (needs Node 20+):

```bash
cd frontend
npm install
npm run dev        # http://localhost:5173
```

Admin panel (needs PHP 8.3+ and Composer):

```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
php artisan serve  # http://localhost:8000/admin
```

## Pages

Website: Home, Shop / category (filters, sort, search), Product, Cart, Checkout, Order confirmation,
Login / Register (OTP), My account, Track order, Help & policy pages (FAQ, size guide, returns…), 404.

Admin: Login + 2FA, Dashboard, POS (desktop + tablet), Orders, Order details, Invoice / POS receipt /
packing slip (print), Customers, Returns & exchanges, Reviews, Products, Add / edit product (variant
generator), Categories & brands, Barcode labels, Stock overview & adjustments, Stock-in / purchases,
Suppliers, Coupons, Flash sale, Banners & pages, Accounting, Reports, SMS & email, Users & roles,
Activity log, Settings.

## Next steps for developers

1. Create the database schema and Eloquent models (products, variants with size/colour/SKU/barcode,
   stock movements, orders, customers, coupons, …) and replace `backend/app/Support/DemoData.php`
   and `backend/app/Support/Demo/*Data.php` with real queries — controllers already pass data to views.
2. Point the React app at the API (`/api/products`, `/api/products/{slug}`, `/api/categories` exist as
   placeholders) instead of `frontend/src/data/*.js`. See `frontend/README.md`.
3. Add authentication + role permissions, SMS/email gateway, then payment gateway and courier APIs (phase 2).

Photos are free-licence Unsplash images used as placeholders — replace them with your own product photos
(`frontend/public/images/`, `backend/public/images/`).
