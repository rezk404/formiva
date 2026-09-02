import { qs, qsa, on } from '../lib/dom.js';
import { damp } from '../lib/math.js';
import { env } from '../lib/env.js';

/**
 * The cursor.
 *
 * A ring that trails the pointer by a few frames and reports what the thing
 * underneath will do. The lag is the whole trick: a cursor pinned exactly to
 * the pointer reads as a rendering artefact, one that arrives just behind it
 * reads as attached to the hand.
 *
 * Fine pointers only, motion permitting. Touch keeps the system cursor,
 * which is to say none at all.
 */
export function initCursor() {
    if (!env.finePointer || !env.animate) return null;

    const cursor = qs('[data-cursor-root]');
    if (!cursor) return null;

    const label = qs('.cursor__label', cursor);
    const dot = qs('.cursor__dot', cursor);

    document.documentElement.classList.add('has-cursor');

    const pointer = { x: window.innerWidth / 2, y: window.innerHeight / 2 };
    const ring = { x: pointer.x, y: pointer.y };
    const point = { x: pointer.x, y: pointer.y };

    let magnets = [];
    let raf = null;
    let last = performance.now();
    let visible = false;

    function cacheMagnets() {
        magnets = qsa('[data-magnetic]').map((el) => ({
            el,
            strength: Number(el.dataset.magnetic) || 0.32,
            rect: el.getBoundingClientRect(),
            x: 0,
            y: 0,
        }));
    }

    /**
     * Magnetic pull. Elements lean toward the pointer inside a radius set by
     * their own size, so a large button has a wide field and a small one
     * does not grab at the pointer from across the page.
     */
    function updateMagnets(dt) {
        magnets.forEach((magnet) => {
            const { rect, strength } = magnet;
            const cx = rect.left + rect.width / 2;
            const cy = rect.top + rect.height / 2;
            const radius = Math.max(rect.width, rect.height) * 0.85 + 40;

            const dx = pointer.x - cx;
            const dy = pointer.y - cy;
            const distance = Math.hypot(dx, dy);

            const inside = distance < radius;
            const targetX = inside ? dx * strength : 0;
            const targetY = inside ? dy * strength : 0;

            magnet.x = damp(magnet.x, targetX, 12, dt);
            magnet.y = damp(magnet.y, targetY, 12, dt);

            magnet.el.style.transform =
                Math.abs(magnet.x) < 0.05 && Math.abs(magnet.y) < 0.05
                    ? ''
                    : `translate3d(${magnet.x.toFixed(2)}px, ${magnet.y.toFixed(2)}px, 0)`;
        });
    }

    function frame(now) {
        const dt = Math.min((now - last) / 1000, 0.05);
        last = now;

        // The cursor must be an exact pointer substitute. Previous versions
        // intentionally trailed the ring, which looked polished in isolation
        // but felt inaccurate while navigating. Keep the visual treatment,
        // not the positional delay.
        ring.x = pointer.x;
        ring.y = pointer.y;
        point.x = pointer.x;
        point.y = pointer.y;

        cursor.style.transform = `translate3d(${pointer.x.toFixed(2)}px, ${pointer.y.toFixed(2)}px, 0)`;

        if (dot) {
            dot.style.transform = 'translate3d(0, 0, 0)';
        }

        updateMagnets(dt);

        raf = requestAnimationFrame(frame);
    }

    const offMove = on(window, 'pointermove', (event) => {
        if (event.pointerType !== 'mouse') return;

        pointer.x = event.clientX;
        pointer.y = event.clientY;

        if (!visible) {
            visible = true;
            cursor.style.opacity = '1';
        }
    }, { passive: true });

    // Delegated, so chapters can add cursor states declaratively without
    // registering anything here.
    const offOver = on(document, 'pointerover', (event) => {
        const target = event.target instanceof Element ? event.target.closest('[data-cursor]') : null;
        if (!target) return;

        const state = target.dataset.cursor || 'link';
        cursor.dataset.state = state;
        if (label) label.textContent = target.dataset.cursorLabel || '';
    });

    const offOut = on(document, 'pointerout', (event) => {
        const target = event.target instanceof Element ? event.target.closest('[data-cursor]') : null;
        if (!target) return;

        const next = event.relatedTarget instanceof Element ? event.relatedTarget.closest('[data-cursor]') : null;
        if (next) return;

        cursor.dataset.state = '';
        if (label) label.textContent = '';
    });

    const offDown = on(document, 'pointerdown', () => cursor.classList.add('is-down'));
    const offUp = on(document, 'pointerup', () => cursor.classList.remove('is-down'));

    // The pointer leaving the window should take the cursor with it, or it
    // sits frozen at the edge looking broken.
    const offLeave = on(document, 'pointerleave', () => {
        visible = false;
        cursor.style.opacity = '0';
    });

    const offResize = on(window, 'resize', cacheMagnets);
    const offScroll = on(window, 'scroll', cacheMagnets, { passive: true });

    cacheMagnets();
    raf = requestAnimationFrame(frame);

    return {
        destroy() {
            if (raf) cancelAnimationFrame(raf);
            offMove();
            offOver();
            offOut();
            offDown();
            offUp();
            offLeave();
            offResize();
            offScroll();
            document.documentElement.classList.remove('has-cursor');
        },
        refresh: cacheMagnets,
    };
}
