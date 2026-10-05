/**
 * Account & order-tracking extras (sample data) used by the Account and
 * TrackOrder pages. `orders.js` holds the order summaries; this file adds the
 * per-order line items, progress notes and account notifications that the
 * Account (desktop + M-Account) and TrackOrder designs show.
 *
 * Later: GET /api/orders/{number} (items, note) and GET /api/me/notifications.
 */

/**
 * Line items per order number. Prices come from the product catalogue; the
 * order total in orders.js is the amount the customer actually paid.
 */
export const orderItems = {
  'WB-10482': [
    { productId: 'p01', size: 'M', colour: 'Off-white', qty: 1 },
    { productId: 'k01', size: 'L', colour: 'Maroon', qty: 2 },
  ],
  'WB-10391': [{ productId: 'p01', size: 'L', colour: 'Navy', qty: 1 }],
  'WB-10244': [
    { productId: 's01', size: 'L', colour: 'Sky blue', qty: 1 },
    { productId: 't01', size: 'M', colour: 'Black', qty: 1 },
  ],
  'WB-10118': [{ productId: 'k02', size: 'M', colour: 'Sand', qty: 1 }],
  'WB-09980': [
    { productId: 'p02', size: 'XL', colour: 'Off-white', qty: 1 },
    { productId: 't02', size: 'L', colour: 'Grey melange', qty: 1 },
  ],
  'WB-09812': [{ productId: 'c01', size: '4Y', colour: 'Dusty blue', qty: 1 }],
};

/** One-line progress note per order (M-Account order cards). */
export const orderNotes = {
  'WB-10482': 'With Steadfast Courier. Expected delivery Tue, 6 Oct · Inside Dhaka.',
  'WB-10391': 'Delivered 30 Sep. Exchange window open until 7 Oct.',
  'WB-10244': 'Delivered 18 Sep. Tell us how the fit was — write a review.',
  'WB-10118': 'Delivered 4 Sep.',
  'WB-09980': 'Returned 26 Aug. Refund sent to your bKash number.',
  'WB-09812': 'Cancelled at your request on 9 Aug.',
};

/** Simplified customer-facing steps for the order-card progress bar (M-Account). */
export const cardSteps = ['Placed', 'Confirmed', 'Shipped', 'Delivered'];

/** status → { reached step count (1-4), progress % } */
export const cardProgress = {
  pending: { reached: 1, pct: 12 },
  confirmed: { reached: 2, pct: 37 },
  processing: { reached: 2, pct: 50 },
  shipped: { reached: 3, pct: 75 },
  delivered: { reached: 4, pct: 100 },
};

/** Account notifications (bell badge = unread count). */
export const notifications = [
  {
    id: 'n1',
    unread: true,
    title: 'Your order #WB-10482 has been shipped',
    text: 'Steadfast Courier will deliver it by Tue, 6 Oct. Pay ৳4,705 on delivery.',
    time: '5 Oct, 9:10 AM',
    to: '/track-order?order=WB-10482',
  },
  {
    id: 'n2',
    unread: true,
    title: 'Price drop on your wishlist',
    text: 'Slim Fit Oxford Shirt is now ৳1,190 (was ৳1,650).',
    time: '3 Oct, 6:00 PM',
    to: '/product/slim-fit-oxford-shirt',
  },
  {
    id: 'n3',
    unread: false,
    title: 'Order #WB-10391 delivered',
    text: 'Wrong size? You can request an exchange until 7 Oct.',
    time: '30 Sep, 2:45 PM',
    to: '/track-order?order=WB-10391',
  },
];

/** Default notification preferences (Account → Notifications). */
export const notificationPrefs = [
  { id: 'sms-orders', label: 'Order updates by SMS', note: '(always on)', checked: true, locked: true },
  { id: 'email-orders', label: 'Order updates by email', checked: true },
  { id: 'wishlist-alerts', label: 'Wishlist price drops and back-in-stock alerts', checked: true },
  { id: 'offers', label: 'Offers and new arrivals (SMS / email)', checked: false },
];
