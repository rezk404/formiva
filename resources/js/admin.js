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
        if (dialog.returnValue === 'accept' && pending) {
            if (typeof pending.requestSubmit === 'function') pending.requestSubmit();
            else pending.submit();
        }
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

/* -------------------------------------------------------------------------
 | Small async layer
 |
 | Only forms explicitly marked data-async enter this path. The server keeps
 | returning redirects for ordinary browsers, while JSON responses make
 | common workspace mutations feel immediate when enhancement is available.
 | ---------------------------------------------------------------------- */

function csrf() {
    return document.querySelector('meta[name="csrf-token"]')?.content
        || document.querySelector('input[name="_token"]')?.value
        || '';
}

function toast(message, tone = 'success') {
    const stack = document.querySelector('[data-toast-stack]');
    if (!stack) return;

    const item = document.createElement('div');
    item.className = `admin-toast admin-toast--${tone}`;
    item.setAttribute('role', tone === 'error' ? 'alert' : 'status');
    item.innerHTML = `<span aria-hidden="true">${tone === 'success' ? '✓' : tone === 'error' ? '!' : 'i'}</span><span></span><button type="button" class="admin-toast__close" aria-label="Dismiss notification">×</button>`;
    item.querySelector('span:nth-child(2)').textContent = message;
    item.querySelector('[data-toast-close], .admin-toast__close').addEventListener('click', () => item.remove());
    stack.append(item);
    window.setTimeout(() => item.remove(), 5000);
}

async function adminRequest(form, body = new FormData(form)) {
    const response = await fetch(form.action, {
        method: form.method.toUpperCase(),
        headers: {
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrf(),
        },
        body,
        credentials: 'same-origin',
    });

    let payload = null;
    try { payload = await response.json(); } catch { /* A normal HTML fallback remains usable. */ }

    if (!response.ok) {
        const error = new Error(payload?.message || 'Something could not be completed.');
        error.payload = payload;
        error.status = response.status;
        throw error;
    }

    return payload || { ok: true, message: 'Saved.' };
}

function setBusy(form, busy) {
    form.querySelectorAll('button').forEach((control) => {
        control.disabled = busy;
    });
    const submit = form.querySelector('[type="submit"]');
    if (!submit) return;
    if (busy) {
        submit.dataset.originalLabel = submit.textContent.trim();
        submit.textContent = 'Working...';
        submit.classList.add('is-loading');
    } else {
        submit.textContent = submit.dataset.originalLabel || submit.textContent;
        submit.classList.remove('is-loading');
    }
}

function showValidation(form, errors = {}) {
    form.querySelectorAll('[aria-invalid="true"]').forEach((field) => field.removeAttribute('aria-invalid'));
    const first = Object.entries(errors)[0];
    Object.keys(errors).forEach((name) => {
        const field = form.querySelector(`[name="${CSS.escape(name)}"], [name^="${CSS.escape(name)}["]`);
        if (field) field.setAttribute('aria-invalid', 'true');
    });
    if (first) form.querySelector(`[name="${CSS.escape(first[0])}"], [name^="${CSS.escape(first[0])}["]`)?.focus();
}

function asyncForms() {
    document.addEventListener('submit', async (event) => {
        const form = event.target.closest('form[data-async]');
        if (!form || form.dataset.submitting === 'true') return;

        event.preventDefault();
        form.dataset.submitting = 'true';
        const body = new FormData(form, event.submitter || undefined);
        setBusy(form, true);

        try {
            const result = await adminRequest(form, body);
            toast(result.message || 'Saved.');
            if (result.redirect) {
                window.location.assign(result.redirect);
                return;
            }
            form.closest('[data-removable-row]')?.remove();
            form.dispatchEvent(new CustomEvent('admin:success', { bubbles: true, detail: result }));
        } catch (error) {
            if (error.status === 419) toast('Your session expired. Refresh and try again.', 'error');
            else if (error.status === 403) toast('You do not have permission for that action.', 'error');
            else if (error.status === 422) {
                showValidation(form, error.payload?.errors || {});
                toast(error.payload?.message || 'Please correct the highlighted fields.', 'error');
            }
            else toast(error.message || 'Could not complete the request.', 'error');
        } finally {
            form.dataset.submitting = 'false';
            setBusy(form, false);
        }
    });
}

function asyncNavigation() {
    const workspace = document.querySelector('#workspace');
    if (!workspace) return;

    const load = async (url, push = true) => {
        workspace.classList.add('is-loading');
        try {
            const response = await fetch(url, {
                headers: { Accept: 'text/html', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
            });
            if (!response.ok) throw new Error('Could not load that view.');
            const html = await response.text();
            const documentView = new DOMParser().parseFromString(html, 'text/html');
            const next = documentView.querySelector('#workspace');
            if (!next) throw new Error('Could not load that view.');
            workspace.replaceChildren(...next.childNodes);
            if (push) window.history.pushState({}, '', url);
            window.scrollTo({ top: 0, behavior: 'smooth' });
        } catch (error) {
            toast(error.message, 'error');
        } finally {
            workspace.classList.remove('is-loading');
        }
    };

    document.addEventListener('submit', (event) => {
        const form = event.target.closest('form[data-async-filter]');
        if (!form) return;
        event.preventDefault();
        load(`${form.action}?${new URLSearchParams(new FormData(form)).toString()}`);
    });

    document.addEventListener('click', (event) => {
        const link = event.target.closest('a[data-async-nav]');
        if (!link || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
        event.preventDefault();
        load(link.href);
    });

    window.addEventListener('popstate', () => load(window.location.href, false));
}

function toastLifecycle() {
    document.querySelectorAll('[data-toast]').forEach((item) => {
        item.querySelector('[data-toast-close]')?.addEventListener('click', () => item.remove());
        window.setTimeout(() => item.remove(), 5000);
    });
}

function commandPalette() {
    const dialog = document.querySelector('[data-command-dialog]');
    const input = dialog?.querySelector('[data-command-input]');
    const items = [...(dialog?.querySelectorAll('[data-command-item]') || [])];
    const empty = dialog?.querySelector('[data-command-empty]');
    let active = 0;

    if (!dialog || !input) return;

    const visible = () => items.filter((item) => !item.hidden);
    const highlight = () => visible().forEach((item, index) => item.classList.toggle('is-active', index === active));
    const filter = () => {
        const query = input.value.trim().toLowerCase();
        let count = 0;
        items.forEach((item) => {
            item.hidden = query !== '' && !item.dataset.commandSearch.toLowerCase().includes(query);
            if (!item.hidden) count += 1;
        });
        active = 0;
        if (empty) empty.hidden = count !== 0;
        highlight();
    };

    const open = () => {
        if (typeof dialog.showModal !== 'function') return;
        dialog.showModal();
        input.value = '';
        filter();
        window.requestAnimationFrame(() => input.focus());
    };

    document.querySelector('[data-command-open]')?.addEventListener('click', open);
    document.addEventListener('keydown', (event) => {
        if ((event.metaKey || event.ctrlKey) && event.key.toLowerCase() === 'k') {
            event.preventDefault();
            open();
        }
        if (event.key === 'Escape' && dialog.open) dialog.close();
        if (!dialog.open || !['ArrowDown', 'ArrowUp', 'Enter'].includes(event.key)) return;
        const choices = visible();
        if (event.key === 'ArrowDown') active = (active + 1) % Math.max(choices.length, 1);
        if (event.key === 'ArrowUp') active = (active - 1 + Math.max(choices.length, 1)) % Math.max(choices.length, 1);
        if (event.key === 'Enter' && choices[active]) choices[active].click();
        highlight();
        event.preventDefault();
    });
    input.addEventListener('input', filter);
}

function dirtyForms() {
    document.querySelectorAll('form[data-dirty-form]').forEach((form) => {
        let dirty = false;
        const mark = () => {
            dirty = true;
            document.body.dataset.formDirty = 'true';
            form.closest('[data-editor]')?.classList.add('is-dirty');
            const state = form.querySelector('[data-save-state]');
            if (state) state.textContent = 'Unsaved changes';
            const discard = form.querySelector('[data-discard]');
            if (discard) discard.hidden = false;
        };
        form.addEventListener('input', mark);
        form.addEventListener('change', mark);
        form.addEventListener('submit', () => {
            dirty = false;
            document.body.dataset.formDirty = 'false';
            form.querySelector('[data-save-state]')?.replaceChildren(document.createTextNode('Saving...'));
        });
        form.addEventListener('admin:success', () => {
            dirty = false;
            document.body.dataset.formDirty = 'false';
            form.closest('[data-editor]')?.classList.remove('is-dirty');
            form.querySelector('[data-save-state]')?.replaceChildren(document.createTextNode('Saved just now'));
            form.querySelector('[data-discard]')?.setAttribute('hidden', '');
        });
        form.querySelector('[data-discard]')?.addEventListener('click', () => {
            form.reset();
            dirty = false;
            document.body.dataset.formDirty = 'false';
            form.closest('[data-editor]')?.classList.remove('is-dirty');
            form.querySelector('[data-save-state]')?.replaceChildren(document.createTextNode('No changes'));
            form.querySelector('[data-discard]')?.setAttribute('hidden', '');
        });
        window.addEventListener('beforeunload', (event) => {
            if (!dirty) return;
            event.preventDefault();
            event.returnValue = '';
        });
    });
}

function filterDisclosure() {
    document.addEventListener('click', (event) => {
        const toggle = event.target.closest('[data-filter-toggle]');
        if (!toggle) return;
        const form = toggle.closest('.admin-filters');
        const open = form.classList.toggle('is-open');
        toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
}

const start = () => {
    navigation();
    repeaters();
    confirmations();
    dialogs();
    datetimes();
    slugs();
    asyncForms();
    asyncNavigation();
    toastLifecycle();
    commandPalette();
    dirtyForms();
    filterDisclosure();
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', start);
} else {
    start();
}
