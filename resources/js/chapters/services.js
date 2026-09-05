import { ScrollTrigger } from '../core/scroll.js';
import { qs, qsa, on } from '../lib/dom.js';
import { env } from '../lib/env.js';

/**
 * Services
 *
 * The list is the interface. An entry claims focus when it reaches the
 * reading line, when a pointer enters it, or when the keyboard lands on it —
 * three routes into one function, so the chapter behaves identically however
 * it is being driven.
 *
 * Nothing is pinned and the wheel is never intercepted. A reader who wants
 * to leave should be able to.
 *
 * `frame` is a holder rather than the director itself: the accordion is
 * wired up the moment the page loads, while the 3D module is fetched
 * separately and may never arrive at all. Everything here works either way.
 */
export function initServices(frame = {}) {
    const chapter = qs('[data-services]');
    if (!chapter) return;

    const rows = qsa('[data-service]', chapter);
    const panes = qsa('[data-service-pane]', chapter);

    if (!rows.length) return;

    const triggers = rows.map((row) => qs('[data-service-trigger]', row));

    let currentIndex = 0;

    const focusForm = (form) => {
        if (form) frame.director?.setFocus(form);
    };

    /** Applies one row's open/closed state to markup and assistive tech. */
    function render(index, open = true) {
        rows.forEach((row, i) => row.classList.toggle('is-active', i === index && open));

        triggers.forEach((trigger, i) => {
            if (trigger) trigger.setAttribute('aria-expanded', i === index && open ? 'true' : 'false');
        });

        panes.forEach((pane, i) => {
            const current = i === index && open;
            pane.classList.toggle('is-current', current);
            // A collapsed pane leaves the accessibility tree as well as the
            // view — a screen reader should not be read three ledes in a row.
            pane.setAttribute('aria-hidden', current ? 'false' : 'true');
        });
    }

    function activate(index) {
        if (index === currentIndex || index < 0 || index >= rows.length) return;
        currentIndex = index;

        render(index);

        // Reshape the frame to match. The form name comes from the content
        // file, so adding a service brings its geometry with it.
        focusForm(rows[index].dataset.form);
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
                // Collapse in place. This has to go through `render` too, or
                // the pane stays exposed to assistive tech after closing.
                render(index, !row.classList.contains('is-active'));
                return;
            }

            activate(index);
        });

        // Sub-items inside the open pane get their own, finer-grained form —
        // it only ever shows while this pillar is the active one, and reverts
        // to the pillar's own form the moment the pointer leaves the item, so
        // it can never outlive the row that owns it.
        if (env.finePointer) {
            const pane = panes[index];

            qsa('[data-item-form]', pane || chapter).forEach((item) => {
                on(item, 'pointerenter', () => {
                    if (index === currentIndex) focusForm(item.dataset.itemForm);
                });
                on(item, 'pointerleave', () => {
                    if (index === currentIndex) focusForm(row.dataset.form);
                });
            });
        }
    });

    // The first row is already marked active in the markup, so the chapter is
    // composed before this module runs. Announce it to the frame only — and
    // again once the frame actually arrives, since it may load later.
    render(0);
    focusForm(rows[0].dataset.form);

    return {
        /** Called by the bootstrap once the 3D module has resolved. */
        attach(director) {
            frame.director = director;
            focusForm(rows[currentIndex]?.dataset.form);
        },
    };
}
