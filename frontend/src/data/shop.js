/**
 * Shop listing + product page data (sample data) that the shared catalogue
 * (products.js / categories.js) does not carry yet: brands, sort options and
 * listing copy. Later: brand comes from the product API, copy from the admin
 * "Banners & pages" screen.
 */

/** Products per page on /shop (desktop pagination, phone "Load more" step). */
export const SHOP_PAGE_SIZE = 12;

/** Brands shown in the Shop "Brand" filter and the product meta line. */
export const brands = [
  { slug: 'your-brand', name: 'YOUR BRAND' },
  { slug: 'signature', name: 'YOUR BRAND Signature' },
];

/** Premium line — everything else is the core brand. */
const SIGNATURE_IDS = ['p08', 'p10', 'p14', 'w02', 'k04'];

export function getBrand(product) {
  return SIGNATURE_IDS.includes(product.id) ? brands[1] : brands[0];
}

/** Sort select options (first one is the default). */
export const sortOptions = [
  { value: 'newest', label: 'Newest' },
  { value: 'price-asc', label: 'Price: low to high' },
  { value: 'price-desc', label: 'Price: high to low' },
  { value: 'best-selling', label: 'Best selling' },
];

export const DEFAULT_SORT = sortOptions[0].value;

/** Display order for size facets (adult letters, kids ages, waist). */
export const sizeOrder = ['XS', 'S', 'M', 'L', 'XL', 'XXL', '3XL', '2Y', '4Y', '6Y', '8Y', '30', '32', '34', '36'];

/** Headings + intro copy for listings that are not a single category. */
export const listingCopy = {
  all: {
    title: 'All products',
    short: 'Shop',
    description: 'Panjabi, shirts, kurti, three-piece and kids wear. Cash on Delivery all over Bangladesh and free size exchange within 7 days.',
  },
  men: {
    title: "Men's collection",
    short: 'Men',
    description: 'Panjabi, shirts, T-shirts, chinos and pajamas for Eid, office and every day. Sizes S to XXL.',
  },
  women: {
    title: "Women's collection",
    short: 'Women',
    description: 'Kurti and ready-to-wear three-piece sets in cotton, lawn, linen and georgette. Sizes S to XL.',
  },
  new: {
    title: 'New arrivals',
    short: 'New arrivals',
    description: 'Fresh drops for the season, added every week.',
  },
  'flash-sale': {
    title: 'Flash sale',
    short: 'Flash sale',
    description: 'Limited-time prices while stock lasts. Up to 30% off selected styles.',
  },
};
