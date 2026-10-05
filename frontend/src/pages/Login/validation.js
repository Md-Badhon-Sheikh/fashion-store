/**
 * Client-side checks for the Login / Register forms. The server must repeat
 * every check — these only give fast feedback.
 */

/** Bangladeshi mobile: 01[3-9]XXXXXXXX, optionally prefixed with 88 / +88. */
export function isBdMobile(value) {
  const digits = String(value).replace(/[\s-]/g, '');
  return /^(?:\+?88)?01[3-9]\d{8}$/.test(digits);
}

export function isEmail(value) {
  return /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(String(value).trim());
}

/** "01712345678" → "017•••••678" (how numbers are shown on screen). */
export function maskPhone(value) {
  const digits = String(value).replace(/\D/g, '').replace(/^88/, '');
  if (digits.length < 11) return value;
  return `${digits.slice(0, 3)}•••••${digits.slice(-3)}`;
}

/**
 * Password strength 0–4 with a label and tone for the meter.
 * Rule shown to the user: at least 6 characters, mix letters and numbers.
 */
export function passwordStrength(pw) {
  if (!pw) return { score: 0, label: '', tone: 'none' };
  const longEnough = pw.length >= 6;
  const mixed = /[a-z]/i.test(pw) && /\d/.test(pw);
  let score = 0;
  if (longEnough) score += 1;
  if (mixed) score += 1;
  if (pw.length >= 8) score += 1;
  if (pw.length >= 10 && (/[^a-z0-9]/i.test(pw) || (/[a-z]/.test(pw) && /[A-Z]/.test(pw)))) score += 1;
  if (!longEnough || !mixed) score = Math.min(score, 1);
  score = Math.max(score, 1);
  const map = {
    1: { label: 'Too weak', tone: 'weak' },
    2: { label: 'Fair', tone: 'fair' },
    3: { label: 'Good', tone: 'good' },
    4: { label: 'Strong', tone: 'good' },
  };
  return { score, ...map[score], valid: longEnough && mixed };
}
