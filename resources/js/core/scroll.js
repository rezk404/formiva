import gsap from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import Lenis from 'lenis';
import { env } from '../lib/env.js';

gsap.registerPlugin(ScrollTrigger);

let lenis = null;

/**
 * Smooth scrolling, carefully.
 *
 * Lenis runs only where it improves things: a fine pointer driving a wheel,
 * with motion allowed. Touch devices keep their native scrolling — momentum
 * on a trackpad is a nicety, momentum on a phone is a fight with the OS.
 *
 * The integration with ScrollTrigger matters more than the easing values.
 * Lenis drives ScrollTrigger.update, and GSAP's ticker drives Lenis, so both
 * run on one clock. Two clocks produce pinned sections that drift a pixel or
 * two behind the content, which is the tell of a rushed implementation.
 */
export function initScroll() {
    if (!env.animate || env.touch || !env.finePointer) {
        // Native scrolling still needs ScrollTrigger kept in step with the
        // browser's own scroll events; that happens automatically.
        ScrollTrigger.normalizeScroll(false);
        return null;
    }

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

/** Pauses scrolling — used while the intro veil is up and the menu is open. */
export function lockScroll() {
    document.body.classList.add('is-locked');
    if (lenis) lenis.stop();
}

export function unlockScroll() {
    document.body.classList.remove('is-locked');
    if (lenis) lenis.start();
}

/**
 * One scroll-to implementation for anchors, the skip link and the menu, so
 * every internal jump behaves identically whichever route the user took.
 */
export function scrollToTarget(target, { offset = 0, immediate = false } = {}) {
    const el = typeof target === 'string' ? document.querySelector(target) : target;
    if (!el) return;

    if (lenis && !immediate) {
        lenis.scrollTo(el, { offset, duration: 1.25 });
        return;
    }

    const top = el.getBoundingClientRect().top + window.scrollY + offset;
    window.scrollTo({ top, behavior: env.animate && !immediate ? 'smooth' : 'auto' });
}

/**
 * Recalculates every trigger. Called after fonts land and after the reveal
 * pass rewrites headline markup, both of which change document height.
 */
export function refreshScroll() {
    ScrollTrigger.refresh();
}

export { gsap, ScrollTrigger };
