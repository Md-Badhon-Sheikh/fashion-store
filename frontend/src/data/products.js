/**
 * Product catalogue (sample data) — every product that appears anywhere in the
 * store designs, consolidated into one list. Where the artboards disagree on a
 * price, the Shop / Product / Checkout value wins (see README "Data notes").
 *
 * Later: GET /api/products and GET /api/products/{slug} return this shape.
 *
 * Product shape
 * {
 *   id, slug, sku, name,
 *   category,            // category slug from categories.js (panjabi, shirts, …)
 *   subcategory,         // subcategory slug inside that category
 *   price, oldPrice,     // numbers in BDT; oldPrice null when not on sale
 *   image, tone,         // image key → /images/<key>.jpg, tone = placeholder bg colour
 *   gallery,             // image keys for the product page gallery
 *   colours: [{ name, hex, code, image }],
 *   sizes:   [{ label, stock }],          // stock summed over colours
 *   stockByColour?: { [colourName]: { [size]: stock } }  // detailed variant stock
 *   fabric, description, highlights[], care,
 *   tags: ['new' | 'bestseller'],
 *   rating, reviewsCount,
 *   soldOut,             // true when every variant has 0 stock
 *   flashSale?: { price, compareAt, soldPct, left }  // Home flash-sale block
 *   sizeChart?           // key into sizeCharts
 * }
 */

import { categories, getCategory, getParent } from './categories.js';
import { percentOff } from '../utils/format.js';

// ---- Colour swatches used across the catalogue ----
const C = {
  offWhite: { name: 'Off-white', hex: '#F2EEE6', code: 'OW' },
  white: { name: 'White', hex: '#FFFFFF', code: 'WH' },
  beige: { name: 'Beige', hex: '#C9B79A', code: 'BG' },
  navy: { name: 'Navy', hex: '#2F3E4E', code: 'NV' },
  olive: { name: 'Olive', hex: '#5E6B3A', code: 'OL' },
  maroon: { name: 'Maroon', hex: '#7A2E3A', code: 'MR' },
  black: { name: 'Black', hex: '#16181D', code: 'BK' },
  sky: { name: 'Sky blue', hex: '#9DB8D2', code: 'SK' },
  brown: { name: 'Brown', hex: '#6B4F3A', code: 'BR' },
  mint: { name: 'Mint', hex: '#B9CDB8', code: 'MT' },
  steelBlue: { name: 'Steel blue', hex: '#3B5B7A', code: 'SB' },
  brick: { name: 'Brick red', hex: '#8A3B3B', code: 'BRK' },
  royalBlue: { name: 'Royal blue', hex: '#1F4E79', code: 'RB' },
  grey: { name: 'Grey melange', hex: '#A7A9AC', code: 'GM' },
  bottleGreen: { name: 'Bottle green', hex: '#2E5E4E', code: 'BG2' },
  cream: { name: 'Cream', hex: '#E8D9B5', code: 'CR' },
  sand: { name: 'Sand', hex: '#B9A27D', code: 'SD' },
  slate: { name: 'Slate', hex: '#40505E', code: 'SL' },
  plum: { name: 'Plum', hex: '#3D2E5C', code: 'PL' },
  gold: { name: 'Gold', hex: '#C9A96E', code: 'GD' },
  lavender: { name: 'Lavender', hex: '#B7A8CF', code: 'LV' },
  pink: { name: 'Rose pink', hex: '#D9A3A8', code: 'RP' },
  red: { name: 'Red', hex: '#B0343A', code: 'RD' },
  dustyBlue: { name: 'Dusty blue', hex: '#5B7A99', code: 'DB' },
  yellow: { name: 'Mustard', hex: '#D6A84A', code: 'MS' },
  khaki: { name: 'Khaki', hex: '#C2B49A', code: 'KH' },
};

const ADULT = ['S', 'M', 'L', 'XL', 'XXL'];
const KIDS = ['2Y', '4Y', '6Y', '8Y'];
const WAIST = ['30', '32', '34', '36'];

/** Build the sizes array from labels + per-size stock. */
const sizes = (labels, stock) => labels.map((label, i) => ({ label, stock: stock[i] ?? 0 }));

/** Attach the product image to each colour (designs have one photo per product). */
const colours = (list, image) => list.map((c) => ({ ...c, image }));

/** Product factory with sensible defaults. */
function product(p) {
  const totalStock = p.sizes.reduce((n, s) => n + s.stock, 0);
  return {
    oldPrice: null,
    subcategory: null,
    tags: [],
    rating: 0,
    reviewsCount: 0,
    highlights: [],
    care: 'Gentle machine wash cold, wash dark colours separately, iron on medium heat, do not bleach.',
    gallery: [p.image],
    flashSale: null,
    sizeChart: null,
    ...p,
    soldOut: totalStock === 0,
  };
}

export const products = [
  // ------------------------------------------------------------------ Panjabi
  product({
    id: 'p01',
    slug: 'embroidered-cotton-panjabi',
    sku: 'PNJ-1024',
    name: 'Embroidered Cotton Panjabi',
    category: 'panjabi',
    subcategory: 'embroidered',
    price: 2450,
    oldPrice: 2950,
    image: 'p_panjabi_offwhite',
    tone: '#E4DCCF',
    gallery: ['p_panjabi_offwhite', 'cat_panjabi', 'hero3', 'hero1'],
    colours: [
      { ...C.offWhite, image: 'p_panjabi_offwhite', tone: '#E4DCCF' },
      { ...C.navy, image: 'p_panjabi_navy', tone: '#C9D0D8' },
      { ...C.olive, image: 'p_panjabi_olive', tone: '#D5D9C5' },
    ],
    stockByColour: {
      'Off-white': { S: 6, M: 4, L: 11, XL: 2, XXL: 0 },
      Navy: { S: 0, M: 9, L: 7, XL: 5, XXL: 3 },
      Olive: { S: 3, M: 0, L: 4, XL: 1, XXL: 0 },
    },
    sizes: sizes(ADULT, [9, 13, 22, 8, 3]),
    fabric: 'Cotton · Embroidered',
    fabricDetail: '100% combed cotton, 140 GSM',
    description:
      'A regular-fit panjabi in soft combed cotton with tone-on-tone embroidery on the placket and cuffs. Made for Eid, weddings and Friday prayers.',
    highlights: ['Regular fit, mid-thigh length', 'Hidden button placket, side pockets', 'Matching pajama sold separately'],
    tags: ['bestseller'],
    rating: 4.6,
    reviewsCount: 38,
    ratingBreakdown: [
      { stars: 5, count: 26 },
      { stars: 4, count: 8 },
      { stars: 3, count: 3 },
      { stars: 2, count: 1 },
      { stars: 1, count: 0 },
    ],
    reviews: [
      {
        rating: 5,
        date: '12 Sep 2026',
        text: 'Fabric is soft and the embroidery is neat. Size M fits as per the chart.',
        meta: 'Verified purchase · Size M · Off-white',
      },
      {
        rating: 4,
        date: '28 Aug 2026',
        text: 'Good quality for the price. Delivery inside Dhaka came the next day.',
        meta: 'Verified purchase · Size L · Navy',
      },
    ],
    related: ['linen-blend-panjabi', 'printed-cotton-panjabi', 'kids-cotton-panjabi-set', 'cotton-pajama'],
    flashSale: { price: 1890, compareAt: 2450, soldPct: 70, left: 8 },
    sizeChart: 'panjabi',
  }),
  product({
    id: 'p02',
    slug: 'linen-blend-panjabi',
    sku: 'PNJ-1031',
    name: 'Linen Blend Panjabi',
    category: 'panjabi',
    subcategory: 'linen',
    price: 2650,
    image: 'p_panjabi_linen',
    tone: '#E3DDD2',
    colours: colours([C.offWhite, C.navy, C.brown], 'p_panjabi_linen'),
    sizes: sizes(ADULT, [3, 5, 4, 4, 2]),
    fabric: 'Linen blend',
    fabricDetail: '55% linen, 45% cotton',
    description: 'Breathable linen-blend panjabi cut for the heat, with a band collar and a clean placket.',
    tags: ['new'],
    rating: 4.8,
    reviewsCount: 12,
    sizeChart: 'panjabi',
  }),
  product({
    id: 'p03',
    slug: 'printed-cotton-panjabi',
    sku: 'PNJ-1042',
    name: 'Printed Cotton Panjabi',
    category: 'panjabi',
    subcategory: 'printed',
    price: 1950,
    image: 'p_panjabi_printed',
    tone: '#D6DEE6',
    colours: colours([C.sky, C.maroon], 'p_panjabi_printed'),
    sizes: sizes(ADULT, [1, 3, 3, 2, 0]),
    fabric: 'Cotton · Printed',
    fabricDetail: '100% cotton voile',
    description: 'Lightweight printed cotton panjabi with an all-over geometric motif. Easy everyday wear.',
    rating: 4.4,
    reviewsCount: 21,
    sizeChart: 'panjabi',
  }),
  product({
    id: 'p04',
    slug: 'classic-navy-panjabi',
    sku: 'PNJ-1007',
    name: 'Classic Navy Panjabi',
    category: 'panjabi',
    subcategory: 'embroidered',
    price: 2190,
    oldPrice: 2590,
    image: 'p_panjabi_navy',
    tone: '#C9D0D8',
    colours: colours([C.navy, C.black], 'p_panjabi_navy'),
    sizes: sizes(ADULT, [0, 1, 1, 0, 0]),
    fabric: 'Cotton',
    fabricDetail: '100% cotton',
    description: 'A timeless navy panjabi with subtle collar embroidery. Our most-loved everyday panjabi.',
    tags: ['bestseller'],
    rating: 4.7,
    reviewsCount: 54,
    sizeChart: 'panjabi',
  }),
  product({
    id: 'p05',
    slug: 'navy-slim-fit-panjabi',
    sku: 'PNJ-1052',
    name: 'Navy Slim Fit Panjabi',
    category: 'panjabi',
    subcategory: 'embroidered',
    price: 2250,
    image: 'p_panjabi_navy',
    tone: '#C9D0D8',
    colours: colours([C.navy], 'p_panjabi_navy'),
    sizes: sizes(ADULT, [4, 6, 6, 3, 2]),
    fabric: 'Cotton',
    fabricDetail: '100% cotton',
    description: 'Slim-fit navy panjabi with a tapered body and short side slits.',
    rating: 4.5,
    reviewsCount: 19,
    sizeChart: 'panjabi',
  }),
  product({
    id: 'p06',
    slug: 'mint-cotton-panjabi',
    sku: 'PNJ-1058',
    name: 'Mint Cotton Panjabi',
    category: 'panjabi',
    subcategory: 'printed',
    price: 1990,
    oldPrice: 2350,
    image: 'p_panjabi_olive',
    tone: '#D5D9C5',
    colours: colours([C.mint, C.olive], 'p_panjabi_olive'),
    sizes: sizes(ADULT, [3, 5, 5, 3, 1]),
    fabric: 'Cotton',
    fabricDetail: '100% cotton',
    description: 'Fresh mint cotton panjabi with a mandarin collar. Light enough for summer Eid.',
    rating: 4.4,
    reviewsCount: 9,
    sizeChart: 'panjabi',
  }),
  product({
    id: 'p07',
    slug: 'olive-slub-cotton-panjabi',
    sku: 'PNJ-1063',
    name: 'Olive Slub Cotton Panjabi',
    category: 'panjabi',
    subcategory: 'linen',
    price: 2350,
    image: 'p_panjabi_olive',
    tone: '#D5D9C5',
    colours: colours([C.olive, C.beige], 'p_panjabi_olive'),
    sizes: sizes(ADULT, [2, 4, 4, 3, 1]),
    fabric: 'Slub cotton',
    fabricDetail: '100% slub cotton',
    description: 'Textured slub cotton panjabi in earthy olive with contrast buttons.',
    rating: 4.5,
    reviewsCount: 17,
    sizeChart: 'panjabi',
  }),
  product({
    id: 'p08',
    slug: 'chikankari-panjabi',
    sku: 'PNJ-1070',
    name: 'Chikankari Panjabi',
    category: 'panjabi',
    subcategory: 'embroidered',
    price: 3250,
    image: 'p_panjabi_offwhite',
    tone: '#E8E1D6',
    colours: colours([C.white, C.offWhite], 'p_panjabi_offwhite'),
    sizes: sizes(ADULT, [1, 2, 2, 1, 1]),
    fabric: 'Cotton · Hand embroidered',
    fabricDetail: '100% cotton, hand chikankari embroidery',
    description: 'Hand-embroidered chikankari panjabi with delicate shadow work on the front panel.',
    tags: ['new'],
    rating: 4.9,
    reviewsCount: 8,
    sizeChart: 'panjabi',
  }),
  product({
    id: 'p09',
    slug: 'kabli-style-panjabi-set',
    sku: 'PNJ-1081',
    name: 'Kabli Style Panjabi Set',
    category: 'panjabi',
    subcategory: 'kabli',
    price: 2890,
    image: 'p_panjabi_olive',
    tone: '#DCD8C8',
    colours: colours([C.olive, C.black], 'p_panjabi_olive'),
    sizes: sizes(ADULT, [0, 0, 0, 0, 0]),
    fabric: 'Cotton · 2-piece set',
    fabricDetail: '100% cotton, 2-piece set',
    description: 'Kabli-style long panjabi with matching trousers. Relaxed fit.',
    rating: 4.3,
    reviewsCount: 6,
    sizeChart: 'panjabi',
  }),
  product({
    id: 'p10',
    slug: 'jacquard-festive-panjabi',
    sku: 'PNJ-1090',
    name: 'Jacquard Festive Panjabi',
    category: 'panjabi',
    subcategory: 'festive',
    price: 3650,
    oldPrice: 4200,
    image: 'p_panjabi_navy',
    tone: '#D2D6DE',
    colours: colours([C.navy, C.maroon], 'p_panjabi_navy'),
    sizes: sizes(ADULT, [1, 1, 2, 1, 0]),
    fabric: 'Jacquard',
    fabricDetail: 'Cotton-viscose jacquard',
    description: 'Woven jacquard festive panjabi with a subtle sheen. Made for weddings and Eid.',
    rating: 4.6,
    reviewsCount: 14,
    sizeChart: 'panjabi',
  }),
  product({
    id: 'p11',
    slug: 'short-kurta-panjabi',
    sku: 'PNJ-1102',
    name: 'Short Kurta Panjabi',
    category: 'panjabi',
    subcategory: 'short-kurta',
    price: 1590,
    image: 'p_panjabi_printed',
    tone: '#DCE2E6',
    colours: colours([C.sky, C.offWhite, C.beige], 'p_panjabi_printed'),
    sizes: sizes(ADULT, [4, 6, 6, 4, 2]),
    fabric: 'Cotton · Printed',
    fabricDetail: '100% cotton',
    description: 'Hip-length printed short kurta. Pairs with jeans or chinos.',
    rating: 4.3,
    reviewsCount: 11,
    sizeChart: 'panjabi',
  }),
  product({
    id: 'p12',
    slug: 'pin-tuck-cotton-panjabi',
    sku: 'PNJ-1110',
    name: 'Pin-tuck Cotton Panjabi',
    category: 'panjabi',
    subcategory: 'embroidered',
    price: 2250,
    image: 'p_panjabi_linen',
    tone: '#E6E0D4',
    colours: colours([C.offWhite, C.sky], 'p_panjabi_linen'),
    sizes: sizes(ADULT, [0, 1, 1, 1, 0]),
    fabric: 'Cotton',
    fabricDetail: '100% cotton',
    description: 'Cotton panjabi with fine pin-tuck pleats on the front placket.',
    rating: 4.5,
    reviewsCount: 7,
    sizeChart: 'panjabi',
  }),
  product({
    id: 'p13',
    slug: 'mandarin-collar-panjabi',
    sku: 'PNJ-1118',
    name: 'Mandarin Collar Panjabi',
    category: 'panjabi',
    subcategory: 'printed',
    price: 1850,
    oldPrice: 2250,
    image: 'p_panjabi_offwhite',
    tone: '#E9E4DA',
    colours: colours([C.white, C.black, C.navy], 'p_panjabi_offwhite'),
    sizes: sizes(ADULT, [3, 4, 4, 3, 2]),
    fabric: 'Cotton',
    fabricDetail: '100% cotton',
    description: 'Minimal mandarin-collar panjabi in crisp cotton.',
    rating: 4.4,
    reviewsCount: 15,
    sizeChart: 'panjabi',
  }),
  product({
    id: 'p14',
    slug: 'silk-blend-eid-panjabi',
    sku: 'PNJ-1125',
    name: 'Silk Blend Eid Panjabi',
    category: 'panjabi',
    subcategory: 'festive',
    price: 4450,
    image: 'p_panjabi_navy',
    tone: '#DDD4C6',
    colours: colours([C.beige, C.maroon], 'p_panjabi_navy'),
    sizes: sizes(ADULT, [0, 0, 0, 0, 0]),
    fabric: 'Silk blend',
    fabricDetail: 'Silk-cotton blend',
    description: 'Silk-blend Eid panjabi with a soft drape and self-woven texture.',
    tags: ['new'],
    rating: 4.8,
    reviewsCount: 5,
    sizeChart: 'panjabi',
  }),
  product({
    id: 'p15',
    slug: 'printed-festive-panjabi',
    sku: 'PNJ-1133',
    name: 'Printed Festive Panjabi',
    category: 'panjabi',
    subcategory: 'festive',
    price: 2150,
    oldPrice: 2690,
    image: 'p_panjabi_printed',
    tone: '#E6D3D3',
    colours: colours([C.maroon, C.sky], 'p_panjabi_printed'),
    sizes: sizes(ADULT, [0, 0, 0, 0, 0]),
    fabric: 'Cotton · Printed',
    fabricDetail: '100% cotton',
    description: 'Festive digital-print panjabi with a contrast placket.',
    rating: 4.2,
    reviewsCount: 10,
    sizeChart: 'panjabi',
  }),
  product({
    id: 'p16',
    slug: 'panjabi-pajama-set',
    sku: 'PNJ-1140',
    name: 'Panjabi-Pajama Set',
    category: 'panjabi',
    subcategory: 'kabli',
    price: 2890,
    oldPrice: 3290,
    image: 'p_pajama',
    tone: '#EDEBE6',
    colours: colours([C.offWhite, C.white], 'p_pajama'),
    sizes: sizes(ADULT, [2, 4, 4, 3, 1]),
    fabric: 'Cotton · 2-piece set',
    fabricDetail: '100% cotton, panjabi + pajama',
    description: 'Matching cotton panjabi and pajama set, ready for Eid morning.',
    rating: 4.3,
    reviewsCount: 11,
    sizeChart: 'panjabi',
  }),

  // ------------------------------------------------------------------ Shirts
  product({
    id: 's01',
    slug: 'slim-fit-oxford-shirt',
    sku: 'SHR-0412',
    name: 'Slim Fit Oxford Shirt',
    category: 'shirts',
    subcategory: 'formal',
    price: 1190,
    oldPrice: 1650,
    image: 'p_shirt_oxford',
    tone: '#D3DCE4',
    colours: colours([C.sky, C.white], 'p_shirt_oxford'),
    sizes: sizes(['M', 'L', 'XL', 'XXL'], [4, 5, 3, 2]),
    fabric: 'Cotton Oxford',
    fabricDetail: '100% cotton Oxford weave',
    description: 'Slim-fit Oxford shirt with a button-down collar. Office to evening.',
    tags: ['bestseller'],
    rating: 4.5,
    reviewsCount: 42,
    flashSale: { price: 1190, compareAt: 1650, soldPct: 55, left: 14 },
  }),
  product({
    id: 's02',
    slug: 'printed-casual-shirt',
    sku: 'SHR-0420',
    name: 'Printed Casual Shirt',
    category: 'shirts',
    subcategory: 'casual',
    price: 1450,
    image: 'p_shirt_check',
    tone: '#D6DEE6',
    colours: colours([C.steelBlue, C.brick], 'p_shirt_check'),
    sizes: sizes(['M', 'L', 'XL'], [5, 6, 4]),
    fabric: 'Cotton',
    fabricDetail: '100% cotton poplin',
    description: 'Relaxed printed casual shirt with a camp collar.',
    rating: 4.3,
    reviewsCount: 13,
  }),
  product({
    id: 's03',
    slug: 'check-casual-shirt',
    sku: 'SHR-0428',
    name: 'Check Casual Shirt',
    category: 'shirts',
    subcategory: 'casual',
    price: 1450,
    image: 'p_shirt_check',
    tone: '#D6DEE6',
    colours: colours([C.steelBlue, C.brick], 'p_shirt_check'),
    sizes: sizes(['M', 'L', 'XL'], [6, 7, 5]),
    fabric: 'Cotton',
    fabricDetail: '100% yarn-dyed cotton',
    description: 'Yarn-dyed check shirt in soft brushed cotton. Regular fit.',
    tags: ['new'],
    rating: 4.6,
    reviewsCount: 9,
  }),
  product({
    id: 's04',
    slug: 'navy-twill-shirt',
    sku: 'SHR-0435',
    name: 'Navy Twill Shirt',
    category: 'shirts',
    subcategory: 'formal',
    price: 1550,
    oldPrice: 1790,
    image: 'cat_shirt',
    tone: '#CFD8DF',
    colours: colours([C.navy], 'cat_shirt'),
    sizes: sizes(['M', 'L', 'XL', 'XXL'], [3, 4, 3, 1]),
    fabric: 'Cotton twill',
    fabricDetail: '100% cotton twill',
    description: 'Structured navy twill shirt with a spread collar.',
    rating: 4.4,
    reviewsCount: 16,
  }),

  // ---------------------------------------------------------------- T-Shirts
  product({
    id: 't01',
    slug: 'premium-polo-t-shirt',
    sku: 'TSH-0219',
    name: 'Premium Polo T-Shirt',
    category: 't-shirts',
    subcategory: 'polo',
    price: 890,
    image: 'p_polo',
    tone: '#DCE0D6',
    colours: colours([C.black, C.white, C.royalBlue], 'p_polo'),
    sizes: sizes(ADULT, [6, 9, 9, 1, 4]),
    fabric: 'Cotton piqué',
    fabricDetail: '100% combed cotton piqué, 220 GSM',
    description: 'Premium piqué polo with a ribbed collar and two-button placket.',
    tags: ['new'],
    rating: 4.6,
    reviewsCount: 31,
  }),
  product({
    id: 't02',
    slug: 'basic-crew-neck-t-shirt',
    sku: 'TSH-0104',
    name: 'Basic Crew Neck T-Shirt',
    category: 't-shirts',
    subcategory: 'basic',
    price: 390,
    oldPrice: 550,
    image: 'p_tshirt_basic',
    tone: '#DADFD3',
    colours: colours([C.black, C.white, C.grey], 'p_tshirt_basic'),
    sizes: sizes(ADULT, [10, 14, 24, 12, 6]),
    fabric: 'Cotton jersey',
    fabricDetail: '100% combed cotton jersey, 180 GSM',
    description: 'The everyday crew-neck tee in soft combed cotton. Pre-shrunk.',
    tags: ['bestseller'],
    rating: 4.5,
    reviewsCount: 88,
    flashSale: { price: 390, compareAt: 550, soldPct: 40, left: 31 },
  }),
  product({
    id: 't03',
    slug: 'essential-white-tee',
    sku: 'TSH-0110',
    name: 'Essential White Tee',
    category: 't-shirts',
    subcategory: 'basic',
    price: 450,
    image: 'cat_tshirt',
    tone: '#E4E6E1',
    colours: colours([C.white], 'cat_tshirt'),
    sizes: sizes(ADULT, [0, 0, 0, 0, 0]),
    fabric: 'Cotton jersey',
    fabricDetail: '100% combed cotton jersey',
    description: 'A heavier white tee that keeps its shape wash after wash.',
    rating: 4.4,
    reviewsCount: 23,
  }),

  // ------------------------------------------------------------------- Kurti
  product({
    id: 'k01',
    slug: 'block-print-cotton-kurti',
    sku: 'KRT-0310',
    name: 'Block Print Cotton Kurti',
    category: 'kurti',
    subcategory: 'block-print',
    price: 1350,
    oldPrice: 1590,
    image: 'p_kurti_block',
    tone: '#E8D8D6',
    colours: colours([C.maroon, C.bottleGreen, C.cream], 'p_kurti_block'),
    sizes: sizes(['S', 'M', 'L', 'XL'], [4, 6, 6, 3]),
    fabric: 'Cotton · Block print',
    fabricDetail: '100% cotton, hand block print',
    description: 'Hand block-printed cotton kurti with a round neck and three-quarter sleeves.',
    tags: ['new'],
    rating: 4.7,
    reviewsCount: 27,
  }),
  product({
    id: 'k02',
    slug: 'solid-linen-long-kurti',
    sku: 'KRT-0322',
    name: 'Solid Linen Long Kurti',
    category: 'kurti',
    subcategory: 'linen',
    price: 1650,
    image: 'p_kurti_linen',
    tone: '#E2DCD3',
    colours: colours([C.sand, C.slate], 'p_kurti_linen'),
    sizes: sizes(['S', 'M', 'L'], [4, 5, 4]),
    fabric: 'Linen',
    fabricDetail: '60% linen, 40% cotton',
    description: 'Calf-length solid linen kurti with side slits and a mandarin collar.',
    tags: ['new'],
    rating: 4.6,
    reviewsCount: 12,
  }),
  product({
    id: 'k03',
    slug: 'embroidered-maroon-kurti',
    sku: 'KRT-0330',
    name: 'Embroidered Maroon Kurti',
    category: 'kurti',
    subcategory: 'embroidered',
    price: 1490,
    oldPrice: 1850,
    image: 'cat_kurti',
    tone: '#E6D3D3',
    colours: colours([C.maroon], 'cat_kurti'),
    sizes: sizes(['S', 'M', 'L', 'XL'], [2, 4, 4, 2]),
    fabric: 'Cotton · Embroidered',
    fabricDetail: '100% cotton',
    description: 'Maroon cotton kurti with thread embroidery on the yoke.',
    rating: 4.5,
    reviewsCount: 18,
  }),
  product({
    id: 'k04',
    slug: 'lavender-festive-kurti',
    sku: 'KRT-0341',
    name: 'Lavender Festive Kurti',
    category: 'kurti',
    subcategory: 'embroidered',
    price: 1990,
    image: 'hero2',
    tone: '#E3DDEA',
    colours: colours([C.lavender, C.pink], 'hero2'),
    sizes: sizes(['S', 'M', 'L', 'XL'], [3, 4, 4, 2]),
    fabric: 'Georgette',
    fabricDetail: 'Georgette with cotton lining',
    description: 'Flowing lavender festive kurti with sequin details on the neckline.',
    tags: ['new'],
    rating: 4.7,
    reviewsCount: 6,
  }),

  // ------------------------------------------------------------- Three-Piece
  product({
    id: 'w01',
    slug: 'printed-lawn-three-piece',
    sku: 'TPC-0510',
    name: 'Printed Lawn Three-Piece',
    category: 'three-piece',
    subcategory: 'lawn',
    price: 2290,
    oldPrice: 2990,
    image: 'p_threepiece_lawn',
    tone: '#E7D6DA',
    colours: colours([C.pink, C.sky], 'p_threepiece_lawn'),
    sizes: sizes(['M', 'L', 'XL'], [1, 3, 1]),
    fabric: 'Lawn',
    fabricDetail: 'Cotton lawn kameez + dupatta, cotton salwar',
    description: 'Printed lawn kameez, dupatta and salwar. Stitched and ready to wear.',
    rating: 4.6,
    reviewsCount: 24,
    flashSale: { price: 2290, compareAt: 2990, soldPct: 82, left: 5 },
  }),
  product({
    id: 'w02',
    slug: 'embroidered-georgette-set',
    sku: 'TPC-0522',
    name: 'Embroidered Georgette Set',
    category: 'three-piece',
    subcategory: 'georgette',
    price: 3850,
    image: 'p_threepiece_georgette',
    tone: '#E0D9E6',
    colours: colours([C.plum, C.gold], 'p_threepiece_georgette'),
    sizes: sizes(['M', 'L', 'XL'], [2, 3, 2]),
    fabric: 'Georgette · Embroidered',
    fabricDetail: 'Georgette with silk lining',
    description: 'Embroidered georgette three-piece with a chiffon dupatta. Party and Eid ready.',
    tags: ['bestseller'],
    rating: 4.8,
    reviewsCount: 33,
  }),
  product({
    id: 'w03',
    slug: 'red-cotton-three-piece',
    sku: 'TPC-0530',
    name: 'Red Cotton Three-Piece',
    category: 'three-piece',
    subcategory: 'cotton',
    price: 2490,
    oldPrice: 2790,
    image: 'cat_threepiece',
    tone: '#E9D2D2',
    colours: colours([C.red], 'cat_threepiece'),
    sizes: sizes(['M', 'L', 'XL'], [0, 0, 0]),
    fabric: 'Cotton',
    fabricDetail: '100% cotton',
    description: 'Classic red cotton three-piece with printed dupatta.',
    rating: 4.5,
    reviewsCount: 15,
  }),

  // -------------------------------------------------------------------- Kids
  product({
    id: 'c01',
    slug: 'kids-cotton-panjabi-set',
    sku: 'KID-0210',
    name: 'Kids Cotton Panjabi Set',
    category: 'kids',
    subcategory: 'boys-panjabi',
    price: 1150,
    oldPrice: 1350,
    image: 'p_kids_panjabi',
    tone: '#E8E0CF',
    colours: colours([C.offWhite, C.dustyBlue], 'p_kids_panjabi'),
    sizes: sizes(KIDS, [4, 5, 5, 3]),
    fabric: 'Cotton · 2-piece set',
    fabricDetail: '100% soft cotton',
    description: 'Soft cotton panjabi and pajama set for boys aged 2 to 8.',
    tags: ['new'],
    rating: 4.6,
    reviewsCount: 29,
  }),
  product({
    id: 'c02',
    slug: 'girls-festive-frock',
    sku: 'KID-0222',
    name: 'Girls Festive Frock',
    category: 'kids',
    subcategory: 'girls-frocks',
    price: 1290,
    image: 'cat_kids',
    tone: '#EDE3C8',
    colours: colours([C.yellow, C.pink], 'cat_kids'),
    sizes: sizes(KIDS, [2, 3, 3, 2]),
    fabric: 'Cotton',
    fabricDetail: '100% cotton with net layer',
    description: 'Twirl-ready festive frock with a cotton lining.',
    rating: 4.7,
    reviewsCount: 8,
  }),

  // ------------------------------------------------------------------- Pants
  product({
    id: 'n01',
    slug: 'stretch-chino-pant',
    sku: 'PNT-0610',
    name: 'Stretch Chino Pant',
    category: 'pants',
    subcategory: 'chino',
    price: 1550,
    oldPrice: 1790,
    image: 'p_chino',
    tone: '#DDD8CC',
    colours: colours([C.khaki, C.navy, C.black], 'p_chino'),
    sizes: sizes(WAIST, [3, 5, 5, 3]),
    fabric: 'Stretch cotton',
    fabricDetail: '98% cotton, 2% elastane',
    description: 'Slim-tapered stretch chino with a comfortable mid rise.',
    tags: ['new'],
    rating: 4.5,
    reviewsCount: 20,
  }),
  product({
    id: 'n02',
    slug: 'slim-fit-khaki-chino',
    sku: 'PNT-0618',
    name: 'Slim Fit Khaki Chino',
    category: 'pants',
    subcategory: 'chino',
    price: 1650,
    image: 'cat_pant',
    tone: '#E0D9C9',
    colours: colours([C.khaki], 'cat_pant'),
    sizes: sizes(WAIST, [0, 0, 0, 0]),
    fabric: 'Cotton twill',
    fabricDetail: '100% cotton twill',
    description: 'Classic slim-fit chino in khaki cotton twill.',
    rating: 4.4,
    reviewsCount: 12,
  }),
  product({
    id: 'n03',
    slug: 'cotton-pajama',
    sku: 'PJM-0330',
    name: 'Cotton Pajama',
    category: 'pants',
    subcategory: 'pajama',
    price: 750,
    image: 'p_pajama',
    tone: '#DCE0D6',
    colours: colours([C.offWhite, C.white, C.beige], 'p_pajama'),
    sizes: sizes(['M', 'L', 'XL'], [6, 9, 5]),
    fabric: 'Cotton',
    fabricDetail: '100% cotton',
    description: 'Straight-cut cotton pajama with a drawstring waist. Pairs with any panjabi.',
    rating: 4.4,
    reviewsCount: 18,
  }),
];

/** Size charts (inches), keyed by product.sizeChart */
export const sizeCharts = {
  panjabi: {
    columns: ['Chest', 'Length', 'Sleeve'],
    rows: [
      { size: 'S', values: ['38', '40', '23'] },
      { size: 'M', values: ['40', '41', '23.5'] },
      { size: 'L', values: ['42', '42', '24'] },
      { size: 'XL', values: ['44', '43', '24.5'] },
      { size: 'XXL', values: ['46', '44', '25'] },
    ],
    howToMeasure: [
      'Chest: measure around the fullest part, under the arms. Length: from the highest shoulder point to the hem.',
      'Between two sizes? Choose the larger size for a relaxed fit. Free size exchange within 7 days.',
    ],
  },
};

// ---------------------------------------------------------------------------
// Helpers (keep pages free of data-shaping logic; mirror these on the API)
// ---------------------------------------------------------------------------

export function getProductById(id) {
  return products.find((p) => p.id === id) || null;
}

export function getProductBySlug(slug) {
  return products.find((p) => p.slug === slug) || null;
}

/** Keeps the order of `ids`, skips unknown ids. */
export function getProductsByIds(ids) {
  return ids.map(getProductById).filter(Boolean);
}

export function getProductsBySlugs(slugs) {
  return slugs.map(getProductBySlug).filter(Boolean);
}

/** Products of a category slug ("panjabi") or a department slug ("men"). */
export function getProductsByCategory(slug) {
  if (!slug) return products;
  if (getCategory(slug)) return products.filter((p) => p.category === slug);
  const childSlugs = categories.filter((c) => c.parent === slug).map((c) => c.slug);
  return products.filter((p) => childSlugs.includes(p.category));
}

/** Department slug for a product: 'men' | 'women' | 'kids' */
export function getProductParent(product) {
  return getCategory(product.category)?.parent ?? null;
}

/** "Men · Panjabi", or just "Kids" when department and category share a name. */
export function getCategoryLabel(product) {
  const cat = getCategory(product.category);
  if (!cat) return '';
  const parent = getParent(cat.parent);
  if (!parent || parent.name === cat.name) return cat.name;
  return `${parent.name} · ${cat.name}`;
}

export function isOnSale(product) {
  return Boolean(product.oldPrice && product.oldPrice > product.price);
}

export function getDiscountPercent(product) {
  return percentOff(product.price, product.oldPrice);
}

export function getTotalStock(product) {
  return product.sizes.reduce((n, s) => n + s.stock, 0);
}

/** Stock for one variant. Uses stockByColour when present, else the per-size stock. */
export function getStock(product, colourName, sizeLabel) {
  if (product.stockByColour && colourName && product.stockByColour[colourName]) {
    return product.stockByColour[colourName][sizeLabel] ?? 0;
  }
  return product.sizes.find((s) => s.label === sizeLabel)?.stock ?? 0;
}

/** First in-stock { colour, size } — used by quick "Add to cart" buttons. */
export function getDefaultVariant(product) {
  for (const colour of product.colours) {
    for (const s of product.sizes) {
      if (getStock(product, colour.name, s.label) > 0) return { colour: colour.name, size: s.label };
    }
  }
  return { colour: product.colours[0]?.name ?? null, size: product.sizes[0]?.label ?? null };
}

/** "PNJ-1024-OW-M" */
export function getVariantSku(product, colourName, sizeLabel) {
  const code = product.colours.find((c) => c.name === colourName)?.code ?? '—';
  return `${product.sku}-${code}-${sizeLabel || '—'}`;
}

export function getNewArrivals() {
  return products.filter((p) => p.tags.includes('new') || p.tags.includes('bestseller'));
}

export function getFlashSaleProducts() {
  return products.filter((p) => p.flashSale);
}

/** Simple case-insensitive search over name, SKU, fabric and category. */
export function searchProducts(query) {
  const q = query.trim().toLowerCase();
  if (!q) return products;
  return products.filter((p) =>
    [p.name, p.sku, p.fabric, p.category, p.subcategory].filter(Boolean).some((v) => v.toLowerCase().includes(q)),
  );
}
