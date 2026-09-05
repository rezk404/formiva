import {
    gsap,
    ScrollTrigger,
    initScroll,
    initAnchors,
    settleInitialHash,
    lockScroll,
    unlockScroll,
    refreshScroll,
} from './core/scroll.js';
import { initServices } from './chapters/services.js';
import { initCursor } from './core/cursor.js';
import { initContactForm } from './core/contact.js';
import { qs, qsa, on, focusables, fontsReady } from './lib/dom.js';
import { env } from './lib/env.js';

/**
 * Bootstrap.
 *
 * Everything below is an enhancement over a page that already works: the
 * document is readable, navigable and submittable with none of it running.
 * Each piece asks lib/env.js whether it is welcome before it does anything.
 */

function initMobileMenu() {
    const menu = qs('.fv-mobile');
    const toggles = qsa('[data-menu-toggle]');
    if (!menu || !toggles.length) return;

    let opener = null;

    const isOpen = () => menu.classList.contains('is-open');

    const setOpen = (open) => {
        if (open === isOpen()) return;

        menu.classList.toggle('is-open', open);
        menu.setAttribute('aria-hidden', String(!open));
        toggles.forEach((button) => button.setAttribute('aria-expanded', String(open)));

        if (open) {
            opener = document.activeElement;
            // Reference-counted, and it stops Lenis as well as the document —
            // `overflow: hidden` alone does not hold a smooth-scrolled page.
            lockScroll();
            focusables(menu)[0]?.focus();
        } else {
            unlockScroll();
            // Return the keyboard where it came from, not to the top of the
            // document.
            if (opener instanceof HTMLElement) opener.focus();
            opener = null;
        }
    };

    toggles.forEach((button) => on(button, 'click', () => setOpen(!isOpen())));

    // Delegated: the panel's links are the only things inside it that should
    // close it, and they may be re-rendered.
    on(menu, 'click', (event) => {
        if (event.target instanceof Element && event.target.closest('a')) setOpen(false);
    });

    on(document, 'keydown', (event) => {
        if (!isOpen()) return;

        if (event.key === 'Escape') {
            setOpen(false);
            return;
        }

        if (event.key !== 'Tab') return;

        // A panel covering the page must not leak focus to the document
        // behind it.
        const items = focusables(menu);
        if (!items.length) return;

        const first = items[0];
        const last = items[items.length - 1];

        if (event.shiftKey && document.activeElement === first) {
            event.preventDefault();
            last.focus();
        } else if (!event.shiftKey && document.activeElement === last) {
            event.preventDefault();
            first.focus();
        }
    });
}

/**
 * The nav inverts to match whichever chapter is behind it. Which link is
 * "current" is a property of the page being viewed, not scroll position —
 * navbar.blade.php sets that server-side from the route name — so this only
 * ever has one job.
 */
function initNavTheme() {
    const nav = qs('.fv-nav');
    if (!nav) return;

    const sections = qsa('[data-theme]');
    if (!sections.length) return;

    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;
                nav.classList.toggle('is-light', entry.target.dataset.theme === 'light');
            });
        },
        { rootMargin: '-35% 0px -55% 0px', threshold: 0 }
    );

    sections.forEach((section) => observer.observe(section));
}

function initReveal() {
    if (!env.animate) return;

    qsa('.fv-service, .fv-project, .fv-insight, .fv-case-step, .fv-stat, .fv-trust__row, .fv-intro__note').forEach((el) => {
        gsap.fromTo(
            el,
            { y: 14, opacity: 0 },
            {
                y: 0,
                opacity: 1,
                duration: 0.5,
                ease: 'power2.out',
                scrollTrigger: { trigger: el, start: 'top 90%', once: true },
            }
        );
    });
}

/**
 * The frame is loaded only where it can actually be seen.
 *
 * Three.js is by far the heaviest thing this site can download, and most
 * pages never show the canvas — they paint an opaque ground over it. Those
 * pages should not pay for it at all, so the import is dynamic and gated on
 * the same attribute the CSS uses to clear a ground for it.
 */
async function initFrame(services) {
    if (!qs('[data-world-visible]')) return;

    try {
        const { initWorld } = await import('./three/director.js');
        const director = initWorld();

        // The accordion was wired up long before this resolved; hand it the
        // director so the open pillar's geometry catches up.
        if (director) services?.attach(director);
    } catch {
        // A failed chunk load is not a broken page: every chapter reads
        // perfectly well without the object behind it.
    }
}

function boot() {
    document.documentElement.classList.add('js');

    initScroll();
    initAnchors();
    initMobileMenu();
    initNavTheme();
    initReveal();
    initCursor();
    initContactForm();

    // The accordion must work the moment the page does, so it is wired up
    // now and handed the director later, if and when the 3D chunk arrives.
    const services = initServices();
    initFrame(services);

    // Triggers are measured against the wrong layout until the webfont has
    // swapped in — Archivo is a variable font and settles noticeably late.
    fontsReady().then(() => {
        refreshScroll();
        settleInitialHash();
    });

    on(window, 'load', () => refreshScroll(), { once: true });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot, { once: true });
} else {
    boot();
}
