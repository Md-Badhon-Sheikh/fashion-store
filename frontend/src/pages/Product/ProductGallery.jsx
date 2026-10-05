import { useRef } from 'react';
import Icon from '../../components/ui/Icon.jsx';
import WishlistButton from '../../components/ui/WishlistButton.jsx';
import { imageUrl } from '../../utils/format.js';

/**
 * Desktop gallery (Product.dc.html): thumbnail column + main photo with
 * hover zoom. The first image is always the selected colour's photo.
 *
 * Props: images (image keys), index, onSelect(i), tone, alt(i) → alt text, badge (node)
 */
export function DesktopGallery({ images, index, onSelect, tone, alt, badge }) {
  const mainRef = useRef(null);

  const onMove = (e) => {
    const el = mainRef.current;
    if (!el) return;
    const r = el.getBoundingClientRect();
    el.style.setProperty('--zx', `${((e.clientX - r.left) / r.width) * 100}%`);
    el.style.setProperty('--zy', `${((e.clientY - r.top) / r.height) * 100}%`);
  };

  return (
    <div className="pdp-gallery">
      <div className="pdp-gallery__thumbs" role="group" aria-label="Product photos">
        {images.map((key, i) => (
          <button
            key={`${key}-${i}`}
            type="button"
            className={`pdp-gallery__thumb media${i === index ? ' is-active' : ''}`}
            style={{ background: tone }}
            aria-label={`Photo ${i + 1} of ${images.length}`}
            aria-pressed={i === index}
            onClick={() => onSelect(i)}
          >
            <img src={imageUrl(key)} alt="" loading="eager" />
          </button>
        ))}
        <button type="button" className="pdp-gallery__video" disabled aria-label="Product video (coming soon)">
          <Icon name="play" size={18} filled />
          Video
        </button>
      </div>
      <div ref={mainRef} className="pdp-gallery__main media" style={{ background: tone }} onMouseMove={onMove}>
        <img key={images[index]} src={imageUrl(images[index])} alt={alt(index)} />
        {badge}
      </div>
    </div>
  );
}

/**
 * Phone gallery (M-Product.dc.html): full-bleed swipeable photo, back,
 * wishlist and share buttons, position dots and an "n / N" counter.
 *
 * Props: product, images, index, onSelect(i), tone, alt(i), badge, onBack, onShare
 */
export function MobileGallery({ product, images, index, onSelect, tone, alt, badge, onBack, onShare }) {
  const touchX = useRef(null);
  const count = images.length;
  const go = (i) => onSelect((i + count) % count);

  return (
    <section className="pdp-mgallery" aria-label="Product photos" aria-roledescription="carousel">
      <div
        className="pdp-mgallery__photo media"
        style={{ background: tone }}
        aria-roledescription="slide"
        aria-label={`${index + 1} of ${count}`}
        onTouchStart={(e) => {
          touchX.current = e.touches[0].clientX;
        }}
        onTouchEnd={(e) => {
          if (touchX.current === null || count < 2) return;
          const dx = e.changedTouches[0].clientX - touchX.current;
          touchX.current = null;
          if (Math.abs(dx) > 40) go(index + (dx < 0 ? 1 : -1));
        }}
      >
        <img key={images[index]} src={imageUrl(images[index])} alt={alt(index)} />
      </div>

      <button type="button" className="pdp-mgallery__fab pdp-mgallery__back" aria-label="Back" onClick={onBack}>
        <Icon name="chevron-left" size={20} strokeWidth={2} />
      </button>
      <div className="pdp-mgallery__actions">
        <WishlistButton product={product} className="pdp-mgallery__fab" />
        <button type="button" className="pdp-mgallery__fab" aria-label={`Share ${product.name}`} onClick={onShare}>
          <Icon name="share" size={20} />
        </button>
      </div>
      {badge}

      {count > 1 && (
        <div className="pdp-mgallery__dots">
          {images.map((key, i) => (
            <button
              key={`${key}-${i}`}
              type="button"
              className={`pdp-mgallery__dot${i === index ? ' is-active' : ''}`}
              aria-label={`Photo ${i + 1} of ${count}`}
              aria-pressed={i === index}
              onClick={() => onSelect(i)}
            >
              <span />
            </button>
          ))}
        </div>
      )}
      <span className="pdp-mgallery__counter" aria-hidden="true">
        {index + 1} / {count}
      </span>
    </section>
  );
}
