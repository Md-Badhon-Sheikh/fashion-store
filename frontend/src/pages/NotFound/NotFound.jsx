import { Link } from 'react-router-dom';
import Icon from '../../components/ui/Icon.jsx';
import SearchForm from '../../components/ui/SearchForm.jsx';
import { useDocumentTitle } from '../../hooks/useDocumentTitle.js';
import './NotFound.css';

const SUGGESTIONS = [
  { label: 'Panjabi', to: '/shop/panjabi' },
  { label: 'Kurti', to: '/shop/kurti' },
  { label: 'New arrivals', to: '/shop?filter=new' },
  { label: 'Flash sale', to: '/shop?filter=flash-sale' },
  { label: 'Track your order', to: '/track-order' },
];

export default function NotFound() {
  useDocumentTitle('Page not found');
  return (
    <section className="not-found container" aria-labelledby="nf-title">
      <p className="not-found__code" aria-hidden="true">
        404
      </p>
      <h1 id="nf-title" className="not-found__title">
        We couldn’t find that page
      </h1>
      <p className="not-found__text">
        The link may be broken or the product may no longer be available. Try searching, or pick up where most people
        start.
      </p>
      <SearchForm id="nf-search" className="not-found__search" placeholder="Search panjabi, kurti, shirt…" />
      <div className="not-found__actions">
        <Link to="/" className="btn btn--primary btn--lg">
          <Icon name="home" size={18} />
          Back to home
        </Link>
        <Link to="/shop" className="btn btn--outline btn--lg">
          Browse the shop
        </Link>
      </div>
      <nav aria-label="Popular pages" className="not-found__links">
        <ul role="list">
          {SUGGESTIONS.map((s) => (
            <li key={s.to}>
              <Link to={s.to} className="chip chip--sm">
                {s.label}
              </Link>
            </li>
          ))}
        </ul>
      </nav>
    </section>
  );
}
