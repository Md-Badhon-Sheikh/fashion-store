import { Link } from 'react-router-dom';

/** "By placing the order you agree to our Terms and Return policy…" */
export default function TermsNote({ className = '' }) {
  return (
    <p className={`co-terms ${className}`.trim()}>
      By placing the order you agree to our <Link to="/pages/terms">Terms</Link> and{' '}
      <Link to="/pages/return-policy">Return policy</Link>. You’ll get an SMS and email confirmation.
    </p>
  );
}
