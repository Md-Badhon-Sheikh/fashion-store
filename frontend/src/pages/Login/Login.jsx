import { useEffect, useId, useRef, useState } from 'react';
import { Link, useNavigate, useSearchParams } from 'react-router-dom';
import Icon from '../../components/ui/Icon.jsx';
import { brand, contact } from '../../data/content.js';
import { customer } from '../../data/orders.js';
import { useDocumentTitle } from '../../hooks/useDocumentTitle.js';
import { imageUrl } from '../../utils/format.js';
import OtpInput from './OtpInput.jsx';
import PasswordField from './PasswordField.jsx';
import { isBdMobile, isEmail, maskPhone, passwordStrength } from './validation.js';
import './Login.css';

/**
 * Login / Register — Login.dc.html.
 *   /login                 Login tab (password, or "Login with OTP")
 *   /login?mode=register   Register tab
 * No real auth: a valid form "logs in" and navigates to /account.
 * Wire submit handlers to the API (POST /api/login, /api/otp, /api/register).
 */

const OTP_LENGTH = 6;
const RESEND_SECONDS = 45;
const emptyCode = () => Array(OTP_LENGTH).fill('');

const HEADINGS = {
  password: ['Welcome back', 'Log in to track orders, see your wishlist and check out faster.'],
  otp: ['Login with OTP', 'No password needed. We sent a one-time code to your phone.'],
  forgot: ['Reset your password', 'Enter your mobile number or email and we’ll send you a 6-digit code.'],
  reset: ['Verify it’s you', 'Enter the code we sent to reset your password.'],
  register: ['Create your account', 'It takes less than a minute. Your phone number is your login.'],
};

export default function Login() {
  const [params, setParams] = useSearchParams();
  const tab = params.get('mode') === 'register' ? 'register' : 'login';
  // Login sub-mode: 'password' | 'otp' | 'forgot' | 'reset' (OTP for password reset)
  const [mode, setMode] = useState('password');
  const [otpPhone, setOtpPhone] = useState(customer.phone);
  const [resendLeft, setResendLeft] = useState(RESEND_SECONDS);
  const headingRef = useRef(null);
  const tabsRef = useRef(null);

  const view = tab === 'register' ? 'register' : mode;
  const [heading, subheading] = HEADINGS[view];
  useDocumentTitle(tab === 'register' ? 'Create account' : 'Login');

  // Live resend countdown while an OTP screen is open.
  const otpOpen = tab === 'login' && (mode === 'otp' || mode === 'reset');
  useEffect(() => {
    if (!otpOpen || resendLeft <= 0) return undefined;
    const id = setTimeout(() => setResendLeft((s) => s - 1), 1000);
    return () => clearTimeout(id);
  }, [otpOpen, resendLeft]);

  const switchTab = (next) => {
    setParams(next === 'register' ? { mode: 'register' } : {}, { replace: true });
    if (next === 'login') setMode('password');
  };

  const goTo = (nextMode) => {
    setMode(nextMode);
    // Move focus to the new heading so screen readers announce the change.
    requestAnimationFrame(() => headingRef.current?.focus());
  };

  const startOtp = (phone, nextMode = 'otp') => {
    if (phone) setOtpPhone(phone);
    setResendLeft(RESEND_SECONDS);
    setMode(nextMode); // the OTP screen focuses its first digit box
  };

  const onTabKeyDown = (e) => {
    if (e.key !== 'ArrowLeft' && e.key !== 'ArrowRight' && e.key !== 'Home' && e.key !== 'End') return;
    e.preventDefault();
    const next = tab === 'login' ? 'register' : 'login';
    switchTab(next);
    tabsRef.current?.querySelector(`#tab-${next}`)?.focus();
  };

  return (
    <div className="auth">
      <div className="auth__inner">
        <section className="auth-card" aria-labelledby="auth-title">
          <div className="auth-card__head">
            <h1 id="auth-title" className="auth-card__title" tabIndex={-1} ref={headingRef}>
              {heading}
            </h1>
            <p className="auth-card__sub">{subheading}</p>
          </div>

          <div className="auth-tabs" role="tablist" aria-label="Account access" ref={tabsRef}>
            {[
              ['login', 'Login'],
              ['register', 'Register'],
            ].map(([id, label]) => (
              <button
                key={id}
                type="button"
                role="tab"
                id={`tab-${id}`}
                aria-selected={tab === id}
                aria-controls={`panel-${id}`}
                tabIndex={tab === id ? 0 : -1}
                className="auth-tabs__tab"
                onClick={() => switchTab(id)}
                onKeyDown={onTabKeyDown}
              >
                {label}
              </button>
            ))}
          </div>

          {tab === 'login' ? (
            <div role="tabpanel" id="panel-login" aria-labelledby="tab-login" className="auth-panel">
              {mode === 'password' && (
                <PasswordLogin onOtp={(phone) => startOtp(phone)} onForgot={() => goTo('forgot')} />
              )}
              {mode === 'forgot' && (
                <ForgotPassword onBack={() => goTo('password')} onSent={(phone) => startOtp(phone, 'reset')} />
              )}
              {(mode === 'otp' || mode === 'reset') && (
                <OtpLogin
                  key={mode}
                  purpose={mode}
                  phone={otpPhone}
                  resendLeft={resendLeft}
                  onResend={() => setResendLeft(RESEND_SECONDS)}
                  onChangePhone={(phone) => startOtp(phone, mode)}
                  onBack={() => goTo('password')}
                />
              )}
              <p className="auth-panel__switch">
                New to {brand.name}?{' '}
                <button type="button" className="auth-link" onClick={() => switchTab('register')}>
                  Create an account
                </button>
              </p>
            </div>
          ) : (
            <div role="tabpanel" id="panel-register" aria-labelledby="tab-register" className="auth-panel">
              <RegisterForm />
              <p className="auth-panel__switch">
                Already have an account?{' '}
                <button type="button" className="auth-link" onClick={() => switchTab('login')}>
                  Log in
                </button>
              </p>
            </div>
          )}
        </section>

        <Benefits />
      </div>
    </div>
  );
}

/* --------------------------------------------------------- password login */
function PasswordLogin({ onOtp, onForgot }) {
  const navigate = useNavigate();
  const [identifier, setIdentifier] = useState('');
  const [password, setPassword] = useState('');
  const [remember, setRemember] = useState(true);
  const [errors, setErrors] = useState({});
  const idRef = useRef(null);
  const uid = useId();

  const submit = (e) => {
    e.preventDefault();
    const next = {};
    if (!identifier.trim()) next.identifier = 'Enter your mobile number or email.';
    else if (!isBdMobile(identifier) && !isEmail(identifier))
      next.identifier = 'Enter a valid mobile number (01XXX-XXXXXX) or email address.';
    if (!password) next.password = 'Enter your password.';
    else if (password.length < 6) next.password = 'Passwords are at least 6 characters.';
    setErrors(next);
    if (next.identifier) idRef.current?.focus();
    else if (next.password) document.getElementById(`${uid}-pw`)?.focus();
    else navigate('/account');
  };

  return (
    <>
      <form className="auth-form" onSubmit={submit} noValidate>
        <div className="field">
          <label className="label" htmlFor={`${uid}-id`}>
            Phone number or email
          </label>
          <input
            ref={idRef}
            id={`${uid}-id`}
            className="input auth-input"
            type="text"
            inputMode="email"
            autoComplete="username"
            placeholder="01XXX-XXXXXX or you@email.com"
            value={identifier}
            onChange={(e) => setIdentifier(e.target.value)}
            aria-invalid={Boolean(errors.identifier) || undefined}
            aria-describedby={errors.identifier ? `${uid}-id-err` : undefined}
          />
          {errors.identifier && (
            <span id={`${uid}-id-err`} className="error-text">
              {errors.identifier}
            </span>
          )}
        </div>

        <div className="field">
          <div className="auth-form__label-row">
            <label className="label" htmlFor={`${uid}-pw`}>
              Password
            </label>
            <button type="button" className="auth-link auth-link--sm" onClick={onForgot}>
              Forgot password?
            </button>
          </div>
          <PasswordField
            id={`${uid}-pw`}
            value={password}
            onChange={setPassword}
            autoComplete="current-password"
            placeholder="Your password"
            invalid={Boolean(errors.password)}
            describedBy={errors.password ? `${uid}-pw-err` : undefined}
          />
          {errors.password && (
            <span id={`${uid}-pw-err`} className="error-text">
              {errors.password}
            </span>
          )}
        </div>

        <label className="auth-check">
          <input type="checkbox" className="checkbox" checked={remember} onChange={(e) => setRemember(e.target.checked)} />
          Keep me logged in
        </label>

        <button type="submit" className="btn btn--brand btn--block auth-submit">
          Login
        </button>
      </form>

      <div className="auth-divider" aria-hidden="true">
        <span>or</span>
      </div>

      <button
        type="button"
        className="btn btn--outline btn--block auth-alt"
        onClick={() => onOtp(isBdMobile(identifier) ? maskPhone(identifier) : null)}
      >
        <PhoneIcon />
        Login with OTP (no password)
      </button>
    </>
  );
}

/* --------------------------------------------------------- forgot password */
function ForgotPassword({ onBack, onSent }) {
  const [identifier, setIdentifier] = useState('');
  const [error, setError] = useState('');
  const uid = useId();

  const submit = (e) => {
    e.preventDefault();
    if (!identifier.trim()) return setError('Enter your mobile number or email.');
    if (!isBdMobile(identifier) && !isEmail(identifier))
      return setError('Enter a valid mobile number (01XXX-XXXXXX) or email address.');
    setError('');
    onSent(isBdMobile(identifier) ? maskPhone(identifier) : identifier.trim());
    return undefined;
  };

  return (
    <div className="auth-stack">
      <BackButton onClick={onBack}>Back to login</BackButton>
      <form className="auth-form" onSubmit={submit} noValidate>
        <div className="field">
          <label className="label" htmlFor={`${uid}-id`}>
            Mobile number or email
          </label>
          <input
            id={`${uid}-id`}
            className="input auth-input"
            type="text"
            autoComplete="username"
            placeholder="01XXX-XXXXXX or you@email.com"
            value={identifier}
            onChange={(e) => setIdentifier(e.target.value)}
            aria-invalid={Boolean(error) || undefined}
            aria-describedby={error ? `${uid}-err` : `${uid}-hint`}
          />
          {error ? (
            <span id={`${uid}-err`} className="error-text">
              {error}
            </span>
          ) : (
            <span id={`${uid}-hint`} className="hint">
              The code is valid for 5 minutes.
            </span>
          )}
        </div>
        <button type="submit" className="btn btn--brand btn--block auth-submit">
          Send reset code
        </button>
      </form>
    </div>
  );
}

/* ---------------------------------------------------------------- OTP login */
function OtpLogin({ purpose, phone, resendLeft, onResend, onChangePhone, onBack }) {
  const navigate = useNavigate();
  const [code, setCode] = useState(emptyCode);
  const [error, setError] = useState('');
  const [status, setStatus] = useState('');
  const [editingPhone, setEditingPhone] = useState(false);
  const [newPhone, setNewPhone] = useState('');
  const [phoneError, setPhoneError] = useState('');
  const uid = useId();

  const mm = String(Math.floor(resendLeft / 60)).padStart(2, '0');
  const ss = String(resendLeft % 60).padStart(2, '0');

  const verify = (e) => {
    e.preventDefault();
    if (code.some((d) => !d)) {
      setError(`Enter all ${OTP_LENGTH} digits of the code.`);
      return;
    }
    setError('');
    navigate(purpose === 'reset' ? '/account?tab=profile' : '/account');
  };

  const resend = () => {
    setCode(emptyCode());
    setError('');
    onResend();
    setStatus(`A new code has been sent to ${phone}.`);
  };

  const savePhone = (e) => {
    e.preventDefault();
    if (!isBdMobile(newPhone)) {
      setPhoneError('Enter a valid mobile number, e.g. 01712-345678.');
      return;
    }
    setPhoneError('');
    setEditingPhone(false);
    setCode(emptyCode());
    setStatus(`Code sent to ${maskPhone(newPhone)}.`);
    onChangePhone(maskPhone(newPhone));
    requestAnimationFrame(() => document.querySelector(`[id="${uid}-code"] input`)?.focus());
  };

  return (
    <div className="auth-stack">
      <BackButton onClick={onBack}>Use password instead</BackButton>

      {editingPhone ? (
        <form className="auth-otp-phone" onSubmit={savePhone} noValidate>
          <label className="label" htmlFor={`${uid}-phone`}>
            New mobile number
          </label>
          <div className="auth-otp-phone__row">
            <div className={`auth-phone${phoneError ? ' is-invalid' : ''}`}>
              <span className="auth-phone__prefix">+88</span>
              <input
                id={`${uid}-phone`}
                type="tel"
                autoComplete="tel"
                placeholder="01XXX-XXXXXX"
                value={newPhone}
                onChange={(e) => setNewPhone(e.target.value)}
                aria-invalid={Boolean(phoneError) || undefined}
                aria-describedby={phoneError ? `${uid}-phone-err` : undefined}
              />
            </div>
            <button type="submit" className="btn btn--primary">
              Send code
            </button>
          </div>
          {phoneError && (
            <span id={`${uid}-phone-err`} className="error-text">
              {phoneError}
            </span>
          )}
          <button type="button" className="auth-link auth-link--sm" onClick={() => setEditingPhone(false)}>
            Cancel
          </button>
        </form>
      ) : (
        <div className="auth-otp-sent">
          <span>
            Code sent by SMS to <strong className="tabular">{phone}</strong>
          </span>
          <button type="button" className="auth-link auth-link--sm" onClick={() => setEditingPhone(true)}>
            Change number
          </button>
        </div>
      )}

      <form className="auth-stack" onSubmit={verify} noValidate>
        <fieldset className="auth-otp-fieldset">
          <legend className="label">Enter the {OTP_LENGTH}-digit code</legend>
          <OtpInput
            id={`${uid}-code`}
            value={code}
            onChange={(next) => {
              setCode(next);
              if (error) setError('');
            }}
            length={OTP_LENGTH}
            invalid={Boolean(error)}
            describedBy={error ? `${uid}-code-err` : undefined}
            autoFocus
          />
          {error && (
            <span id={`${uid}-code-err`} className="error-text">
              {error}
            </span>
          )}
        </fieldset>

        <div className="auth-otp-resend">
          <span className="auth-otp-resend__timer">
            <Icon name="clock" size={16} />
            {resendLeft > 0 ? (
              <>
                Resend code in{' '}
                <strong className="tabular" aria-live="off">
                  {mm}:{ss}
                </strong>
              </>
            ) : (
              'Didn’t get the code?'
            )}
          </span>
          <button type="button" className="auth-link auth-link--sm" disabled={resendLeft > 0} onClick={resend}>
            Resend OTP
          </button>
        </div>
        <p className="visually-hidden" role="status" aria-live="polite">
          {status}
        </p>

        <button type="submit" className="btn btn--brand btn--block auth-submit">
          {purpose === 'reset' ? 'Verify & reset password' : 'Verify & login'}
        </button>
        <p className="auth-note">
          The code is valid for 5 minutes. Not getting the SMS? Call our hotline{' '}
          <a href={contact.phoneHref}>{contact.phone}</a>.
        </p>
      </form>
    </div>
  );
}

/* ----------------------------------------------------------------- register */
function RegisterForm() {
  const navigate = useNavigate();
  const uid = useId();
  const [values, setValues] = useState({ name: '', phone: '', email: '', password: '', terms: false, offers: true });
  const [errors, setErrors] = useState({});
  const strength = passwordStrength(values.password);

  const set = (key) => (e) => {
    const value = e.target.type === 'checkbox' ? e.target.checked : e.target.value;
    setValues((v) => ({ ...v, [key]: value }));
    if (errors[key]) setErrors((er) => ({ ...er, [key]: undefined }));
  };

  const submit = (e) => {
    e.preventDefault();
    const next = {};
    if (values.name.trim().length < 2) next.name = 'Enter your full name.';
    if (!values.phone.trim()) next.phone = 'Enter your mobile number.';
    else if (!isBdMobile(values.phone)) next.phone = 'Enter a valid Bangladeshi mobile number, e.g. 01712-345678.';
    if (values.email.trim() && !isEmail(values.email)) next.email = 'Enter a valid email address or leave it empty.';
    if (!values.password) next.password = 'Choose a password.';
    else if (!strength.valid) next.password = 'Use at least 6 characters with letters and numbers.';
    if (!values.terms) next.terms = 'Please accept the Terms & conditions and Privacy policy.';
    setErrors(next);
    const first = ['name', 'phone', 'email', 'password', 'terms'].find((k) => next[k]);
    if (first) document.getElementById(`${uid}-${first}`)?.focus();
    else navigate('/account');
  };

  const err = (key) =>
    errors[key] && (
      <span id={`${uid}-${key}-err`} className="error-text">
        {errors[key]}
      </span>
    );
  const a11y = (key, hintId) => ({
    'aria-invalid': Boolean(errors[key]) || undefined,
    'aria-describedby': errors[key] ? `${uid}-${key}-err` : hintId,
  });

  return (
    <form className="auth-form" onSubmit={submit} noValidate>
      <div className="field">
        <label className="label" htmlFor={`${uid}-name`}>
          Full name <span aria-hidden="true">*</span>
        </label>
        <input
          id={`${uid}-name`}
          className="input auth-input"
          type="text"
          autoComplete="name"
          placeholder="Your name"
          value={values.name}
          onChange={set('name')}
          required
          {...a11y('name')}
        />
        {err('name')}
      </div>

      <div className="field">
        <label className="label" htmlFor={`${uid}-phone`}>
          Mobile number <span aria-hidden="true">*</span>
        </label>
        <div className={`auth-phone${errors.phone ? ' is-invalid' : ''}`}>
          <span className="auth-phone__prefix">+88</span>
          <input
            id={`${uid}-phone`}
            type="tel"
            autoComplete="tel"
            placeholder="01XXX-XXXXXX"
            value={values.phone}
            onChange={set('phone')}
            required
            {...a11y('phone', `${uid}-phone-hint`)}
          />
        </div>
        {errors.phone ? (
          err('phone')
        ) : (
          <span id={`${uid}-phone-hint`} className="hint auth-hint-xs">
            We’ll send a 6-digit OTP to verify this number.
          </span>
        )}
      </div>

      <div className="field">
        <label className="label" htmlFor={`${uid}-email`}>
          Email <span className="auth-optional">(optional, for invoices)</span>
        </label>
        <input
          id={`${uid}-email`}
          className="input auth-input"
          type="email"
          autoComplete="email"
          placeholder="you@email.com"
          value={values.email}
          onChange={set('email')}
          {...a11y('email')}
        />
        {err('email')}
      </div>

      <div className="field">
        <label className="label" htmlFor={`${uid}-password`}>
          Password <span aria-hidden="true">*</span>
        </label>
        <PasswordField
          id={`${uid}-password`}
          value={values.password}
          onChange={(v) => {
            setValues((s) => ({ ...s, password: v }));
            if (errors.password) setErrors((er) => ({ ...er, password: undefined }));
          }}
          autoComplete="new-password"
          placeholder="At least 6 characters"
          invalid={Boolean(errors.password)}
          describedBy={errors.password ? `${uid}-password-err` : `${uid}-password-hint`}
          required
        />
        <div className={`auth-meter auth-meter--${strength.tone}`} aria-hidden="true">
          {[1, 2, 3, 4].map((n) => (
            <span key={n} className={n <= strength.score ? 'is-on' : undefined} />
          ))}
        </div>
        {errors.password ? (
          err('password')
        ) : (
          <span id={`${uid}-password-hint`} className={`auth-pw-hint auth-pw-hint--${strength.tone}`} aria-live="polite">
            {strength.label ? `${strength.label} password · ` : ''}at least 6 characters, mix letters and numbers
          </span>
        )}
      </div>

      <div className="field">
        <label className="auth-check auth-check--top">
          <input
            id={`${uid}-terms`}
            type="checkbox"
            className="checkbox"
            checked={values.terms}
            onChange={set('terms')}
            {...a11y('terms')}
          />
          <span>
            I agree to the{' '}
            <Link to="/pages/terms" className="link-underline">
              Terms &amp; conditions
            </Link>{' '}
            and{' '}
            <Link to="/pages/privacy" className="link-underline">
              Privacy policy
            </Link>
          </span>
        </label>
        {err('terms')}
      </div>

      <label className="auth-check">
        <input type="checkbox" className="checkbox" checked={values.offers} onChange={set('offers')} />
        Send me offers and new arrivals by SMS
      </label>

      <button type="submit" className="btn btn--brand btn--block auth-submit">
        Create account
      </button>
    </form>
  );
}

/* ------------------------------------------------------------- benefits */
const BENEFITS = [
  { icon: <Icon name="truck" size={20} />, title: 'Track orders', text: 'Live status from confirmation to your door, with courier ID.' },
  { icon: <Icon name="heart" size={20} />, title: 'Save a wishlist', text: 'Keep favourites and get an SMS when they go on sale.' },
  { icon: <BoltIcon />, title: 'Faster checkout', text: 'Saved addresses and phone, order in a few taps.' },
  { icon: <Icon name="tag" size={20} />, title: 'Member-only offers', text: 'Early access to flash sales and Eid collections.' },
];

function Benefits() {
  return (
    <aside className="auth-benefits" aria-labelledby="benefits-title">
      <div className="auth-benefits__media media">
        <img src={imageUrl('promo1')} alt="Customers wearing the new collection" loading="lazy" decoding="async" />
      </div>
      <div className="auth-benefits__body">
        <div className="auth-benefits__head">
          <span className="auth-benefits__eyebrow">Member benefits</span>
          <h2 id="benefits-title" className="auth-benefits__title">
            One account for every order
          </h2>
        </div>
        <ul role="list" className="auth-benefits__list">
          {BENEFITS.map((b) => (
            <li key={b.title} className="auth-benefits__item">
              <span className="auth-benefits__icon">{b.icon}</span>
              <span>
                <strong className="auth-benefits__item-title">{b.title}</strong>
                <span className="auth-benefits__item-text">{b.text}</span>
              </span>
            </li>
          ))}
        </ul>
      </div>
    </aside>
  );
}

/* ---------------------------------------------------------------- bits */
function BackButton({ onClick, children }) {
  return (
    <button type="button" className="auth-back" onClick={onClick}>
      <Icon name="chevron-left" size={16} strokeWidth={2} />
      {children}
    </button>
  );
}

/** Page-local icons (not in the shared Icon set). */
function PhoneIcon() {
  return (
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinejoin="round" aria-hidden="true" focusable="false">
      <rect x="6" y="2" width="12" height="20" rx="2.5" />
      <path d="M10 18h4" />
    </svg>
  );
}

function BoltIcon() {
  return (
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true" focusable="false">
      <path d="M13 3L5 14h6l-1 7 8-11h-6l1-7z" />
    </svg>
  );
}
