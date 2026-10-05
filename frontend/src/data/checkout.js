/**
 * Checkout sample data: districts → areas (thana) for the shipping address
 * selects, plus the related products shown under the cart.
 * Later: GET /api/locations (districts with their areas and delivery zone).
 *
 * `zone` is the delivery zone id from content.js `delivery.zones` that is
 * pre-selected when the customer picks the district.
 */
export const districts = [
  {
    name: 'Dhaka',
    zone: 'inside',
    areas: [
      'Mirpur',
      'Dhanmondi',
      'Uttara',
      'Mohammadpur',
      'Gulshan',
      'Banani',
      'Badda',
      'Bashundhara',
      'Motijheel',
      'Old Dhaka',
      'Khilgaon',
      'Tejgaon',
    ],
  },
  { name: 'Gazipur', zone: 'outside', areas: ['Gazipur Sadar', 'Tongi', 'Kaliakair', 'Sreepur'] },
  { name: 'Narayanganj', zone: 'outside', areas: ['Narayanganj Sadar', 'Fatullah', 'Siddhirganj', 'Rupganj'] },
  { name: 'Chattogram', zone: 'outside', areas: ['Agrabad', 'Halishahar', 'Panchlaish', 'Khulshi', 'Patenga'] },
  { name: 'Sylhet', zone: 'outside', areas: ['Zindabazar', 'Ambarkhana', 'Shahjalal Upashahar', 'Tilagor'] },
  { name: 'Rajshahi', zone: 'outside', areas: ['Boalia', 'Motihar', 'Rajpara', 'Shah Makhdum'] },
  { name: 'Khulna', zone: 'outside', areas: ['Khulna Sadar', 'Sonadanga', 'Khalishpur', 'Daulatpur'] },
];

export function getDistrict(name) {
  return districts.find((d) => d.name === name) || null;
}

/** "You may also like" under the cart (Cart artboard), topped up from the fallbacks. */
export const cartRelatedIds = ['p01', 's01', 'n01', 's03'];
export const cartRelatedFallbackIds = ['t01', 'k02', 'w02', 'c01', 'p02'];
