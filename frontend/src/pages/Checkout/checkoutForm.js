/**
 * Checkout form helpers: initial values, client-side validation and the
 * order object handed to /order-success (same shape as `sampleOrder` in
 * data/orders.js). When the API exists, POST the form + cart and use the
 * order the server returns instead of buildOrder().
 */
import { delivery } from '../../data/content.js';

export const initialForm = {
  phone: '',
  email: '',
  name: '',
  district: 'Dhaka',
  area: '',
  address: '',
  saveAddress: true,
  billingSame: true,
  billingName: '',
  billingDistrict: 'Dhaka',
  billingArea: '',
  billingAddress: '',
  note: '',
};

/** Field order = focus order for the first error. Ids are `co-<field>`. */
export const FIELD_ORDER = [
  'phone',
  'email',
  'name',
  'district',
  'area',
  'address',
  'billingName',
  'billingDistrict',
  'billingArea',
  'billingAddress',
];

export const fieldId = (field) => `co-${field}`;

/** "+880 1712-345678" / "01712 345678" → "01712345678" (or the raw digits when it doesn't parse). */
export function normalizePhone(value) {
  let digits = String(value || '').replace(/\D/g, '');
  // Drop the +88 country code: 8801712345678 → 01712345678
  if (digits.length === 13 && digits.startsWith('88')) digits = digits.slice(2);
  return digits;
}

/** "01712345678" → "017•••••678" (as printed on the confirmation). */
export function maskPhone(phone) {
  const d = normalizePhone(phone);
  return d.length >= 6 ? `${d.slice(0, 3)}•••••${d.slice(-3)}` : d;
}

const BD_MOBILE = /^01[3-9]\d{8}$/;
const EMAIL = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;

function validateAddress(errors, form, keys) {
  if (form[keys.name].trim().length < 2) errors[keys.name] = 'Enter the recipient’s full name.';
  if (!form[keys.district]) errors[keys.district] = 'Choose a district.';
  if (!form[keys.area]) errors[keys.area] = 'Choose an area / thana.';
  if (form[keys.address].trim().length < 6) errors[keys.address] = 'Enter the full address: house, road and block.';
}

export const SHIPPING_KEYS = { name: 'name', district: 'district', area: 'area', address: 'address' };
export const BILLING_KEYS = {
  name: 'billingName',
  district: 'billingDistrict',
  area: 'billingArea',
  address: 'billingAddress',
};

/** Returns { [field]: message } — empty object when the form is valid. */
export function validate(form) {
  const errors = {};
  const phone = normalizePhone(form.phone);
  if (!phone) errors.phone = 'Enter your mobile number.';
  else if (!BD_MOBILE.test(phone)) errors.phone = 'Enter a valid 11-digit mobile number, e.g. 01712345678.';

  const email = form.email.trim();
  if (email && !EMAIL.test(email)) errors.email = 'Enter a valid email address, e.g. you@email.com.';

  validateAddress(errors, form, SHIPPING_KEYS);
  if (!form.billingSame) validateAddress(errors, form, BILLING_KEYS);
  return errors;
}

// ---------------------------------------------------------------- order

const WEEKDAYS = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
const MONTHS = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
const DAY_MS = 24 * 60 * 60 * 1000;

const dayLabel = (d) => `${WEEKDAYS[d.getDay()]} ${d.getDate()} ${MONTHS[d.getMonth()]}`;

function timeLabel(d) {
  const h = d.getHours() % 12 || 12;
  const m = String(d.getMinutes()).padStart(2, '0');
  return `${h}:${m} ${d.getHours() < 12 ? 'AM' : 'PM'}`;
}

/** "Mon 5 – Tue 6 Oct 2026" (month/year only once when shared) */
function windowLabel(from, to) {
  const sameYear = from.getFullYear() === to.getFullYear();
  const sameMonth = sameYear && from.getMonth() === to.getMonth();
  const start = sameMonth
    ? `${WEEKDAYS[from.getDay()]} ${from.getDate()}`
    : `${dayLabel(from)}${sameYear ? '' : ` ${from.getFullYear()}`}`;
  return `${start} – ${dayLabel(to)} ${to.getFullYear()}`;
}

/** Days from the zone's ETA text ("1–2 working days" → [1, 2]). */
function etaDays(zone) {
  const nums = (zone.eta.match(/\d+/g) || []).map(Number);
  return [nums[0] ?? 1, nums[1] ?? nums[0] ?? 2];
}

/**
 * Build the confirmation order from the validated form + CartContext values.
 * `cart` is the useCart() value.
 */
export function buildOrder(form, cart, now = new Date()) {
  const { lines, count, subtotal, coupon, discount, zone, deliveryFee, total } = cart;
  const [minDays, maxDays] = etaDays(zone);
  const masked = maskPhone(form.phone);
  const addressLines = [form.address.trim(), `${form.area}, ${form.district}, Bangladesh`];

  return {
    number: `WB-${10483 + (Math.floor(now.getTime() / 1000) % 9000)}`,
    placedAt: `${WEEKDAYS[now.getDay()]}, ${now.getDate()} ${MONTHS[now.getMonth()]} ${now.getFullYear()} · ${timeLabel(now)}`,
    placedDate: `${now.getDate()} ${MONTHS[now.getMonth()]} ${now.getFullYear()}`,
    expectedWindow: windowLabel(new Date(now.getTime() + minDays * DAY_MS), new Date(now.getTime() + maxDays * DAY_MS)),
    status: 'pending',
    statusOnPlacement: 'Pending confirmation',
    payment: { method: 'cod', label: 'Cash on Delivery', short: 'COD' },
    items: lines.map((l) => ({
      productId: l.productId,
      slug: l.product.slug,
      name: l.name,
      image: l.image,
      tone: l.tone,
      size: l.size,
      colour: l.colour,
      unitPrice: l.unitPrice,
      qty: l.qty,
    })),
    itemCount: count,
    subtotal,
    coupon: coupon?.valid ? coupon.code : null,
    discount: coupon?.valid ? discount : 0,
    deliveryZone: zone.id,
    deliveryZoneName: zone.name,
    deliveryFee,
    total,
    address: { name: form.name.trim(), phone: masked, lines: addressLines },
    billingAddress: form.billingSame
      ? null
      : {
          name: form.billingName.trim(),
          lines: [form.billingAddress.trim(), `${form.billingArea}, ${form.billingDistrict}, Bangladesh`],
        },
    contact: { phone: masked, email: form.email.trim() || null },
    note: form.note.trim(),
    courier: { name: delivery.courier },
    guest: true,
  };
}
