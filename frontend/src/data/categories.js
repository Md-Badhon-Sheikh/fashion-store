/**
 * Category tree (sample data).
 * Later: GET /api/categories returns the same shape.
 *
 * - `parents` are the top-level departments (Men / Women / Kids).
 * - `categories` are the shoppable categories used in /shop/:category.
 *   `count` is the catalogue size shown in the UI (the local sample catalogue
 *   in products.js only contains a subset of these products).
 */

export const parents = [
  { slug: 'men', name: 'Men', count: 260 },
  { slug: 'women', name: 'Women', count: 144 },
  { slug: 'kids', name: 'Kids', count: 37 },
];

export const categories = [
  {
    slug: 'panjabi',
    name: 'Panjabi',
    parent: 'men',
    count: 86,
    image: 'cat_panjabi',
    tone: '#DCD3C6',
    title: "Men's Panjabi",
    description:
      'Cotton, linen and festive panjabi for Eid, weddings and everyday wear. Sizes S to XXL, free size exchange within 7 days.',
    subcategories: [
      { slug: 'embroidered', name: 'Embroidered', count: 24 },
      { slug: 'printed', name: 'Printed', count: 18 },
      { slug: 'linen', name: 'Linen', count: 12 },
      { slug: 'festive', name: 'Festive & jacquard', count: 9 },
      { slug: 'kabli', name: 'Kabli sets', count: 7 },
      { slug: 'short-kurta', name: 'Short kurta', count: 16 },
    ],
  },
  {
    slug: 'shirts',
    name: 'Shirts',
    parent: 'men',
    count: 64,
    image: 'cat_shirt',
    tone: '#CFD8DF',
    title: "Men's Shirts",
    description: 'Formal Oxford, twill and casual printed shirts in breathable cotton. Slim and regular fits, M to XXL.',
    subcategories: [
      { slug: 'formal', name: 'Formal', count: 28 },
      { slug: 'casual', name: 'Casual', count: 36 },
    ],
  },
  {
    slug: 't-shirts',
    name: 'T-Shirts',
    menuName: 'T-shirts & polo',
    parent: 'men',
    count: 72,
    image: 'cat_tshirt',
    tone: '#D8DDCF',
    title: "Men's T-Shirts & Polo",
    description: 'Basic crew necks and premium polos in soft combed cotton. Everyday essentials, S to XXL.',
    subcategories: [
      { slug: 'basic', name: 'Basic tees', count: 44 },
      { slug: 'polo', name: 'Polo', count: 28 },
    ],
  },
  {
    slug: 'pants',
    name: 'Pants',
    menuName: 'Pants & chino',
    parent: 'men',
    count: 38,
    image: 'cat_pant',
    tone: '#DDD8CC',
    title: "Men's Pants & Pajama",
    description: 'Stretch chinos and cotton pajamas to pair with your panjabi. Waist 30 to 36.',
    subcategories: [
      { slug: 'chino', name: 'Chino', count: 16 },
      { slug: 'pajama', name: 'Pajama', count: 22 },
    ],
  },
  {
    slug: 'kurti',
    name: 'Kurti',
    parent: 'women',
    count: 58,
    image: 'cat_kurti',
    tone: '#E6D3D3',
    title: "Women's Kurti",
    description: 'Block print, linen and embroidered kurti for work, Eid and everyday. Sizes S to XL.',
    subcategories: [
      { slug: 'block-print', name: 'Block print', count: 21 },
      { slug: 'linen', name: 'Linen', count: 14 },
      { slug: 'embroidered', name: 'Embroidered', count: 23 },
    ],
  },
  {
    slug: 'three-piece',
    name: 'Three-Piece',
    parent: 'women',
    count: 41,
    image: 'cat_threepiece',
    tone: '#DDD5E3',
    title: "Women's Three-Piece",
    description: 'Printed lawn, cotton and embroidered georgette three-piece sets, ready to wear.',
    subcategories: [
      { slug: 'lawn', name: 'Lawn', count: 17 },
      { slug: 'cotton', name: 'Cotton', count: 13 },
      { slug: 'georgette', name: 'Georgette', count: 11 },
    ],
  },
  {
    slug: 'kids',
    name: 'Kids',
    parent: 'kids',
    count: 37,
    image: 'cat_kids',
    tone: '#E5DECC',
    title: 'Kids',
    description: 'Festive panjabi sets, frocks and tees for kids aged 2 to 8 years.',
    subcategories: [
      { slug: 'boys-panjabi', name: 'Boys panjabi', count: 21 },
      { slug: 'girls-frocks', name: 'Girls frocks', count: 9 },
      { slug: 'kids-tshirts', name: 'Kids T-Shirts', count: 7 },
    ],
  },
];

/** Order used on Home "Shop by category" (desktop shows the first 6, mobile all 7). */
export const homeCategoryOrder = ['panjabi', 'shirts', 't-shirts', 'kurti', 'three-piece', 'kids', 'pants'];

/**
 * Mobile menu (M-Menu) groups. Children link to /shop/<to>.
 * Children without a matching category slug fall back to the parent listing.
 */
export const menuGroups = [
  {
    slug: 'men',
    label: 'Men',
    count: 260,
    children: [
      { label: 'Panjabi', count: 86, to: '/shop/panjabi' },
      { label: 'Shirts', count: 64, to: '/shop/shirts' },
      { label: 'T-Shirts', count: 72, to: '/shop/t-shirts' },
      { label: 'Pants', count: 38, to: '/shop/pants' },
    ],
  },
  {
    slug: 'women',
    label: 'Women',
    count: 144,
    children: [
      { label: 'Kurti', count: 58, to: '/shop/kurti' },
      { label: 'Three-Piece', count: 41, to: '/shop/three-piece' },
      { label: 'Tops', count: 26, to: '/shop/women' },
      { label: 'Orna & scarves', count: 19, to: '/shop/women' },
    ],
  },
  {
    slug: 'kids',
    label: 'Kids',
    count: 37,
    children: [
      { label: 'Boys panjabi', count: 21, to: '/shop/kids' },
      { label: 'Girls frocks', count: 9, to: '/shop/kids' },
      { label: 'Kids T-Shirts', count: 7, to: '/shop/kids' },
    ],
  },
];

export function getCategory(slug) {
  return categories.find((c) => c.slug === slug) || null;
}

export function getParent(slug) {
  return parents.find((p) => p.slug === slug) || null;
}

/** Categories that belong to a department ("men" → panjabi, shirts, t-shirts, pants). */
export function getCategoriesByParent(parentSlug) {
  return categories.filter((c) => c.parent === parentSlug);
}

/**
 * Resolve a /shop/:category param. Returns
 *   { type: 'category', category, parent } | { type: 'parent', parent } | null
 * "kids" is both a department and a category; it resolves as a category.
 */
export function resolveShopSlug(slug) {
  const category = getCategory(slug);
  if (category) return { type: 'category', category, parent: getParent(category.parent) };
  const parent = getParent(slug);
  if (parent) return { type: 'parent', parent };
  return null;
}
