/**
 * FORMIVA — workspace behaviour.
 *
 * Four jobs, no framework. Every screen in the CMS is server-rendered and
 * every form posts; this file only adds the things HTML cannot express on its
 * own — a disclosure for the mobile navigation, repeatable form rows, a
 * confirmation that can name what it is about to delete, and a slug that
 * follows a title until someone takes it over.
 *
 * Written so that with JavaScript unavailable the pages still work: the
 * navigation is visible, repeaters keep the rows already rendered, delete
 * buttons submit their form, and slugs are derived server-side anyway.
 */

/* -------------------------------------------------------------------------
 | Mobile navigation
 | ---------------------------------------------------------------------- */

function navigation() {
    const toggle = document.querySelector('[data-nav-toggle]');
    const sidebar = document.querySelector('[data-sidebar]');

    if (!toggle || !sidebar) return;

    toggle.addEventListener('click', () => {
        const open = sidebar.classList.toggle('is-open');
        toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    });

    // Escape closes it, and focus returns to the control that opened it —
    // otherwise the tab order restarts at the top of the document.
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && sidebar.classList.contains('is-open')) {
            sidebar.classList.remove('is-open');
            toggle.setAttribute('aria-expanded', 'false');
            toggle.focus();
        }
    });
}

/* -------------------------------------------------------------------------
 | Repeatable rows
 |
 | A repeater owns a <template> and a list. Adding clones the template and
 | renumbers every input name, so the server receives a dense array whatever
 | order rows were added or removed in.
 | ---------------------------------------------------------------------- */

function renumber(repeater) {
    const name = repeater.dataset.repeater;
    const rows = [...repeater.querySelectorAll('[data-repeater-row]')];

    rows.forEach((row, index) => {
        row.querySelectorAll('[name]').forEach((field) => {
            field.name = field.name.replace(
                new RegExp(`^${name}\\[[^\\]]*\\]`),
                `${name}[${index}]`,
            );
        });

        row.querySelectorAll('[data-repeater-index]').forEach((label) => {
            label.textContent = String(index + 1).padStart(2, '0');
        });

        row.querySelectorAll('[data-repeater-up]').forEach((button) => {
            button.disabled = index === 0;
        });

        row.querySelectorAll('[data-repeater-down]').forEach((button) => {
            button.disabled = index === rows.length - 1;
        });
    });

    const empty = repeater.querySelector('[data-repeater-empty]');
    if (empty) empty.hidden = rows.length > 0;
}

function repeaters() {
    document.querySelectorAll('[data-repeater]').forEach((repeater) => {
        const list = repeater.querySelector('[data-repeater-list]');
        const template = repeater.querySelector('template');

        if (!list || !template) return;

        repeater.addEventListener('click', (event) => {
            const add = event.target.closest('[data-repeater-add]');
            const remove = event.target.closest('[data-repeater-remove]');
            const up = event.target.closest('[data-repeater-up]');
            const down = event.target.closest('[data-repeater-down]');

            if (add) {
                event.preventDefault();
                list.append(template.content.cloneNode(true));
                renumber(repeater);

                const rows = list.querySelectorAll('[data-repeater-row]');
                const field = rows[rows.length - 1]?.querySelector('input, textarea, select');
                field?.focus();

                return;
            }

            if (remove) {
                event.preventDefault();
                remove.closest('[data-repeater-row]')?.remove();
                renumber(repeater);

                return;
            }

            if (up || down) {
                event.preventDefault();
                const row = (up || down).closest('[data-repeater-row]');
                const sibling = up ? row?.previousElementSibling : row?.nextElementSibling;

                if (row && sibling) {
                    up ? sibling.before(row) : sibling.after(row);
                    renumber(repeater);
                    (up || down).focus();
                }
            }
        });

        renumber(repeater);
    });
}

/* -------------------------------------------------------------------------
 | Destructive confirmation
 |
 | A <dialog> rather than window.confirm, because the question is worth
 | asking properly: it names the record and states the consequence. The
 | button stays a submit inside its own form, so without JavaScript the
 | delete still works — it simply does not ask first.
 | ---------------------------------------------------------------------- */

function confirmations() {
    const dialog = document.querySelector('[data-confirm-dialog]');

    if (!dialog || typeof dialog.showModal !== 'function') return;

    const title = dialog.querySelector('[data-confirm-title]');
    const body = dialog.querySelector('[data-confirm-body]');
    const accept = dialog.querySelector('[data-confirm-accept]');
    let pending = null;

    document.addEventListener('click', (event) => {
        const trigger = event.target.closest('[data-confirm]');

        if (!trigger) return;

        event.preventDefault();
        pending = trigger.closest('form');

        if (title) title.textContent = trigger.dataset.confirmTitle || 'Are you sure?';
        if (body) body.textContent = trigger.dataset.confirm;
        if (accept) accept.textContent = trigger.dataset.confirmAccept || 'Delete';

        dialog.showModal();
    });

    dialog.addEventListener('close', () => {
        if (dialog.returnValue === 'accept' && pending) pending.submit();
        pending = null;
    });
}

/* -------------------------------------------------------------------------
 | Slug shadowing
 |
 | The slug follows the title only while it is untouched and the record is
 | new. Once someone types in it — or the record already has a public URL —
 | it is theirs, because changing a published slug breaks inbound links.
 | ---------------------------------------------------------------------- */

function slugify(value) {
    return value
        .toLowerCase()
        .normalize('NFD')
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-+|-+$/g, '');
}

function slugs() {
    document.querySelectorAll('[data-slug-source]').forEach((source) => {
        const target = document.querySelector(`#${source.dataset.slugSource}`);

        if (!target || target.dataset.slugLocked === 'true') return;

        let shadowing = target.value.trim() === '';

        target.addEventListener('input', () => {
            shadowing = target.value.trim() === '';
        });

        source.addEventListener('input', () => {
            if (shadowing) target.value = slugify(source.value);
        });
    });
}

/* -------------------------------------------------------------------------
 | Dialogs
 |
 | Any button carrying data-dialog-open="id" opens that <dialog>; anything
 | with data-dialog-close inside one closes it. Focus goes to the first field
 | on open and returns to the trigger on close, which is the part browsers do
 | not do for you.
 | ---------------------------------------------------------------------- */

function dialogs() {
    let opener = null;

    document.addEventListener('click', (event) => {
        const trigger = event.target.closest('[data-dialog-open]');

        if (trigger) {
            const dialog = document.getElementById(trigger.dataset.dialogOpen);

            if (dialog && typeof dialog.showModal === 'function') {
                event.preventDefault();
                opener = trigger;
                dialog.showModal();
                dialog.querySelector('input, select, textarea, button')?.focus();
            }

            return;
        }

        if (event.target.closest('[data-dialog-close]')) {
            event.preventDefault();
            event.target.closest('dialog')?.close();
        }
    });

    document.addEventListener(
        'close',
        () => {
            opener?.focus();
            opener = null;
        },
        true,
    );
}

/* -------------------------------------------------------------------------
 | Date and time
 |
 | The input stays native. This adds the two things the platform control does
 | not: presets for the answers people actually pick, and the chosen moment
 | written out in words so a numeric field never has to be decoded.
 | ---------------------------------------------------------------------- */

const DATETIME_FORMAT = new Intl.DateTimeFormat(undefined, {
    weekday: 'long',
    day: 'numeric',
    month: 'long',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
});

function localValue(date) {
    const pad = (n) => String(n).padStart(2, '0');

    return (
        `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}` +
        `T${pad(date.getHours())}:${pad(date.getMinutes())}`
    );
}

function presetDate(preset) {
    const date = new Date();

    if (preset === 'tomorrow-09') {
        date.setDate(date.getDate() + 1);
        date.setHours(9, 0, 0, 0);
    } else if (preset === 'next-week') {
        date.setDate(date.getDate() + 7);
        date.setMinutes(0, 0, 0);
    } else if (preset === 'next-month') {
        date.setMonth(date.getMonth() + 1);
        date.setMinutes(0, 0, 0);
    }

    return date;
}

function datetimes() {
    document.querySelectorAll('[data-datetime]').forEach((field) => {
        const input = field.querySelector('[data-datetime-input]');
        const readout = field.querySelector('[data-datetime-readout]');
        const presets = field.querySelector('[data-datetime-presets]');

        if (!input) return;

        if (presets) presets.hidden = false;

        const describe = () => {
            if (!readout) return;

            if (!input.value) {
                readout.textContent = '';
                field.classList.remove('is-set');

                return;
            }

            const parsed = new Date(input.value);

            readout.textContent = Number.isNaN(parsed.valueOf())
                ? ''
                : DATETIME_FORMAT.format(parsed);
            field.classList.add('is-set');
        };

        input.addEventListener('input', describe);
        input.addEventListener('change', describe);

        field.querySelectorAll('[data-datetime-preset]').forEach((button) => {
            button.addEventListener('click', () => {
                input.value = localValue(presetDate(button.dataset.datetimePreset));
                describe();
                input.focus();
            });
        });

        describe();
    });
}

const start = () => {
    navigation();
    repeaters();
    confirmations();
    dialogs();
    datetimes();
    slugs();
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', start);
} else {
    start();
}
