import { ScrollTrigger } from '../core/scroll.js';
import { qs, qsa, on } from '../lib/dom.js';
import { env } from '../lib/env.js';

/**
 * 04 — Services
 *
 * The list is the interface. An entry claims focus when it reaches the
 * reading line, when a pointer enters it, or when the keyboard lands on it —
 * three routes into one function, so the chapter behaves identically however
 * it is being driven.
 *
 * Nothing is pinned and the wheel is never intercepted. A reader who wants
 * to leave should be able to.
 */
export function initServices(director) {
    const chapter = qs('[data-services]');
    if (!chapter) return;

    const rows = qsa('[data-service]', chapter);
    const panes = qsa('[data-service-pane]', chapter);

    if (!rows.length) return;

    const triggers = rows.map((row) => qs('[data-service-trigger]', row));

    let currentIndex = 0;

    function activate(index) {
        if (index === currentIndex || index < 0 || index >= rows.length) return;
        currentIndex = index;

        rows.forEach((row, i) => row.classList.toggle('is-active', i === index));

        triggers.forEach((trigger, i) => {
            if (trigger) trigger.setAttribute('aria-expanded', i === index ? 'true' : 'false');
        });

        panes.forEach((pane, i) => {
            pane.classList.toggle('is-current', i === index);
            // Hidden panes leave the accessibility tree as well as the view.
            // A screen reader should not be read six ledes in a row.
            pane.setAttribute('aria-hidden', i === index ? 'false' : 'true');
        });

        // Reshape the world to match. The form name comes from the content
        // file, so adding a service brings its geometry with it.
        const form = rows[index].dataset.form;
        if (director && form) director.setFocus(form);
    }

    /* ---- Scroll ------------------------------------------------------------
       The reading line sits a little above centre, where the eye actually
       rests when scanning a list. */

    rows.forEach((row, index) => {
        ScrollTrigger.create({
            trigger: row,
            start: 'top 62%',
            end: 'bottom 62%',
            onEnter: () => activate(index),
            onEnterBack: () => activate(index),
        });
    });

    /* ---- Pointer and keyboard ----------------------------------------------
       Below the panel breakpoint the detail folds into the row, so the
       trigger becomes a real toggle. Above it, activation is enough and a
       second click should not collapse what the reader just opened. */

    rows.forEach((row, index) => {
        const trigger = triggers[index];
        if (!trigger) return;

        if (env.finePointer) {
            on(row, 'pointerenter', () => activate(index));
        }

        on(trigger, 'focus', () => activate(index));

        on(trigger, 'click', () => {
            const folded = window.innerWidth <= 1000;

            if (folded && index === currentIndex) {
                const open = row.classList.toggle('is-active');
                trigger.setAttribute('aria-expanded', open ? 'true' : 'false');
                return;
            }

            activate(index);
        });
    });

    // The first row is already marked active in the markup, so the chapter is
    // composed before this module runs. Announce it to the world only.
    const initialForm = rows[0].dataset.form;
    if (director && initialForm) director.setFocus(initialForm);
}
