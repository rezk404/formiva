import { createWorld } from './world.js';
import { ScrollTrigger } from '../core/scroll.js';
import { qs, qsa, on } from '../lib/dom.js';
import { clamp } from '../lib/math.js';
import { env } from '../lib/env.js';

/**
 * THE DIRECTOR
 *
 * Decides what the world is doing at any scroll position.
 *
 * Chapters declare their state in markup — `data-world="fan"` — and this
 * module reads them in document order to build a keyframe track. One
 * ScrollTrigger evaluates the whole track per frame.
 *
 * A trigger per chapter was the obvious approach and the wrong one: adjacent
 * ranges overlap wherever a chapter is short, and two triggers writing a
 * blend in the same frame produce a form that stutters between two shapes.
 * A single source of truth cannot disagree with itself.
 */
export function initWorld() {
    const holder = qs('[data-world-root]');
    const canvas = qs('[data-world-canvas]');

    if (!holder || !canvas) return null;

    const fail = (reason) => {
        holder.classList.add('is-fallback');
        holder.classList.remove('is-ready');

        // The CSS fallback remains fully legible; WebGL is enhancement.
    };

    const world = createWorld(canvas, {
        onReady: () => holder.classList.add('is-ready'),
        onFail: fail,
    });

    if (!world) {
        fail('init');
        return null;
    }

    /* ── The track ───────────────────────────────────────────────────────── */

    const sections = qsa('[data-world]');

    if (!sections.length) {
        world.setState('monolith');
        return world;
    }

    const marks = sections.map((el) => ({ el, state: el.dataset.world, top: 0 }));

    // Where each chapter begins, in document coordinates. Re-measured on
    // every refresh, because a font landing or an image resolving moves them.
    const measure = () => {
        const scrollY = window.scrollY;
        marks.forEach((mark) => {
            mark.top = mark.el.getBoundingClientRect().top + scrollY;
        });
    };

    let focus = null;

    /**
     * Resolve the state for a scroll position.
     *
     * A chapter's state is reached over the last two thirds of its approach,
     * so the transition completes shortly after its first line of type is
     * readable — the form settles as the reader arrives, not after.
     */
    const evaluate = (scrollY) => {
        const vh = window.innerHeight;

        let from = marks[0].state;
        let to = marks[0].state;
        let t = 1;

        for (let i = 1; i < marks.length; i += 1) {
            const start = marks[i].top - vh;
            const end = marks[i].top - vh * 0.35;

            if (end <= start) continue;

            if (scrollY >= end) {
                from = marks[i].state;
                to = marks[i].state;
                t = 1;
                continue;
            }

            if (scrollY > start) {
                from = marks[i - 1].state;
                to = marks[i].state;
                t = clamp((scrollY - start) / (end - start), 0, 1);
            }

            break;
        }

        // A focused service overrides only while the ambient state is `fan`,
        // which means it expires on its own the moment the reader leaves the
        // services chapter. Nothing has to remember to clear it.
        if (focus && to === 'fan') {
            world.blendTo('fan', focus, 1);
            return;
        }

        world.blendTo(from, to, t);
    };

    measure();
    evaluate(window.scrollY);

    ScrollTrigger.create({
        start: 'top top',
        end: 'max',
        onUpdate: () => evaluate(window.scrollY),
        onRefresh: () => {
            measure();
            evaluate(window.scrollY);
        },
    });

    /* ── Visibility ──────────────────────────────────────────────────────────
       The frame is not a continuous backdrop: only the chapters marked
       `data-world-visible` clear their ground for it, and everything else
       paints over it opaquely. Rendering follows that exactly — active
       while one of those chapters is on screen, paused otherwise, which on
       a typical scroll is most of the time.

       Below the compact breakpoint the scrim goes solid (see pages.css),
       so the canvas can never be seen and is never worth rendering. The
       breakpoint is duplicated here deliberately; the two must stay in
       step. */

    const stages = qsa('[data-world-visible]');
    const compact = window.matchMedia('(max-width: 900px)');

    if (!stages.length) {
        world.setActive(false);
    } else {
        const onScreen = new Set();

        const sync = () => world.setActive(onScreen.size > 0 && !compact.matches);

        const visibility = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) onScreen.add(entry.target);
                    else onScreen.delete(entry.target);
                });

                sync();
            },
            { rootMargin: '10% 0px' }
        );

        stages.forEach((stage) => visibility.observe(stage));

        if (typeof compact.addEventListener === 'function') {
            compact.addEventListener('change', sync);
        }
    }

    /* ── Pointer ─────────────────────────────────────────────────────────────
       Moves the camera a little, never the object. Skipped on touch, where
       there is no hover to respond to and the gesture would fight scrolling. */

    if (!env.touch && env.animate) {
        on(
            window,
            'pointermove',
            (event) => {
                if (event.pointerType !== 'mouse') return;
                world.setPointer(
                    (event.clientX / window.innerWidth) * 2 - 1,
                    (event.clientY / window.innerHeight) * 2 - 1
                );
            },
            { passive: true }
        );
    }

    /* ── Resize ──────────────────────────────────────────────────────────────
       Mobile browsers fire resize whenever the address bar collapses. Height
       alone is ignored, or the renderer would be rebuilt on every scroll. */

    let lastWidth = window.innerWidth;
    let lastHeight = window.innerHeight;

    on(
        window,
        'resize',
        () => {
            const widthChanged = window.innerWidth !== lastWidth;
            const heightDelta = Math.abs(window.innerHeight - lastHeight);

            if (!widthChanged && heightDelta < 120) return;

            lastWidth = window.innerWidth;
            lastHeight = window.innerHeight;

            world.resize();
            measure();
            evaluate(window.scrollY);
        },
        { passive: true }
    );

    return {
        world,
        setFocus(name) {
            focus = name;
            evaluate(window.scrollY);
        },
    };
}
