import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import Icon from './Icon.jsx';

/**
 * Pill search field (`.search-pill` in global.css). Submits to /shop?q=<term>.
 * Props: id (input id, required for the label), placeholder, className,
 *        autoFocus, onSubmitted (called after navigation, e.g. to close a panel).
 */
export default function SearchForm({ id, placeholder = 'Search panjabi, shirt, SKU…', className = '', autoFocus = false, onSubmitted }) {
  const navigate = useNavigate();
  const [q, setQ] = useState('');
  return (
    <form
      role="search"
      className={`search-pill ${className}`.trim()}
      onSubmit={(e) => {
        e.preventDefault();
        const term = q.trim();
        navigate(term ? `/shop?q=${encodeURIComponent(term)}` : '/shop');
        onSubmitted?.();
      }}
    >
      <Icon name="search" size={18} strokeWidth={2} />
      <label htmlFor={id} className="visually-hidden">
        Search products
      </label>
      <input
        id={id}
        type="search"
        placeholder={placeholder}
        value={q}
        onChange={(e) => setQ(e.target.value)}
        autoFocus={autoFocus}
        enterKeyHint="search"
      />
    </form>
  );
}
