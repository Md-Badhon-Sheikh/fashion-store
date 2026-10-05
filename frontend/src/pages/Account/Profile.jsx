import { useId, useState } from 'react';
import { customer } from '../../data/orders.js';
import { AccountCard } from './AccountParts.jsx';

/** Profile & password — two forms side by side (stacked on phones). */
export default function Profile({ as, onNotice }) {
  return (
    <AccountCard id="profile" title="Profile & password" as={as}>
      <div className="acct-profile">
        <DetailsForm onNotice={onNotice} />
        <PasswordForm onNotice={onNotice} />
      </div>
    </AccountCard>
  );
}

function DetailsForm({ onNotice }) {
  const uid = useId();
  const [v, setV] = useState({ name: customer.name, email: '', gender: 'Prefer not to say', dob: '' });
  const [errors, setErrors] = useState({});
  const set = (key) => (e) => setV((s) => ({ ...s, [key]: e.target.value }));

  const submit = (e) => {
    e.preventDefault();
    const next = {};
    if (!v.name.trim()) next.name = 'Enter your full name.';
    if (v.email.trim() && !/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(v.email.trim())) next.email = 'Enter a valid email address.';
    setErrors(next);
    const first = Object.keys(next)[0];
    if (first) document.getElementById(`${uid}-${first}`)?.focus();
    else onNotice('Your details have been saved.');
  };

  const errProps = (key) => ({
    'aria-invalid': Boolean(errors[key]) || undefined,
    'aria-describedby': errors[key] ? `${uid}-${key}-err` : undefined,
  });
  const err = (key) =>
    errors[key] && (
      <span id={`${uid}-${key}-err`} className="error-text">
        {errors[key]}
      </span>
    );

  return (
    <form className="acct-form" onSubmit={submit} noValidate aria-labelledby={`${uid}-h`}>
      <h3 id={`${uid}-h`} className="acct-form__title">
        Personal details
      </h3>
      <div className="field">
        <label className="label" htmlFor={`${uid}-name`}>
          Full name
        </label>
        <input id={`${uid}-name`} className="input acct-input" autoComplete="name" value={v.name} onChange={set('name')} {...errProps('name')} />
        {err('name')}
      </div>
      <div className="field">
        <label className="label" htmlFor={`${uid}-phone`}>
          Mobile number
        </label>
        <div className="acct-readonly">
          <input id={`${uid}-phone`} type="tel" value={customer.phone} readOnly aria-describedby={`${uid}-phone-hint`} />
          <span className="status-chip status-chip--delivered">Verified</span>
        </div>
        <span id={`${uid}-phone-hint`} className="hint acct-hint-xs">
          To change your number, verify the new one with an OTP.
        </span>
      </div>
      <div className="field">
        <label className="label" htmlFor={`${uid}-email`}>
          Email
        </label>
        <input
          id={`${uid}-email`}
          type="email"
          className="input acct-input"
          autoComplete="email"
          placeholder="you@email.com"
          value={v.email}
          onChange={set('email')}
          {...errProps('email')}
        />
        {err('email')}
      </div>
      <div className="acct-form__grid">
        <div className="field">
          <label className="label" htmlFor={`${uid}-gender`}>
            Gender
          </label>
          <select id={`${uid}-gender`} className="select acct-input" value={v.gender} onChange={set('gender')}>
            <option>Prefer not to say</option>
            <option>Male</option>
            <option>Female</option>
          </select>
        </div>
        <div className="field">
          <label className="label" htmlFor={`${uid}-dob`}>
            Date of birth
          </label>
          <input id={`${uid}-dob`} type="date" className="input acct-input" autoComplete="bday" value={v.dob} onChange={set('dob')} />
        </div>
      </div>
      <button type="submit" className="btn btn--brand acct-form__submit">
        Save changes
      </button>
    </form>
  );
}

function PasswordForm({ onNotice }) {
  const uid = useId();
  const blank = { current: '', next: '', confirm: '' };
  const [v, setV] = useState(blank);
  const [errors, setErrors] = useState({});
  const set = (key) => (e) => setV((s) => ({ ...s, [key]: e.target.value }));

  const submit = (e) => {
    e.preventDefault();
    const next = {};
    if (!v.current) next.current = 'Enter your current password.';
    if (v.next.length < 6 || !/[a-z]/i.test(v.next) || !/\d/.test(v.next))
      next.next = 'Use at least 6 characters with letters and numbers.';
    else if (v.next === v.current) next.next = 'Choose a password you haven’t used here.';
    if (!next.next && v.confirm !== v.next) next.confirm = 'The passwords don’t match.';
    setErrors(next);
    const first = Object.keys(next)[0];
    if (first) {
      document.getElementById(`${uid}-${first}`)?.focus();
      return;
    }
    setV(blank);
    onNotice('Password updated. You’ll stay logged in on this device.');
  };

  const input = (key, label, autoComplete) => (
    <div className="field">
      <label className="label" htmlFor={`${uid}-${key}`}>
        {label}
      </label>
      <input
        id={`${uid}-${key}`}
        type="password"
        className="input acct-input"
        autoComplete={autoComplete}
        value={v[key]}
        onChange={set(key)}
        aria-invalid={Boolean(errors[key]) || undefined}
        aria-describedby={errors[key] ? `${uid}-${key}-err` : `${uid}-rule`}
      />
      {errors[key] && (
        <span id={`${uid}-${key}-err`} className="error-text">
          {errors[key]}
        </span>
      )}
    </div>
  );

  return (
    <form className="acct-form acct-form--narrow" onSubmit={submit} noValidate aria-labelledby={`${uid}-h`}>
      <h3 id={`${uid}-h`} className="acct-form__title">
        Change password
      </h3>
      {input('current', 'Current password', 'current-password')}
      {input('next', 'New password', 'new-password')}
      {input('confirm', 'Confirm new password', 'new-password')}
      <span id={`${uid}-rule`} className="hint acct-hint-xs">
        At least 6 characters. You’ll stay logged in on this device.
      </span>
      <button type="submit" className="btn btn--outline acct-form__submit">
        Update password
      </button>
    </form>
  );
}
