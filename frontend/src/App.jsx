import { createBrowserRouter, RouterProvider } from 'react-router-dom';
import Layout from './components/layout/Layout.jsx';
import { CartProvider } from './context/CartContext.jsx';
import { WishlistProvider } from './context/WishlistContext.jsx';
import Home from './pages/Home/Home.jsx';
import NotFound from './pages/NotFound/NotFound.jsx';

// Home and 404 ship in the main bundle; every other page is code-split and
// loaded on first visit.
const page = (load) => () => load().then((m) => ({ Component: m.default }));

/**
 * Routes
 *   /                   Home
 *   /shop               Shop (all products; ?q= search, ?filter=new|flash-sale)
 *   /shop/:category     Shop filtered by category (panjabi…) or department (men|women|kids)
 *   /product/:slug      Product detail
 *   /cart               Cart
 *   /checkout           Checkout
 *   /order-success      Order confirmation
 *   /login              Login / register (?mode=register)
 *   /account            My account (?tab=orders|wishlist|addresses|profile…)
 *   /track-order        Track order
 *   /pages/:slug        Info pages: about, contact, faq, size-guide, return-policy, privacy, terms, shipping
 *   *                   404 inside the store layout
 */
const router = createBrowserRouter([
  {
    path: '/',
    element: <Layout />,
    children: [
      { index: true, element: <Home /> },
      { path: 'shop', lazy: page(() => import('./pages/Shop/Shop.jsx')) },
      { path: 'shop/:category', lazy: page(() => import('./pages/Shop/Shop.jsx')) },
      { path: 'product/:slug', lazy: page(() => import('./pages/Product/Product.jsx')) },
      { path: 'cart', lazy: page(() => import('./pages/Cart/Cart.jsx')) },
      { path: 'checkout', lazy: page(() => import('./pages/Checkout/Checkout.jsx')) },
      { path: 'order-success', lazy: page(() => import('./pages/OrderSuccess/OrderSuccess.jsx')) },
      { path: 'login', lazy: page(() => import('./pages/Login/Login.jsx')) },
      { path: 'account', lazy: page(() => import('./pages/Account/Account.jsx')) },
      { path: 'track-order', lazy: page(() => import('./pages/TrackOrder/TrackOrder.jsx')) },
      { path: 'pages/:slug', lazy: page(() => import('./pages/InfoPage/InfoPage.jsx')) },
      { path: '*', element: <NotFound /> },
    ],
  },
]);

export default function App() {
  return (
    <WishlistProvider>
      <CartProvider>
        <RouterProvider router={router} />
      </CartProvider>
    </WishlistProvider>
  );
}
