import gsap from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import Lenis from 'lenis';
import { env } from '../lib/env.js';

gsap.registerPlugin(ScrollTrigger);

/**
 * The scroll system — the single authority for it.
 *
 * Lenis runs only where it improves things: a fine pointer driving a wheel,
 * with motion allowed. Touch devices keep their native scrolling — momentum
 * on a trackpad is a nicety, momentum on a phone is a fight with the OS.
 *
 * The integration with ScrollTrigger matters more than the easing values.
 * Lenis drives ScrollTrigger.update, and GSAP's ticker drives Lenis, so both
 * run on one clock. Two clocks produce sections that drift a pixel or two
 * behind the content, which is the tell of a rushed implementation.
 */

/**
 * How far above a jump target to stop, in pixels — the fixed nav would
 * otherwise sit on top of the heading you just asked for. Mirrored by
 * `scroll-margin-top` in pages.css for native jumps (deep links, the skip
 * link, reduced motion); the two must stay in step.
 */
export const ANCHOR_OFFSET = 88;

let lenis = null;
let locks = 0;

export function initScroll() {
    if (!env.animate || env.touch || !env.finePointer) return null;

    lenis = new Lenis({
        duration: 1.05,
        // Short, shallow curve. Long inertia looks impressive in a demo and
        // makes the site feel slippery the moment you actually try to read.
        easing: (t) => Math.min(1, 1.001 - Math.pow(2, -10 * t)),
        orientation: 'vertical',
        gestureOrientation: 'vertical',
        smoothWheel: true,
        wheelMultiplier: 1,
        touchMultiplier: 1.6,
        infinite: false,
    });

    lenis.on('scroll', ScrollTrigger.update);

    gsap.ticker.add((time) => {
        lenis.raf(time * 1000);
    });

    // GSAP's lag smoothing fights Lenis after a long frame — a dropped frame
    // makes GSAP compress time while Lenis does not, and they desynchronise.
    gsap.ticker.lagSmoothing(0);

    return lenis;
}

/**
 * Scroll locking is reference-counted. Two things that both want the page
 * held still — the menu and, later, a dialog — must not be able to unlock
 * each other by closing first.
 */
export function lockScroll() {
    locks += 1;
    if (locks > 1) return;

    document.body.classList.add('is-locked');
    lenis?.stop();
}

export function unlockScroll() {
    locks = Math.max(locks - 1, 0);
    if (locks > 0) return;

    document.body.classList.remove('is-locked');
    lenis?.start();
}

/**
 * One scroll-to implementation for anchors, the skip link and deep links,
 * so every internal jump behaves identically however it was triggered.
 */
export function scrollToTarget(target, { offset = -ANCHOR_OFFSET, immediate = false } = {}) {
    const el = typeof target === 'string' ? document.querySelector(target) : target;
    if (!el) return;

    if (lenis && !immediate) {
        lenis.scrollTo(el, { offset, duration: 1.1 });
        return;
    }

    const top = el.getBoundingClientRect().top + window.scrollY + offset;
    window.scrollTo({ top, behavior: env.animate && !immediate ? 'smooth' : 'auto' });
}

/** Resolves a hash to an element, tolerating hashes that are not selectors. */
function targetFor(hash) {
    if (!hash || hash.length < 2) return null;

    try {
        return document.querySelector(hash);
    } catch {
        // `#2024`, `#a b` and friends are valid fragments but invalid
        // selectors; getElementById does not care.
        return document.getElementById(decodeURIComponent(hash.slice(1)));
    }
}

/**
 * Same-page anchors.
 *
 * Native anchor jumps and Lenis disagree — the browser sets scrollTop
 * directly, Lenis animates toward its own target, and the page ends up
 * fighting itself. Handling the click here means one scroll implementation
 * is ever in charge, and the address bar and Back button still behave.
 */
export function initAnchors() {
    document.addEventListener('click', (event) => {
        if (event.defaultPrevented || event.button !== 0) return;
        if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;

        const link = event.target instanceof Element ? event.target.closest('a[href]') : null;
        if (!link || link.target === '_blank' || link.hasAttribute('download')) return;

        let url;
        try {
            url = new URL(link.href, window.location.href);
        } catch {
            return;
        }

        // Only same-document jumps. A link to another page that happens to
        // carry a hash must navigate normally.
        if (url.origin !== window.location.origin || url.pathname !== window.location.pathname) return;

        const target = targetFor(url.hash);
        if (!target) return;

        event.preventDefault();
        scrollToTarget(target);

        if (url.hash !== window.location.hash) {
            window.history.pushState(null, '', url.hash);
        }

        // Jumping should move the keyboard as well as the viewport, or the
        // next Tab press carries on from wherever the reader was before.
        if (!target.hasAttribute('tabindex')) target.setAttribute('tabindex', '-1');
        target.focus({ preventScroll: true });
    });

    // Back/forward between fragments on the same page.
    window.addEventListener('popstate', () => {
        const target = targetFor(window.location.hash);
        if (target) scrollToTarget(target);
    });
}

/**
 * A deep link that arrives with a fragment. The browser has already jumped
 * by the time scripts run, but it jumped without the nav offset and before
 * webfonts settled the layout, so the position is wrong twice over.
 */
export function settleInitialHash() {
    const target = targetFor(window.location.hash);
    if (!target) return;

    scrollToTarget(target, { immediate: true });
}

/** Recalculates every trigger — after fonts land, and after a resize. */
export function refreshScroll() {
    ScrollTrigger.refresh();
}

export { gsap, ScrollTrigger };
