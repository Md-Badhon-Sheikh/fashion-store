import { useId, useState } from 'react';
import { Link } from 'react-router-dom';
import Icon from '../../components/ui/Icon.jsx';
import { orderItems } from '../../data/account.js';
import { imageUrl } from '../../utils/format.js';
import { AccountCard } from './AccountParts.jsx';

const orderFor = (productId) =>
  Object.keys(orderItems).find((no) => orderItems[no].some((l) => l.productId === productId));

const listNames = (items) => items.map((p) => p.name).join(', ');

/** Overview strip: "2 delivered items are waiting for your review…" + Write reviews. */
export function ReviewsSummary({ pending, as }) {
  const headingId = 'reviews-title';
  const Heading = as || 'h2';
  return (
    <section id="reviews" className="acct-card acct-reviews-strip" aria-labelledby={headingId}>
      <div className="acct-reviews-strip__text">
        <Heading id={headingId} className="acct-card__title">
          Reviews
        </Heading>
        <p className="text-muted">
          {pending.length > 0
            ? `${pending.length} delivered ${pending.length === 1 ? 'item is' : 'items are'} waiting for your review: ${listNames(pending)}.`
            : 'You’re all caught up — thanks for reviewing your orders.'}
        </p>
      </div>
      {pending.length > 0 && (
        <Link to="/account?tab=reviews" className="btn btn--outline btn--sm">
          Write reviews
        </Link>
      )}
    </section>
  );
}

/** Reviews tab: one short form per delivered item waiting for a review. */
export default function Reviews({ products, submitted, onSubmit, as }) {
  const pending = products.filter((p) => !submitted.includes(p.id));
  const done = products.filter((p) => submitted.includes(p.id));
  return (
    <AccountCard id="reviews" title="Reviews" as={as}>
      <p className="text-muted">
        {pending.length > 0
          ? `Tell other shoppers about fit, fabric and colour. ${pending.length} ${pending.length === 1 ? 'item is' : 'items are'} waiting for your review.`
          : 'You’re all caught up. New delivered items will show up here.'}
      </p>
      <div className="acct-review-list">
        {pending.map((p) => (
          <ReviewForm key={p.id} product={p} onSubmit={() => onSubmit(p)} />
        ))}
        {done.map((p) => (
          <div key={p.id} className="acct-review acct-review--done">
            <ReviewProduct product={p} />
            <p className="acct-review__thanks" role="status">
              <Icon name="check" size={16} strokeWidth={2.4} />
              Thanks! Your review will appear after a quick check.
            </p>
          </div>
        ))}
      </div>
    </AccountCard>
  );
}

function ReviewProduct({ product }) {
  const orderNo = orderFor(product.id);
  return (
    <div className="acct-review__product">
      <div className="acct-thumb acct-thumb--lg media" style={{ background: product.tone }}>
        <img src={imageUrl(product.image)} alt="" loading="lazy" decoding="async" />
      </div>
      <div>
        <Link to={`/product/${product.slug}`} className="acct-review__name">
          {product.name}
        </Link>
        {orderNo && <p className="acct-review__meta">Delivered · Order #{orderNo}</p>}
      </div>
    </div>
  );
}

function ReviewForm({ product, onSubmit }) {
  const uid = useId();
  const [rating, setRating] = useState(0);
  const [text, setText] = useState('');
  const [error, setError] = useState('');

  const submit = (e) => {
    e.preventDefault();
    if (!rating) {
      setError('Choose a star rating.');
      return;
    }
    setError('');
    onSubmit();
  };

  return (
    <form className="acct-review" onSubmit={submit} noValidate aria-label={`Review ${product.name}`}>
      <ReviewProduct product={product} />
      <fieldset className="acct-stars" aria-describedby={error ? `${uid}-err` : undefined}>
        <legend className="label">Your rating</legend>
        <div className="acct-stars__row">
          {[1, 2, 3, 4, 5].map((n) => (
            <label key={n} className={`acct-stars__star${n <= rating ? ' is-on' : ''}`}>
              <input
                type="radio"
                name={`${uid}-rating`}
                value={n}
                checked={rating === n}
                onChange={() => {
                  setRating(n);
                  setError('');
                }}
                className="visually-hidden"
              />
              <Icon name="star" size={26} filled={n <= rating} />
              <span className="visually-hidden">
                {n} {n === 1 ? 'star' : 'stars'}
              </span>
            </label>
          ))}
        </div>
        {error && (
          <span id={`${uid}-err`} className="error-text">
            {error}
          </span>
        )}
      </fieldset>
      <div className="field">
        <label className="label" htmlFor={`${uid}-text`}>
          Your review <span className="acct-optional">(optional)</span>
        </label>
        <textarea
          id={`${uid}-text`}
          className="textarea"
          rows={3}
          placeholder="How was the fit, fabric and colour?"
          value={text}
          onChange={(e) => setText(e.target.value)}
        />
      </div>
      <button type="submit" className="btn btn--brand btn--sm acct-review__submit">
        Submit review
      </button>
    </form>
  );
}
