import { useEffect } from 'react';
import { brand } from '../data/content.js';

/**
 * Sets document.title to "<title> — YOUR BRAND" (or the store default).
 *   useDocumentTitle('Cart');
 */
export function useDocumentTitle(title) {
  useEffect(() => {
    document.title = title ? `${title} — ${brand.name}` : `${brand.name} — Fashion Store`;
  }, [title]);
}
