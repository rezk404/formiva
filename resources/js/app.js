import gsap from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import Lenis from 'lenis';
import { initWorld } from './three/director.js';
import { initServices } from './chapters/services.js';
import { initCursor } from './core/cursor.js';
import { initContactForm } from './core/contact.js';

gsap.registerPlugin(ScrollTrigger);

const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
const touch = matchMedia('(pointer: coarse)').matches;

function initSmoothScroll() {
    if (reducedMotion || touch) return null;
    const lenis = new Lenis({ duration: 0.9, smoothWheel: true, syncTouch: false });
    lenis.on('scroll', ScrollTrigger.update);
    gsap.ticker.add((time) => lenis.raf(time * 1000));
    gsap.ticker.lagSmoothing(0);
    return lenis;
}

function initMobileMenu() {
    const menu = document.querySelector('.fv-mobile');
    const toggles = document.querySelectorAll('[data-menu-toggle]');
    if (!menu || !toggles.length) return;
    const setOpen = (open) => {
        menu.classList.toggle('is-open', open);
        menu.setAttribute('aria-hidden', String(!open));
        toggles.forEach((button) => button.setAttribute('aria-expanded', String(open)));
        document.body.classList.toggle('is-locked', open);
    };
    toggles.forEach((button) => button.addEventListener('click', () => setOpen(!menu.classList.contains('is-open'))));
    menu.querySelectorAll('a').forEach((link) => link.addEventListener('click', () => setOpen(false)));
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && menu.classList.contains('is-open')) setOpen(false);
    });
}

function initNavTheme() {
    const nav = document.querySelector('.fv-nav');
    if (!nav) return;
    const sections = [...document.querySelectorAll('[data-theme]')];
    const navLinks = [...nav.querySelectorAll('.fv-nav__links a')];
    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (!entry.isIntersecting) return;
            const theme = entry.target.dataset.theme;
            nav.classList.toggle('is-light', theme === 'light');
            navLinks.forEach((link) => {
                const href = link.getAttribute('href') || '';
                const isCurrent = href.endsWith(`#${entry.target.id}`) || (entry.target.id === 'about' && href.endsWith('#about'));
                link.classList.toggle('is-current', isCurrent);
                if (isCurrent) link.setAttribute('aria-current', 'location');
                else link.removeAttribute('aria-current');
            });
        });
    }, { rootMargin: '-35% 0px -55% 0px', threshold: 0 });
    sections.forEach((section) => observer.observe(section));
}

function normalizeHomeLinks() {
    // The same navigation is rendered on detail pages. Relative fragments
    // belong to the homepage, so resolve them once without duplicating nav.
    document.querySelectorAll('.fv-nav__brand, .fv-nav__cta').forEach((link) => {
        const fragment = link.getAttribute('href');
        if (fragment?.startsWith('#')) link.setAttribute('href', `/${fragment}`);
    });
    document.querySelectorAll('.fv-nav__cta, .fv-mobile a[href$="#contact"]').forEach((link) => {
        link.setAttribute('href', '/start-a-project');
    });
}

function initReveal() {
    if (reducedMotion) return;
    const groups = document.querySelectorAll('.fv-service, .fv-project, .fv-insight, .fv-case-step, .fv-stat');
    groups.forEach((el) => {
        gsap.fromTo(el, { y: 28, opacity: 0 }, {
            y: 0, opacity: 1, duration: 0.8, ease: 'power3.out',
            scrollTrigger: { trigger: el, start: 'top 88%', once: true }
        });
    });
}

function initHeroMotion() {
    const heroFrame = document.querySelector('.fv-hero__frame');
    if (!heroFrame || reducedMotion || touch) return;
    window.addEventListener('pointermove', (event) => {
        const x = (event.clientX / window.innerWidth - 0.5) * 2;
        const y = (event.clientY / window.innerHeight - 0.5) * 2;
        gsap.to(heroFrame, { rotateY: x * 4, rotateX: y * -3, duration: 0.8, ease: 'power3.out', overwrite: true });
    }, { passive: true });
}

function boot() {
    document.documentElement.classList.add('js');
    initSmoothScroll();
    initMobileMenu();
    normalizeHomeLinks();
    initNavTheme();
    initReveal();
    initHeroMotion();
    initCursor();
    initContactForm();
    const director = initWorld();
    initServices(director);
    window.addEventListener('load', () => ScrollTrigger.refresh(), { once: true });
}

if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot, { once: true });
else boot();
