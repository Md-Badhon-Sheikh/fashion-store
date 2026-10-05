/**
 * localStorage helpers that never throw (private mode, blocked storage, SSR).
 * readJSON returns `undefined` when the key has never been written, so callers
 * can tell "empty" from "never set".
 */
export function readJSON(key) {
  try {
    if (typeof window === 'undefined') return undefined;
    const raw = window.localStorage.getItem(key);
    if (raw === null) return undefined;
    return JSON.parse(raw);
  } catch {
    return undefined;
  }
}

export function writeJSON(key, value) {
  try {
    if (typeof window === 'undefined') return;
    window.localStorage.setItem(key, JSON.stringify(value));
  } catch {
    // Storage full or unavailable — the app keeps working in memory.
  }
}
