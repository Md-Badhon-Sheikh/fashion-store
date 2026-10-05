# YOUR BRAND — Store frontend

Customer website for the fashion store (desktop + mobile, one responsive codebase).
React 19 + Vite + React Router 7, plain CSS, no UI kit. All data is local sample
data until the Laravel API is wired in.

## Run

```bash
npm install
npm run dev       # http://localhost:5173
npm run build     # production build → dist/
npm run preview   # serve the build locally
npx oxlint src    # lint
```

Node 20.19+ (or 22.12+) is required by Vite 8.

> **Windows tip:** if `npm run build` fails with
> `ERR_PACKAGE_IMPORT_NOT_DEFINED … #module-sync-enabled`, the project lives in a path
> longer than 260 characters. Move it to a shorter folder (e.g. `C:\dev\store`) or enable
> Win32 long paths.

## Folder structure

```
src/
  main.jsx                 entry: global CSS + <App/>
  App.jsx                  providers + createBrowserRouter (all routes)
  styles/
    tokens.css             colours, type, radii, shadows, z-index, breakpoints (documented)
    global.css             reset, typography, .container, buttons, chips, badges,
                           status chips, form controls, utilities
  components/
    layout/                Layout, AnnouncementBar, Header, MobileMenu, Footer,
                           FloatingActions, MobileTabBar, routeRules.js
    ui/                    Icon, ProductCard, CategoryRow, QtyStepper, Breadcrumbs,
                           SectionHeader, Countdown, Price, WishlistButton, SearchForm
  context/
    CartContext.jsx        cart lines, coupon, delivery zone, totals (localStorage)
    WishlistContext.jsx    wishlist product ids (localStorage)
  data/                    sample data — replace with API calls (see below)
    products.js            the product catalogue + helpers
    categories.js          departments, categories, mobile-menu groups
    content.js             banners, nav, footer, delivery + coupon rules, flash sale
    orders.js              customer, addresses, sample order #WB-10482, order history
  hooks/                   useMediaQuery, useDocumentTitle
  utils/                   format.js (formatBDT, imageUrl…), storage.js (safe localStorage)
  pages/<Name>/<Name>.jsx  one folder per page, with its own <Name>.css
public/images/             product & banner photos, referenced as /images/<key>.jpg
```

Every component/page owns a CSS file next to it with BEM-ish class names
(`.product-card__price`). Breakpoints: **1200, 900, 768, 480 px** — at ≤ 900 px the
header switches to the hamburger layout, at ≤ 768 px pages use the phone layouts and the
bottom tab bar appears.

## Routes

| Path | Page |
| --- | --- |
| `/` | Home |
| `/shop`, `/shop/:category` | Shop. `:category` is a category (`panjabi`, `shirts`, `t-shirts`, `pants`, `kurti`, `three-piece`, `kids`) or a department (`men`, `women`, `kids`). Query: `?q=` search, `?filter=new` / `?filter=flash-sale` |
| `/product/:slug` | Product detail (e.g. `/product/embroidered-cotton-panjabi`) |
| `/cart` | Cart |
| `/checkout` | Checkout |
| `/order-success` | Order confirmation |
| `/login` | Login / register (`?mode=register`) |
| `/account` | My account (`?tab=wishlist` etc.) |
| `/track-order` | Track order |
| `/pages/:slug` | Info pages: `about`, `contact`, `faq`, `size-guide`, `return-policy`, `shipping`, `privacy`, `terms` |
| `*` | 404 inside the store layout |

## Where the data lives

| File | Contents |
| --- | --- |
| `data/products.js` | `products` (id, slug, sku, name, category, subcategory, price, oldPrice, image, colours, sizes + stock, fabric, tags, rating, reviews, flashSale …), `sizeCharts`, helpers `getProductBySlug`, `getProductsByCategory`, `getStock`, `getDefaultVariant`, `getCategoryLabel`, `searchProducts` … |
| `data/categories.js` | `parents`, `categories` (with counts, images, subcategories), `menuGroups`, `resolveShopSlug()` |
| `data/content.js` | announcement, `mainNav`, `heroSlides`, `promoCards`, `trustBadges`, `homeRows`, `flashSale`, `delivery` (Inside Dhaka ৳70 / Outside ৳130, free over ৳3,000), `coupons` (EID10 = 10% off, min ৳2,500), `footerColumns`, `infoPages`, `contact` |
| `data/orders.js` | `customer`, `addresses`, `sampleOrder` (#WB-10482), `orders`, `accountStats`, `getOrderByNumber()` |

Cart and wishlist are persisted in `localStorage` (`yb_cart_v1`, `yb_wishlist_v1`). On the
first visit the cart is seeded with the two items from the Cart/Checkout designs and the
wishlist with the four Account-page items. Clear site data to reset.

## Replacing sample data with the Laravel API

The backend exposes placeholder endpoints with the same shapes:
`GET /api/products`, `GET /api/products/{slug}`, `GET /api/categories`.

1. Point Vite at Laravel in development (`vite.config.js`):
   ```js
   server: { proxy: { '/api': 'http://localhost:8000' } }
   ```
2. Add a small client, e.g. `src/api/client.js`:
   ```js
   export async function api(path) {
     const res = await fetch(`/api${path}`, { headers: { Accept: 'application/json' } });
     if (!res.ok) throw new Error(`API ${res.status}`);
     return res.json();
   }
   ```
3. Swap imports page by page — keep the helpers' names so components don't change:
   - `products` / `getProductBySlug(slug)` → `api('/products')` / `api('/products/' + slug)`
     (load in a route `loader` or a `useEffect`, show a skeleton while loading).
   - `categories` → `api('/categories')`.
   - `CartContext` only needs `getProductById`, `getStock` and `getVariantSku`; back them with
     a product cache (or have the cart API return enriched lines) and move totals/coupon
     validation to the server when checkout is implemented.
4. Images: the API returns the same image keys; `imageUrl(key)` builds `/images/<key>.jpg`.
   Change that one function if images move to a CDN or storage URL.

## Data notes (design inconsistencies resolved)

- **Embroidered Cotton Panjabi** costs ৳2,450 (old price ৳2,950) everywhere, matching Shop,
  Product, Cart and Checkout. Its Home flash-sale price ৳1,890 lives in `flashSale` and is only shown
  in the Flash Sale block; the Home category row shows the regular price.
- Where artboards show different prices for the same product (Classic Navy Panjabi, Olive
  Slub, Cotton Pajama, Stretch Chino), the Shop/Product value is used.
- Header/tab-bar cart badges count **units** (seed cart = 3) and the floating widget shows
  the cart **subtotal** (৳5,150).
- Free delivery (≥ ৳3,000) does not stack with a coupon (`delivery.freeDeliveryWithCoupon`),
  which reproduces the Checkout artboard: ৳5,150 − ৳515 + ৳70 = ৳4,705.
