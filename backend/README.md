# YOUR BRAND Admin (Laravel 13)

The admin panel ("backend") of the Fashion Store: online store + POS management, built as **Blade views** from the approved static design, plus a small **placeholder JSON API** for the React store in `../frontend`.

It is a **static design export**. Every page renders realistic sample data from `app/Support/DemoData.php`. There is no database query, no real authentication and no real payment. Pure UI interactions (tabs, filters, toggles, drawers, the POS cart and so on) work through Alpine.js.

## Run it

Requirements: PHP 8.3+ and Composer. Node/Vite is **not** needed for the admin.

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan serve
```

Open <http://localhost:8000/admin>. The site root `/` redirects there. The staff login screen is at `/admin/login`.

Sessions and cache use the `file` driver, so no database or migrations are needed to browse the panel.

### Checks

```bash
php artisan route:list --path=admin   # all admin pages
php artisan view:cache                # every Blade view compiles
php artisan test                      # AdminPagesTest: every admin GET route + API returns 200
```

> **Windows, very deep folders:** if `view:cache` or the tests fail with `tempnam(): file created in the system's temporary directory`, the project path is too long for Windows' temp-file API. Point the compiled views somewhere shorter, for example `set VIEW_COMPILED_PATH=C:\temp\views` (PowerShell: `$env:VIEW_COMPILED_PATH='C:\temp\views'`), or move the project to a shorter path.

## Folder map

```
app/
  Http/Controllers/Admin/    one controller per area (Dashboard, Pos, Order, Customer, ReturnRequest,
                             Product, Category, Barcode, Review, Inventory, StockIn, Supplier, Coupon,
                             FlashSale, Banner, Accounting, Report, Notification, User, ActivityLog,
                             Setting, Auth). They only pass sample data to views.
  Http/Controllers/Api/      CatalogController (placeholder JSON API)
  Support/DemoData.php       ALL sample data + money/number formatting (৳2,14,600, en-IN grouping)
  Support/AdminNav.php       sidebar + phone tab bar definition (groups, links, icons, badges)
  Support/Status.php         status word → chip tone mapping (pending → amber, shipped → cyan, …)
public/
  css/admin.css              the single hand-written stylesheet (tokens, layout, components, page sections)
  images/<key>.jpg           product / category photos (keys match the design's IMG.<key>)
resources/views/
  admin/layouts/             app (sidebar + topbar shell), auth (login), print (invoice/receipt/slip)
  admin/partials/            sidebar, topbar, tabbar (phone bottom bar), bar-chart
  admin/<area>/*.blade.php   one folder per page area
  components/admin/          <x-admin.*> Blade components (see below)
routes/
  web.php                    admin routes (prefix /admin, names admin.*)
  api.php                    /api/products, /api/products/{slug}, /api/categories
tests/Feature/AdminPagesTest.php
```

## Routes

| Name | URL | Design |
|---|---|---|
| `admin.login` | `/admin/login` | AdminLogin |
| `admin.dashboard` | `/admin` | Dashboard, M-AdminDashboard |
| `admin.pos` | `/admin/pos` | POS, T-POS |
| `admin.orders.index` | `/admin/orders` | Orders, M-AdminOrders |
| `admin.orders.show` | `/admin/orders/{order}` (e.g. `WB-10482`) | OrderDetail |
| `admin.orders.invoice` | `/admin/orders/{order}/invoice` | Invoice |
| `admin.orders.receipt` | `/admin/orders/{order}/receipt` | Invoice (POS receipt) |
| `admin.orders.packing-slip` | `/admin/orders/{order}/packing-slip` | Invoice (packing slip) |
| `admin.customers` | `/admin/customers` | Customers |
| `admin.returns` | `/admin/returns` | Returns |
| `admin.reviews` | `/admin/reviews` | Reviews |
| `admin.products.index` | `/admin/products` | Products |
| `admin.products.create` | `/admin/products/create` | ProductForm |
| `admin.products.edit` | `/admin/products/{product}/edit` (id, slug or SKU) | ProductForm |
| `admin.categories` | `/admin/categories` | Categories |
| `admin.barcodes` | `/admin/barcodes` | Barcodes |
| `admin.inventory` | `/admin/inventory` | Inventory |
| `admin.stock-in` | `/admin/stock-in` | StockIn |
| `admin.suppliers` | `/admin/suppliers` | Suppliers |
| `admin.coupons` | `/admin/coupons` | Coupons |
| `admin.flash-sales` | `/admin/flash-sales` | FlashSale |
| `admin.banners` | `/admin/banners` | Banners |
| `admin.accounting` | `/admin/accounting` | Accounting |
| `admin.reports` | `/admin/reports` | Reports |
| `admin.notifications` | `/admin/notifications` | Notifications |
| `admin.users` | `/admin/users` | Users |
| `admin.activity-log` | `/admin/activity-log` | ActivityLog |
| `admin.settings` | `/admin/settings` | Settings |

API: `GET /api/products`, `GET /api/products/{slug}`, `GET /api/categories`. Each returns `{ "data": … }` and exposes public fields only (no purchase prices).

## Building a page

```blade
@extends('admin.layouts.app')
@section('title', 'Orders')          {{-- browser tab + phone topbar title --}}

@section('content')
    <x-admin.page-header title="Orders" subtitle="…">
        <x-slot:actions><button type="button" class="btn btn--brand">Manual order</button></x-slot:actions>
    </x-admin.page-header>

    <x-admin.card title="Recent orders" :link="route('admin.orders.index')" link-label="View all →">
        <div class="table-wrap"><table class="table" style="--table-min: 620px">…</table></div>
    </x-admin.card>
@endsection
```

Components (`resources/views/components/admin/`):

| Component | Props |
|---|---|
| `<x-admin.page-header>` | `title`, `subtitle`, `phone-title`; slots `actions`, `breadcrumb`, `meta` |
| `<x-admin.card>` | `title`, `subtitle`, `link`, `link-label`, `as`, `heading-level`, `flush`; slot `action` |
| `<x-admin.kpi>` | `label`, `value`, `sub`, `tone` (default/warn/pending/danger/success). Each of label/value/sub can be an array keyed by Alpine `range` |
| `<x-admin.status-chip>` | `status` (any status word), `label`, `size` (sm/lg) |
| `<x-admin.toggle>` | `name`, `checked`, `label`, `show-label`, `hint`, `disabled`, `model` |
| `<x-admin.tabs>` | `tabs` (key → label or `[label, count]`), `model`, `label`, `active`, `variant` (line/pill) |
| `<x-admin.empty-state>` | `title`, `message`, `icon` |
| `<x-admin.icon>` | `name`, `size`, `stroke`, `label` |
| `<x-admin.money>` | `amount` |

Page-specific CSS goes at the end of `public/css/admin.css`, below `/* ===== PAGE STYLES (append below) ===== */`, in a commented block per page (`/* === Orders === */`).

## Sample data → real data

All sample data lives in **`app/Support/DemoData.php`**: `products()`, `categories()`, `customers()`, `orders()`, `kpis()`, `salesLast14Days()`, `lowStockAlerts()`, `topProducts()`, `navBadges()` and the other methods listed there. Views only receive plain arrays from controllers.

To switch a page to the database:

1. Create the model and migration (for example `php artisan make:model Order -m`) and move the sample rows into a seeder. `DemoData` is a ready-made seed source.
2. In the controller, replace `DemoData::orders()` with an Eloquent query. Either return the same array shape (`->map(fn ($o) => [...])`, or an API Resource's `toArray()`), or update the view to read model attributes.
3. Swap route parameters to route-model binding (`/orders/{order}` → `Order $order`) and remove `DemoData::findOrder()`.
4. Keep `DemoData::money()` (or move it to a helper) for ৳ formatting with en-IN grouping.
5. Add `->middleware('auth')` to the admin route group once real login is wired. The login view currently posts nowhere.
6. Point the React store at `/api/*` and replace `CatalogController`'s DemoData calls with queries or API Resources.
