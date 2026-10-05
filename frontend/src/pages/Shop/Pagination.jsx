import Icon from '../../components/ui/Icon.jsx';

/** 1 … 4 5 6 … 12 — first, last and the neighbours of the current page. */
function pageItems(page, count) {
  if (count <= 6) return Array.from({ length: count }, (_, i) => i + 1);
  const set = new Set([1, count, page - 1, page, page + 1]);
  if (page <= 3) [2, 3, 4].forEach((n) => set.add(n));
  if (page >= count - 2) [count - 3, count - 2, count - 1].forEach((n) => set.add(n));
  const nums = [...set].filter((n) => n >= 1 && n <= count).sort((a, b) => a - b);
  const items = [];
  nums.forEach((n, i) => {
    if (i > 0 && n - nums[i - 1] > 1) items.push(`gap-${n}`);
    items.push(n);
  });
  return items;
}

/** Desktop pagination (Shop.dc.html): Previous · numbers · Next. */
export default function Pagination({ page, count, onChange }) {
  if (count <= 1) return null;
  return (
    <nav aria-label="Pagination" className="shop-pagination">
      <button type="button" className="shop-pagination__step" disabled={page <= 1} onClick={() => onChange(page - 1)}>
        <Icon name="chevron-left" size={16} strokeWidth={2} />
        Previous
      </button>
      {pageItems(page, count).map((item) =>
        typeof item === 'string' ? (
          <span key={item} className="shop-pagination__gap" aria-hidden="true">
            …
          </span>
        ) : (
          <button
            key={item}
            type="button"
            className={`shop-pagination__num${item === page ? ' is-current' : ''}`}
            aria-label={`Page ${item}`}
            aria-current={item === page ? 'page' : undefined}
            onClick={() => item !== page && onChange(item)}
          >
            {item}
          </button>
        ),
      )}
      <button type="button" className="shop-pagination__step" disabled={page >= count} onClick={() => onChange(page + 1)}>
        Next
        <Icon name="chevron-right" size={16} strokeWidth={2} />
      </button>
    </nav>
  );
}
