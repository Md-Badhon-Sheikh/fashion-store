/**
 * Info page copy (sample content) for /pages/:slug — InfoPage.dc.html.
 * Page list & titles come from `infoPages` in content.js; this file holds the
 * body copy. Later: GET /api/pages/{slug} from the admin "Banners & pages".
 *
 * IMPORTANT: this is placeholder copy, not legal advice. Anything marked
 * [PLACEHOLDER] is a business fact the store owner must fill in or confirm,
 * and the Privacy policy and Terms must be reviewed by a qualified lawyer.
 *
 * Page shape
 * {
 *   navLabel?,            // label in the left page list (defaults to the title)
 *   eyebrow, title, updated?, intro,
 *   notice?,              // highlighted note under the intro
 *   highlights?: [{ value, text, accent? }],
 *   contactFirst?,        // show the contact card before the sections
 *   sizeGuide?,           // render the size-guide tables
 *   sections: [{ heading, blocks: Block[] }],
 *   faqTitle?, faqs?: [{ q, a }], faqGroups?: [{ title, items: [{ q, a }] }], faqDefaultOpen?,
 *   contact?,             // append the "Still have questions? Contact us" card
 * }
 * Block: { type: 'p', text } | { type: 'list', items } | { type: 'steps', items }
 *        | { type: 'table', columns, rows }
 * Text values are a string or an array of strings and links { label, to }.
 */

import { contact, delivery, paymentMethods } from './content.js';
import { sizeCharts } from './products.js';
import { formatBDT } from '../utils/format.js';

const [inside, outside] = delivery.zones;
const free = formatBDT(delivery.freeDeliveryThreshold);

/** Map / directions link for the contact card. Replace with the shop's Google Maps link. */
export const mapUrl = `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(contact.address)}`;

/** Size guide tables (inches). Panjabi comes from the product data; the rest are samples. */
export const sizeGuide = {
  intro:
    'All measurements are in inches and taken from the garment laid flat. Each product page also shows the chart for that item.',
  howToMeasure: [
    'Chest / bust: measure around the fullest part, under the arms, keeping the tape level.',
    'Length: from the highest shoulder point down to the hem.',
    'Waist: around your natural waistline, where you normally wear your trousers.',
    'Between two sizes? Choose the larger size for a relaxed fit. Free size exchange within 7 days.',
  ],
  tables: [
    { id: 'panjabi', label: 'Panjabi', ...sizeCharts.panjabi, sample: false },
    {
      id: 'shirts',
      label: 'Shirts & T-shirts',
      columns: ['Chest', 'Length', 'Shoulder'],
      rows: [
        { size: 'S', values: ['38', '28', '17'] },
        { size: 'M', values: ['40', '29', '17.5'] },
        { size: 'L', values: ['42', '30', '18'] },
        { size: 'XL', values: ['44', '31', '18.5'] },
        { size: 'XXL', values: ['46', '32', '19'] },
      ],
      sample: true,
    },
    {
      id: 'kurti',
      label: 'Kurti & three-piece',
      columns: ['Bust', 'Length', 'Hip'],
      rows: [
        { size: 'S', values: ['36', '42', '38'] },
        { size: 'M', values: ['38', '43', '40'] },
        { size: 'L', values: ['40', '44', '42'] },
        { size: 'XL', values: ['42', '45', '44'] },
      ],
      sample: true,
    },
    {
      id: 'pants',
      label: 'Pants',
      columns: ['Waist', 'Hip', 'Length'],
      rows: [
        { size: '30', values: ['30', '39', '40'] },
        { size: '32', values: ['32', '41', '41'] },
        { size: '34', values: ['34', '43', '41.5'] },
        { size: '36', values: ['36', '45', '42'] },
      ],
      sample: true,
    },
    {
      id: 'kids',
      label: 'Kids',
      columns: ['Age', 'Chest', 'Length'],
      rows: [
        { size: '2Y', values: ['2 years', '22', '20'] },
        { size: '4Y', values: ['4 years', '24', '23'] },
        { size: '6Y', values: ['6 years', '26', '26'] },
        { size: '8Y', values: ['8 years', '28', '29'] },
      ],
      sample: true,
    },
  ],
  sampleNote: '[PLACEHOLDER] Sample measurements — replace with your own size chart.',
};

const returnFaqs = [
  {
    q: 'Can I exchange a sale item?',
    a: 'Yes. Sale items can be exchanged for another size or colour within 7 days. Only products marked "Final sale" during flash sales cannot be exchanged or returned.',
  },
  {
    q: 'How long does an exchange take?',
    a: 'Inside Dhaka, the replacement usually reaches you 2–3 working days after the courier picks up your item. Outside Dhaka it takes 4–6 working days. We send an SMS when the new item is on its way.',
  },
  {
    q: 'I paid Cash on Delivery. How do I get my refund?',
    a: 'We refund COD orders to your bKash or Nagad number, or by bank transfer, within 5–7 working days after we receive and check the item. Tell us your preferred method in the request form.',
  },
  {
    q: 'The size I want is out of stock. What now?',
    a: 'You can choose a different product of the same or higher value (pay the difference), or take store credit that you can use on your next order within 6 months.',
  },
  {
    q: 'Can I return an item at your shop?',
    a: 'Yes. Bring the item with its tags and your order number to our shop in Dhaka during opening hours. We will exchange it on the spot if the new size is in stock.',
  },
];

const legalNotice =
  '[PLACEHOLDER] Sample text for the demo store, not legal advice. Have this page written or reviewed by a qualified lawyer before going live.';

export const pageContent = {
  /* ------------------------------------------------------------------ About */
  about: {
    eyebrow: 'Our story',
    title: 'About us',
    intro:
      'YOUR BRAND makes everyday and festive clothing for men, women and kids — panjabi, shirts, kurti and three-piece sets in breathable cotton and linen, priced for every day.',
    highlights: [
      { value: 'S – XXL', text: 'sizes in every men’s and women’s style', accent: true },
      { value: 'COD', text: 'Cash on Delivery all over Bangladesh' },
      { value: '7 days', text: 'easy size or colour exchange' },
    ],
    sections: [
      {
        heading: 'Who we are',
        blocks: [
          {
            type: 'p',
            text: 'We started in Dhaka in [PLACEHOLDER: year] with a small range of cotton panjabi. Today we design, source and sell our own collections online and at our shop, with new drops for Eid, Puja and Pohela Boishakh.',
          },
          {
            type: 'p',
            text: '[PLACEHOLDER] Add a few lines about the founders, the team and what makes the brand different.',
          },
        ],
      },
      {
        heading: 'What we care about',
        blocks: [
          {
            type: 'list',
            items: [
              'Fabrics that work in our weather: combed cotton, linen blends and lawn.',
              'Honest prices — the price you see is the price you pay, with delivery shown before checkout.',
              'Fit you can trust: every product page has a size chart, and exchanges are free for the product.',
              'Real people behind the hotline who answer every order call and message.',
            ],
          },
        ],
      },
      {
        heading: 'Visit or contact us',
        blocks: [
          {
            type: 'p',
            text: [
              `Our shop is at ${contact.address}, open ${contact.hours}. Questions about an order? `,
              { label: 'Contact us', to: '/pages/contact' },
              ' or ',
              { label: 'track your order', to: '/track-order' },
              '.',
            ],
          },
        ],
      },
    ],
  },

  /* ---------------------------------------------------------------- Contact */
  contact: {
    eyebrow: 'Help centre',
    title: 'Contact us',
    intro:
      'Questions about an order, a size or an exchange? Call, WhatsApp or email us — we reply during opening hours.',
    contactFirst: true,
    sections: [
      {
        heading: 'Before you get in touch',
        blocks: [
          {
            type: 'list',
            items: [
              'Keep your order number handy (for example #WB-10482). It is in your confirmation SMS and email.',
              ['Most delivery questions are answered on the ', { label: 'Track order', to: '/track-order' }, ' page.'],
              ['For exchanges, read the ', { label: 'Return & exchange policy', to: '/pages/return-policy' }, ' first.'],
            ],
          },
        ],
      },
      {
        heading: 'Wholesale & corporate orders',
        blocks: [
          {
            type: 'p',
            text: 'Uniforms, Eid gifts for your team or bulk orders: email [PLACEHOLDER: wholesale email] with the products, sizes and quantities you need.',
          },
        ],
      },
    ],
  },

  /* -------------------------------------------------------------------- FAQ */
  faq: {
    eyebrow: 'Help centre',
    title: 'Frequently asked questions',
    navLabel: 'FAQ',
    intro: 'Quick answers about ordering, delivery, payment, sizes and exchanges.',
    faqGroups: [
      {
        title: 'Orders & payment',
        items: [
          {
            q: 'How do I place an order?',
            a: 'Add products to your cart, go to checkout, enter your name, phone and address, and choose a payment method. We call or SMS you to confirm the order.',
          },
          {
            q: 'Which payment methods do you accept?',
            a: `${paymentMethods.join(', ')}. Cash on Delivery is available all over Bangladesh.`,
          },
          {
            q: 'How do I use a coupon code?',
            a: `Enter the code in the cart or at checkout and tap Apply. Codes have a minimum order value — for example EID10 gives 10% off orders over ${formatBDT(2500)}. Only one code can be used per order.`,
          },
          {
            q: 'Can I change or cancel my order?',
            a: 'Call our hotline with your order number as soon as possible. [PLACEHOLDER] Confirm until when orders can be changed (e.g. before they are handed to the courier).',
          },
        ],
      },
      {
        title: 'Delivery',
        items: [
          {
            q: 'How much is delivery and how long does it take?',
            a: `${inside.name}: ${formatBDT(inside.fee)}, ${inside.eta}. ${outside.name}: ${formatBDT(outside.fee)}, ${outside.eta}. Delivery is free on orders over ${free} (not combined with coupon codes).`,
          },
          {
            q: 'How can I track my order?',
            a: 'Use the Track order page with your order number and phone, or open My account → My orders. You also get an SMS when your order ships.',
          },
        ],
      },
      { title: 'Returns & exchanges', items: returnFaqs.slice(0, 3) },
      {
        title: 'Sizes & products',
        items: [
          {
            q: 'How do I find my size?',
            a: 'Every product page has a size chart, and our Size guide explains how to measure. Between two sizes? Choose the larger one for a relaxed fit.',
          },
          {
            q: 'Do colours look the same as in the photos?',
            a: 'We photograph every product in daylight, but screens differ. If the colour is clearly different from the photo, we exchange it free of charge.',
          },
        ],
      },
    ],
    contact: true,
  },

  /* ------------------------------------------------------------- Size guide */
  'size-guide': {
    eyebrow: 'Help centre',
    title: 'Size guide',
    intro: sizeGuide.intro,
    sizeGuide: true,
    sections: [
      {
        heading: 'How to measure',
        blocks: [{ type: 'list', items: sizeGuide.howToMeasure }],
      },
      {
        heading: 'Still not sure?',
        blocks: [
          {
            type: 'p',
            text: [
              'Send us your height, weight and usual size on WhatsApp and we will suggest the right size. Wrong fit? Exchange it within 7 days — see the ',
              { label: 'Return & exchange policy', to: '/pages/return-policy' },
              '.',
            ],
          },
        ],
      },
    ],
  },

  /* --------------------------------------------------------- Return policy */
  'return-policy': {
    eyebrow: 'Help centre',
    title: 'Return & exchange policy',
    updated: '1 Sep 2026',
    intro:
      'We want every panjabi, kurti and shirt to fit right. If it doesn’t, you can exchange it for another size or colour within 7 days of delivery, at no extra cost for the product.',
    highlights: [
      { value: '7 days', text: 'to exchange size or colour after delivery', accent: true },
      { value: '3 days', text: 'to report a damaged or wrong item for a full refund' },
      { value: '5–7 days', text: 'refund to bKash, Nagad or bank after we receive the item' },
    ],
    sections: [
      {
        heading: '1. What you can exchange or return',
        blocks: [
          {
            type: 'list',
            items: [
              'Size or colour exchange for any full-price or sale item, within 7 days of delivery.',
              'Full refund if the item arrived damaged, defective or different from what you ordered — please report it within 3 days.',
              'If the size you want is out of stock, you can pick another product of equal value or take store credit.',
            ],
          },
        ],
      },
      {
        heading: '2. Conditions',
        blocks: [
          {
            type: 'list',
            items: [
              'The item is unworn, unwashed and free of perfume or stains.',
              'Original tags, barcode label and packaging are attached.',
              'You have the order number (for example #WB-10482) or the invoice.',
            ],
          },
        ],
      },
      {
        heading: '3. Items we can’t take back',
        blocks: [
          {
            type: 'list',
            items: [
              'Products marked "Final sale" during flash sales.',
              'Altered, custom-tailored or personalised items.',
              'Innerwear, socks and gift cards, for hygiene and security reasons.',
            ],
          },
        ],
      },
      {
        heading: '4. How to request',
        blocks: [
          {
            type: 'steps',
            items: [
              [
                'Open ',
                { label: 'My orders', to: '/account?tab=orders' },
                ' or ',
                { label: 'Track order', to: '/track-order' },
                ' and choose "Request return / exchange".',
              ],
              'Select the item, the reason and the new size or colour. Add a photo if the item is damaged.',
              'Hand the parcel to the courier rider at pickup, or drop it at our shop in Dhaka.',
              'We check the item within 2 working days and send the replacement or refund. You get an SMS at every step.',
            ],
          },
        ],
      },
      {
        heading: '5. Delivery charges and refunds',
        blocks: [
          {
            type: 'table',
            columns: ['Reason', 'Return shipping', 'Refund method'],
            rows: [
              ['Size or colour exchange', `You pay ${formatBDT(inside.fee)} ${inside.name} / ${formatBDT(outside.fee)} outside`, 'Replacement item'],
              ['Damaged, defective or wrong item', 'Free — we cover it', 'Full refund or replacement'],
              ['Changed your mind (unworn)', 'You pay delivery both ways', 'Store credit'],
            ],
          },
          {
            type: 'p',
            text: 'Cash on Delivery orders are refunded to your bKash or Nagad number, or by bank transfer, within 5–7 working days after we receive the item. The original delivery charge is refunded only when the mistake was ours.',
          },
        ],
      },
    ],
    faqTitle: 'Frequently asked questions',
    faqs: returnFaqs,
    faqDefaultOpen: 1,
    contact: true,
  },

  /* --------------------------------------------------------------- Shipping */
  shipping: {
    eyebrow: 'Help centre',
    title: 'Shipping & delivery',
    navLabel: 'Shipping info',
    intro: `We deliver all over Bangladesh with ${delivery.courier}. Pay cash when the parcel arrives, or pay online at checkout.`,
    highlights: [
      { value: formatBDT(inside.fee), text: `${inside.name} · ${inside.eta}`, accent: true },
      { value: formatBDT(outside.fee), text: `${outside.name} · ${outside.eta}` },
      { value: 'Free', text: `delivery on orders over ${free}` },
    ],
    sections: [
      {
        heading: 'Delivery charges',
        blocks: [
          {
            type: 'table',
            columns: ['Zone', 'Delivery charge', 'Delivery time'],
            rows: [
              [inside.name, formatBDT(inside.fee), inside.eta],
              [outside.name, formatBDT(outside.fee), outside.eta],
              [`Orders over ${free}`, 'Free', 'Same as your zone'],
            ],
          },
          {
            type: 'p',
            text: 'Free delivery does not combine with coupon codes. Delivery times start when we confirm your order. [PLACEHOLDER] Note any days you don’t deliver (e.g. public holidays).',
          },
        ],
      },
      {
        heading: 'How your order travels',
        blocks: [
          {
            type: 'steps',
            items: [
              'You place the order and get an SMS with your order number.',
              'We confirm it by phone call, then pack it at our warehouse.',
              `We hand it to ${delivery.courier} and SMS you the consignment ID.`,
              ['A rider is assigned on delivery day. Follow every step on the ', { label: 'Track order', to: '/track-order' }, ' page.'],
            ],
          },
        ],
      },
      {
        heading: 'Cash on Delivery',
        blocks: [
          {
            type: 'list',
            items: [
              'Pay the rider in cash when the parcel arrives. Please keep the exact amount ready.',
              '[PLACEHOLDER] Say whether customers may open the parcel before paying.',
              '[PLACEHOLDER] State your policy for refused or failed deliveries (for example, whether the delivery charge is payable).',
            ],
          },
        ],
      },
    ],
    contact: true,
  },

  /* ---------------------------------------------------------------- Privacy */
  privacy: {
    eyebrow: 'Legal',
    title: 'Privacy policy',
    updated: '[PLACEHOLDER: date]',
    intro: 'This page explains what personal information we collect when you shop with us and how we use it.',
    notice: legalNotice,
    sections: [
      {
        heading: '1. What we collect',
        blocks: [
          {
            type: 'list',
            items: [
              'Contact details you give us: name, mobile number, email (optional) and delivery addresses.',
              'Order details: products, sizes, payment method and delivery status.',
              'Account details: your login and preferences such as SMS or email updates.',
              'Basic technical data such as browser type and pages visited, to keep the site working and secure.',
            ],
          },
        ],
      },
      {
        heading: '2. How we use it',
        blocks: [
          {
            type: 'list',
            items: [
              'To confirm, deliver and support your orders, exchanges and refunds.',
              'To send order updates by SMS and, if you opt in, offers and new arrivals.',
              'To prevent fraud and improve the store.',
            ],
          },
        ],
      },
      {
        heading: '3. Who we share it with',
        blocks: [
          {
            type: 'p',
            text: `Only with partners who help us complete your order — for example ${delivery.courier} for delivery and payment providers for online payments. We do not sell your personal information. [PLACEHOLDER] List every third-party service you use (SMS gateway, analytics, payment gateway).`,
          },
        ],
      },
      {
        heading: '4. Cookies and local storage',
        blocks: [
          {
            type: 'p',
            text: 'Your cart and wishlist are saved in your browser so they are still there when you come back. You can clear them at any time in your browser settings.',
          },
        ],
      },
      {
        heading: '5. Your choices',
        blocks: [
          {
            type: 'p',
            text: [
              'You can update your details and notification preferences in ',
              { label: 'My account', to: '/account?tab=profile' },
              `, or ask us to delete your account by emailing ${contact.email}. [PLACEHOLDER] State how long you keep order records.`,
            ],
          },
        ],
      },
    ],
    contact: true,
  },

  /* ------------------------------------------------------------------ Terms */
  terms: {
    eyebrow: 'Legal',
    title: 'Terms & conditions',
    updated: '[PLACEHOLDER: date]',
    intro: 'These terms apply when you browse or buy from our website. By placing an order you agree to them.',
    notice: legalNotice,
    sections: [
      {
        heading: '1. Orders',
        blocks: [
          {
            type: 'list',
            items: [
              'An order is confirmed when we call or SMS you to confirm it. We may cancel an order if a product is out of stock or a price was shown incorrectly, and we will tell you if we do.',
              'Product colours may look slightly different on different screens.',
            ],
          },
        ],
      },
      {
        heading: '2. Prices and payment',
        blocks: [
          {
            type: 'p',
            text: `Prices are in Bangladeshi Taka (৳) and include [PLACEHOLDER: VAT statement]. Delivery charges are shown at checkout. We accept ${paymentMethods.join(', ')}.`,
          },
        ],
      },
      {
        heading: '3. Delivery, returns and exchanges',
        blocks: [
          {
            type: 'p',
            text: [
              'See ',
              { label: 'Shipping & delivery', to: '/pages/shipping' },
              ' and the ',
              { label: 'Return & exchange policy', to: '/pages/return-policy' },
              ' for details.',
            ],
          },
        ],
      },
      {
        heading: '4. Your account',
        blocks: [
          {
            type: 'p',
            text: 'Keep your password and OTP codes private. You are responsible for orders placed from your account. Tell us straight away if you think someone else has used it.',
          },
        ],
      },
      {
        heading: '5. Business details and governing law',
        blocks: [
          {
            type: 'p',
            text: '[PLACEHOLDER] Registered business name, trade licence number, registered address and the law that governs these terms.',
          },
        ],
      },
    ],
    contact: true,
  },
};
