/**
 * Orders & customer (sample data) for OrderSuccess, Account and TrackOrder.
 * Later: GET /api/orders, GET /api/orders/{number}, GET /api/me.
 *
 * Status values (lower-case) map 1:1 to the `.status-chip--<status>` classes
 * in global.css: pending, confirmed, processing, shipped, delivered,
 * cancelled, returned.
 */

export const customer = {
  name: '[Customer name]',
  phone: '017•••••678',
  email: '[EMAIL]',
  memberSince: 'Jan 2025',
  avatar: null,
};

export const addresses = [
  {
    id: 'home',
    label: 'Home',
    isDefault: true,
    name: '[Customer name]',
    phone: '017•••••678',
    lines: ['House 12, Road 5, Block C, Mirpur 10', 'Dhaka 1216, Bangladesh'],
    zone: 'inside',
  },
  {
    id: 'office',
    label: 'Office',
    isDefault: false,
    name: '[Customer name]',
    phone: '018•••••214',
    lines: ['Level 6, [Office building], Gulshan 1', 'Dhaka 1212'],
    zone: 'inside',
  },
];

/** Order status timeline order (TrackOrder stepper) */
export const orderSteps = ['pending', 'confirmed', 'processing', 'shipped', 'delivered'];

export const statusLabels = {
  pending: 'Pending',
  confirmed: 'Confirmed',
  processing: 'Processing',
  shipped: 'Shipped',
  delivered: 'Delivered',
  cancelled: 'Cancelled',
  returned: 'Returned',
};

/** The order used by OrderSuccess and TrackOrder (and "On the way" in Account). */
export const sampleOrder = {
  number: 'WB-10482',
  placedAt: 'Sun, 4 Oct 2026 · 10:42 AM',
  placedDate: '4 Oct 2026',
  expectedDelivery: 'Tue, 6 Oct 2026',
  expectedWindow: 'Mon 5 – Tue 6 Oct 2026',
  status: 'shipped',
  statusOnPlacement: 'Pending confirmation',
  payment: { method: 'cod', label: 'Cash on Delivery', short: 'COD' },
  items: [
    {
      productId: 'p01',
      name: 'Embroidered Cotton Panjabi',
      image: 'p_panjabi_offwhite',
      tone: '#E4DCCF',
      size: 'M',
      colour: 'Off-white',
      unitPrice: 2450,
      qty: 1,
    },
    {
      productId: 'k01',
      name: 'Block Print Cotton Kurti',
      image: 'p_kurti_block',
      tone: '#E8D8D6',
      size: 'L',
      colour: 'Maroon',
      unitPrice: 1350,
      qty: 2,
    },
  ],
  itemCount: 3,
  subtotal: 5150,
  coupon: 'EID10',
  discount: 515,
  deliveryZone: 'inside',
  deliveryZoneName: 'Inside Dhaka',
  deliveryFee: 70,
  total: 4705,
  address: addresses[0],
  courier: {
    name: 'Steadfast Courier',
    short: 'SF',
    consignmentId: 'SF-88412276',
    trackingCode: '7XK2-MRP-9041',
    rider: 'Assigned on delivery day',
    trackingUrl: '#courier-tracking',
  },
  timeline: [
    { status: 'pending', time: '4 Oct, 10:42 AM', note: 'Order placed' },
    { status: 'confirmed', time: '4 Oct, 11:15 AM', note: 'Confirmed by phone call' },
    { status: 'processing', time: '4 Oct, 3:30 PM', note: 'Packed at warehouse' },
    { status: 'shipped', time: '5 Oct, 9:10 AM', note: 'With Steadfast Courier' },
    { status: 'delivered', time: 'Expected Tue, 6 Oct', note: 'Pay ৳4,705 on delivery' },
  ],
  history: [
    { text: 'Arrived at Steadfast Mirpur hub', meta: '5 Oct 2026, 6:20 PM · Mirpur, Dhaka' },
    { text: 'Handed over to Steadfast Courier', meta: '5 Oct 2026, 9:10 AM · Consignment SF-88412276' },
    { text: 'Packed and ready to ship', meta: '4 Oct 2026, 3:30 PM · YOUR BRAND warehouse' },
    { text: 'Order confirmed by phone call', meta: '4 Oct 2026, 11:15 AM · Sales Staff 01' },
    { text: 'Order placed · Cash on Delivery', meta: '4 Oct 2026, 10:42 AM · Website' },
  ],
  returnWindowEnds: 'Tue, 13 Oct 2026',
};

/** Order history (Account → My orders). Newest first. */
export const orders = [
  { number: 'WB-10482', date: '4 Oct 2026', items: 3, total: 4705, payment: 'COD', status: 'shipped' },
  { number: 'WB-10391', date: '28 Sep 2026', items: 1, total: 2520, payment: 'COD', status: 'delivered' },
  { number: 'WB-10244', date: '16 Sep 2026', items: 2, total: 3240, payment: 'bKash', status: 'delivered' },
  { number: 'WB-10118', date: '2 Sep 2026', items: 1, total: 1460, payment: 'COD', status: 'delivered' },
  { number: 'WB-09980', date: '21 Aug 2026', items: 2, total: 2180, payment: 'COD', status: 'returned' },
  { number: 'WB-09812', date: '9 Aug 2026', items: 1, total: 950, payment: 'COD', status: 'cancelled' },
];

export const accountStats = [
  { label: 'Total orders', value: '14', note: 'Since Jan 2025' },
  { label: 'On the way', value: '1', note: '#WB-10482 · shipped' },
  { label: 'Wishlist', value: '6', note: '2 items on sale now' },
  { label: 'Total spent', value: '৳38,450', note: '12 delivered orders' },
];

/** Delivered items waiting for a review (Account → Reviews) */
export const pendingReviews = ['s01', 't01'];

export function getOrderByNumber(number) {
  const clean = String(number).replace(/^#/, '').trim().toUpperCase();
  if (clean === sampleOrder.number) return sampleOrder;
  return orders.find((o) => o.number === clean) || null;
}
