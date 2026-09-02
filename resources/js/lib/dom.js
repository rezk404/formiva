/** DOM helpers. Nothing clever — just less noise at the call sites. */

export const qs = (selector, scope = document) => scope.querySelector(selector);

export const qsa = (selector, scope = document) => Array.from(scope.querySelectorAll(selector));

/** Returns an unsubscribe function, so callers can clean up in one line. */
export const on = (target, type, handler, options) => {
    if (!target) return () => {};
    target.addEventListener(type, handler, options);
    return () => target.removeEventListener(type, handler, options);
};

/**
 * Trailing-edge debounce. Used for resize work that is expensive and only
 * correct once the user has stopped dragging the window edge.
 */
export const debounce = (fn, wait = 180) => {
    let timer;
    return (...args) => {
        clearTimeout(timer);
        timer = setTimeout(() => fn(...args), wait);
    };
};

/**
 * Waits for webfonts before measuring anything.
 *
 * Line splitting has to happen against the real typeface — measuring while
 * Archivo is still swapping produces line breaks that are wrong the moment
 * it arrives. Falls back to a short timer where the API is missing, and
 * never rejects: a font that fails to load should not stop the page.
 */
export const fontsReady = () => {
    if (!document.fonts || !document.fonts.ready) {
        return new Promise((resolve) => setTimeout(resolve, 220));
    }

    return Promise.race([
        document.fonts.ready,
        new Promise((resolve) => setTimeout(resolve, 2500)),
    ]).catch(() => undefined);
};

/** Focusable descendants, in tab order. Used by the mobile menu's trap. */
export const focusables = (scope) =>
    qsa(
        'a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])',
        scope
    ).filter((el) => el.offsetParent !== null || el === document.activeElement);
