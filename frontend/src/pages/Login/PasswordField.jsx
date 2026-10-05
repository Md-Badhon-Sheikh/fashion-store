import { useState } from 'react';
import Icon from '../../components/ui/Icon.jsx';

/**
 * Password input with a show/hide toggle inside the field.
 *   <PasswordField id="login-pw" value={pw} onChange={setPw} autoComplete="current-password" />
 * The label is rendered by the parent (so it can sit next to "Forgot password?").
 */
export default function PasswordField({
  id,
  value,
  onChange,
  autoComplete,
  placeholder,
  invalid = false,
  describedBy,
  required = false,
}) {
  const [visible, setVisible] = useState(false);
  return (
    <div className={`auth-pw${invalid ? ' is-invalid' : ''}`}>
      <input
        id={id}
        className="auth-pw__input"
        type={visible ? 'text' : 'password'}
        autoComplete={autoComplete}
        placeholder={placeholder}
        value={value}
        onChange={(e) => onChange(e.target.value)}
        aria-invalid={invalid || undefined}
        aria-describedby={describedBy}
        required={required}
      />
      <button
        type="button"
        className="auth-pw__toggle"
        onClick={() => setVisible((v) => !v)}
        aria-label={visible ? 'Hide password' : 'Show password'}
        aria-pressed={visible}
        aria-controls={id}
      >
        <Icon name={visible ? 'eye-off' : 'eye'} size={20} />
      </button>
    </div>
  );
}
