/**
 * Store content & settings (sample data): copy, navigation, banners,
 * delivery and coupon rules. Later these come from the admin "Banners &
 * pages", "Coupons", "Flash sale" and "Settings" screens via the API.
 */

export const brand = {
  name: 'YOUR BRAND',
  year: 2026,
};

export const contact = {
  address: '[Shop address], Dhaka',
  phone: '[PHONE]',
  email: '[EMAIL]',
  hours: '10am – 10pm, every day',
  // Replace with real links, e.g. https://wa.me/8801XXXXXXXXX and https://m.me/<page>
  whatsappUrl: '#whatsapp',
  messengerUrl: '#messenger',
  phoneHref: 'tel:+880',
};

export const announcement = {
  desktop: 'Free delivery on orders over ৳3,000 · Cash on Delivery all over Bangladesh · 7-day easy exchange',
  mobile: 'Free delivery over ৳3,000 · Cash on Delivery all over Bangladesh',
};

/** Header navigation (desktop) — also used in the footer "Shop" column. */
export const mainNav = [
  { label: 'Men', to: '/shop/men' },
  { label: 'Women', to: '/shop/women' },
  { label: 'Kids', to: '/shop/kids' },
  { label: 'New Arrivals', to: '/shop?filter=new' },
  { label: 'Flash Sale', to: '/shop?filter=flash-sale', sale: true },
];

/** Mobile menu extra links under the category groups */
export const menuHighlights = [
  { label: 'New Arrivals', to: '/shop?filter=new', badge: '48 new', tone: 'success' },
  { label: 'Flash Sale', to: '/shop?filter=flash-sale', badge: 'Up to 30% off', tone: 'sale' },
];

/** Home hero. Desktop shows slide 0 copy + three photo tiles; mobile is a slider over all slides. */
export const heroSlides = [
  {
    id: 'eid',
    eyebrow: 'Eid Collection 2026',
    title: 'Made for the season. Priced for every day.',
    text: 'Panjabi, kurti and three-piece in pure cotton and linen.',
    textDesktop: 'Panjabi, shirts, kurti and three-piece sets in pure cotton and linen. Sizes S to XXL.',
    cta: { label: 'Shop the collection', to: '/shop' },
    secondaryCta: { label: 'View flash sale', to: '#flash' },
    image: 'hero1',
    alt: 'Model wearing an Eid collection panjabi',
    tone: '#D9CFC1',
  },
  {
    id: 'linen',
    eyebrow: 'New in · Men',
    title: 'Linen panjabi, cut for the heat.',
    text: 'Breathable linen blends in off-white, navy and olive. S to XXL.',
    cta: { label: 'Shop panjabi', to: '/shop/panjabi' },
    image: 'hero2',
    alt: 'Model wearing an Eid collection outfit',
    tone: '#C8D3CC',
  },
  {
    id: 'three-piece',
    eyebrow: 'Women · Three-Piece',
    title: 'Lawn and georgette sets, ready to wear.',
    text: 'Printed lawn and embroidered georgette from ৳2,290.',
    cta: { label: 'Shop three-piece', to: '/shop/three-piece' },
    image: 'hero3',
    alt: 'Close-up of fabric and embroidery detail',
    tone: '#E8E1D6',
  },
];

/** Two promo cards under New arrivals (mobile shows only the first, as a compact card). */
export const promoCards = [
  {
    id: 'eid10',
    eyebrow: 'Use code EID10',
    title: '10% off orders over ৳2,500',
    cta: { label: 'Shop now', to: '/shop' },
    image: 'promo1',
    alt: 'EID10 offer',
    tone: '#D5DED7',
  },
  {
    id: 'size-guide',
    eyebrow: 'Not sure about your size?',
    title: 'Check the size guide, exchange in 7 days',
    cta: { label: 'Open size guide', to: '/pages/size-guide' },
    image: 'promo2',
    alt: '',
    tone: '#E3E6EA',
  },
];

/** Trust badges (icon names from components/ui/Icon.jsx) */
export const trustBadges = [
  { icon: 'truck', title: 'Delivery all over Bangladesh', titleShort: 'Delivery all over BD', text: 'Inside Dhaka 1–2 days' },
  { icon: 'cash', title: 'Cash on Delivery', text: 'Pay when you receive' },
  { icon: 'exchange', title: '7-day easy exchange', titleShort: '7-day exchange', text: 'Size or colour swap' },
  { icon: 'shield', title: 'Secure checkout', text: 'bKash, Nagad, cards soon', textShort: 'bKash, Nagad soon' },
];

/** Category rows on Home (CategoryRow). Product ids from products.js */
export const homeRows = [
  {
    id: 'panjabi',
    title: 'Panjabi',
    subtitle: '86 products · Cotton, linen & embroidered panjabi',
    to: '/shop/panjabi',
    productIds: ['p01', 'p05', 'p06', 'p02', 'p15', 'p16'],
  },
  {
    id: 'shirts',
    title: 'Shirts & T-Shirts',
    subtitle: '136 products · Formal, casual, polo & basic tees',
    to: '/shop/men',
    productIds: ['s01', 's02', 's04', 't01', 't02', 't03'],
  },
  {
    id: 'women',
    title: 'Kurti & Three-Piece',
    subtitle: '99 products · Block print, linen, lawn & georgette',
    to: '/shop/women',
    productIds: ['k01', 'k02', 'k03', 'w01', 'w02', 'w03', 'k04'],
  },
  {
    id: 'kids',
    title: 'Kids & Pants',
    subtitle: '62 products · Kids festive wear, chinos & pajamas',
    to: '/shop/kids',
    productIds: ['c01', 'c02', 'n01', 'n02'],
  },
];

/** Home "New arrivals" block: product order + filter tabs */
export const newArrivalIds = ['p02', 's03', 'k01', 'w02', 't01', 'c01', 'n01', 'k02'];

export const newArrivalTabs = [
  { id: 'all', label: 'All' },
  { id: 'men', label: 'Men' },
  { id: 'women', label: 'Women' },
  { id: 'kids', label: 'Kids' },
  { id: 'under-1500', label: 'Under ৳1,500', maxPrice: 1500, mobileOnly: true },
];

/** Flash sale. endsAt is relative to page load for the demo (08:42:15 left). */
export const flashSale = {
  title: 'Flash Sale',
  endsAt: Date.now() + (8 * 3600 + 42 * 60 + 15) * 1000,
  productIds: ['p01', 's01', 'w01', 't02'],
  maxDiscountLabel: 'Up to 30% off',
};

export const newsletter = {
  title: 'Get new drops and offers first',
  text: 'Subscribe by email. No spam, unsubscribe any time.',
  placeholder: 'you@email.com',
  button: 'Subscribe',
  success: 'Thanks! You are on the list.',
};

/** Delivery & checkout rules */
export const delivery = {
  zones: [
    { id: 'inside', name: 'Inside Dhaka', fee: 70, eta: '1–2 working days', etaShort: '1–2 days' },
    { id: 'outside', name: 'Outside Dhaka', fee: 130, eta: '3–5 working days', etaShort: '3–5 days' },
  ],
  defaultZone: 'inside',
  freeDeliveryThreshold: 3000,
  // The checkout artboard charges ৳70 on a ৳5,150 order that uses EID10, so
  // free delivery does not stack with a coupon. Flip to true to allow it.
  freeDeliveryWithCoupon: false,
  courier: 'Steadfast Courier',
};

/** Coupons accepted by the cart (codes are matched case-insensitively). */
export const coupons = [
  {
    code: 'EID10',
    type: 'percent',
    value: 10,
    minSubtotal: 2500,
    label: '10% off',
    description: '10% off orders over ৳2,500',
  },
  {
    code: 'NEW200',
    type: 'flat',
    value: 200,
    minSubtotal: 0,
    label: '৳200 off',
    description: '৳200 off your first app order',
    appOnly: true,
  },
];

export const paymentMethods = ['COD', 'bKash', 'Nagad', 'Visa / Master'];

/** Info pages served by /pages/:slug (InfoPage owns the body copy). */
export const infoPages = [
  { slug: 'about', title: 'About us' },
  { slug: 'contact', title: 'Contact us' },
  { slug: 'faq', title: 'FAQ' },
  { slug: 'size-guide', title: 'Size guide' },
  { slug: 'return-policy', title: 'Return & exchange policy' },
  { slug: 'shipping', title: 'Shipping & delivery' },
  { slug: 'privacy', title: 'Privacy policy' },
  { slug: 'terms', title: 'Terms & conditions' },
];

export const footerColumns = [
  {
    id: 'shop',
    title: 'Shop',
    links: [
      { label: 'Men', to: '/shop/men' },
      { label: 'Women', to: '/shop/women' },
      { label: 'Kids', to: '/shop/kids' },
      { label: 'New arrivals', to: '/shop?filter=new' },
      { label: 'Flash sale', to: '/shop?filter=flash-sale' },
    ],
  },
  {
    id: 'help',
    title: 'Help',
    links: [
      { label: 'Track your order', to: '/track-order' },
      { label: 'Size guide', to: '/pages/size-guide' },
      { label: 'Return & exchange policy', to: '/pages/return-policy' },
      { label: 'FAQ', to: '/pages/faq' },
      { label: 'Contact us', to: '/pages/contact' },
    ],
  },
  {
    id: 'company',
    title: 'Company',
    links: [
      { label: 'About us', to: '/pages/about' },
      { label: 'Privacy policy', to: '/pages/privacy' },
      { label: 'Terms & conditions', to: '/pages/terms' },
    ],
  },
];
