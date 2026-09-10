/**
 * The project intake.
 *
 * A nine-step qualification form, not a contact box. Every step is a
 * `<fieldset>` already in the document — this module only ever toggles
 * `hidden` and focus, so back and forward never re-render content the
 * reader has already filled in.
 *
 * Submission is progressively enhanced: the browser posts the same form to
 * Laravel when JavaScript is available, while the server remains the source
 * of truth for validation and persistence.
 *
 * Every dynamic piece of markup below — including the reader's own name and
 * company — is built with createElement/textContent rather than innerHTML.
 * textContent never interprets its input as HTML, so there is nothing here
 * for user-entered text to break out of.
 */

export function initContactForm() {
    const form = document.querySelector('[data-contact-form]');
    if (!form) return;

    const steps = [...form.querySelectorAll('[data-step]')];
    if (!steps.length) return;

    const next = form.querySelector('[data-intake-next]');
    const back = form.querySelector('[data-intake-back]');
    const submit = form.querySelector('[data-intake-submit]');
    const notice = form.querySelector('[data-intake-notice]');
    const progress = form.querySelector('[data-intake-progress]');
    const progressTotal = form.querySelector('[data-intake-total]');
    const bar = form.querySelector('[data-intake-bar]');
    const budgetOptions = form.querySelector('[data-budget-options]');
    const budgetValue = form.querySelector('[name="budget"]');
    const fileInput = form.querySelector('[data-file-input]');
    const fileList = form.querySelector('[data-file-list]');

    // One source of truth for the estimator's business rules — see
    // resources/content/intake.php. Nothing here hardcodes a duplicate of
    // that mapping; if the config is missing a group, the estimator says so
    // rather than guessing.
    let intake = {};
    try {
        intake = JSON.parse(form.dataset.intake || '{}');
    } catch {
        intake = {};
    }

    const budgets = intake.budgets || {};
    const rules = intake.rules || {};

    // Which step does what is read from the markup, not counted by hand — a
    // step added or reordered in Blade must not silently break the budget
    // list or the review.
    const stepWith = (selector) => steps.findIndex((step) => step.querySelector(selector));
    const budgetStep = stepWith('[data-budget-options]');
    const reviewStep = stepWith('[data-intake-summary]');

    let current = 0;

    const typeInput = () => form.querySelector('input[name="type"]:checked');
    const typeGroup = () => typeInput()?.dataset.group || 'other';

    const setNotice = (message = '') => {
        if (notice) notice.textContent = message;
    };

    /**
     * Reads the whole form as plain strings.
     *
     * `Object.fromEntries(formData.entries())` silently keeps only the last
     * value of any repeated field, which would drop every service the
     * reader ticked but one. `getAll` is the only correct way to read a
     * multi-value control.
     */
    const collect = () => {
        const data = new FormData(form);
        const out = {};

        [...new Set(data.keys())].forEach((key) => {
            if (key === 'attachments') return;

            const values = data
                .getAll(key)
                .filter((value) => typeof value === 'string' && value.trim() !== '');

            if (values.length) out[key] = values.join(', ');
        });

        return out;
    };

    const renderBudgets = () => {
        if (!budgetOptions) return;

        const options = budgets[typeGroup()] || [];
        budgetOptions.replaceChildren();

        if (!options.length) {
            // No configured range for this group is not a dead end: the step
            // simply has nothing to ask, and the review says so.
            const note = document.createElement('p');
            note.className = 'fv-intake__hint';
            note.textContent = 'We will size this one together during discovery — continue.';
            budgetOptions.append(note);
            return;
        }

        options.forEach((item) => {
            const label = document.createElement('label');
            label.className = 'fv-choice';

            const input = document.createElement('input');
            input.type = 'radio';
            input.name = 'budget_choice';
            input.value = item;
            input.required = true;

            const span = document.createElement('span');
            span.textContent = item;

            label.append(input, span);
            budgetOptions.append(label);
        });
    };

    const renderReview = () => {
        const data = collect();
        const typeLabel = typeInput()?.closest('label')?.querySelector('span')?.textContent || 'Not selected';
        const group = typeGroup();

        const summary = form.querySelector('[data-intake-summary]');
        if (summary) {
            summary.replaceChildren();

            const dl = document.createElement('dl');

            [
                ['Project', typeLabel],
                ['Contact', [data.name, data.company].filter(Boolean).join(' / ') || 'Not given'],
                ['Industry', data.industry || 'Not given'],
                ['Services', data['services[]'] || 'To be scoped'],
                ['Timeline', data.timeline || 'Flexible'],
                ['Budget', data.budget_choice || 'To discuss'],
            ].forEach(([term, value]) => {
                const row = document.createElement('div');
                const dt = document.createElement('dt');
                dt.textContent = term;
                const dd = document.createElement('dd');
                // `value` carries the reader's own free text — textContent,
                // never innerHTML, so nothing here can execute.
                dd.textContent = value;
                row.append(dt, dd);
                dl.append(row);
            });

            summary.append(dl);
        }

        const estimate = form.querySelector('[data-intake-estimate]');
        if (!estimate) return;

        estimate.replaceChildren();

        const strong = document.createElement('strong');
        strong.textContent = 'Initial project fit';

        const category = document.createElement('span');
        category.textContent = rules.fit?.[group] || 'To be scoped';

        const detail = document.createElement('small');
        const complexity = rules.complexity?.[group] || 'to be scoped';
        const timeline = rules.timeline_hint?.[group] || data.timeline || 'flexible';
        const range = data.budget_choice ? ` Indicative range: ${data.budget_choice}.` : '';
        detail.textContent =
            `Indicative complexity: ${complexity} · Typical timeline: ${timeline}.${range} ` +
            'This is an initial fit, not a quotation — final scope is confirmed during discovery.';

        estimate.append(strong, category, detail);
    };

    const show = (index, { focus = true } = {}) => {
        current = Math.min(Math.max(index, 0), steps.length - 1);
        steps.forEach((step, i) => { step.hidden = i !== current; });

        if (progress) progress.textContent = String(current + 1).padStart(2, '0');
        if (bar) bar.style.width = `${((current + 1) / steps.length) * 100}%`;

        back.hidden = current === 0;
        next.hidden = current === steps.length - 1;
        submit.hidden = current !== steps.length - 1;

        if (current === budgetStep) renderBudgets();
        if (current === reviewStep) renderReview();

        setNotice('');

        if (focus) {
            steps[current].querySelector('input, select, textarea, button')?.focus({ preventScroll: true });
        }
    };

    const valid = () => {
        const invalid = steps[current].querySelector(':invalid');

        if (!invalid) {
            setNotice('');
            return true;
        }

        // The browser's own message is more specific than anything generic
        // written here, and it is already localised.
        setNotice(invalid.validationMessage || 'Please complete this step before continuing.');
        invalid.focus();
        return false;
    };

    form.addEventListener('change', (event) => {
        const field = event.target;
        if (!(field instanceof HTMLInputElement)) return;

        if (field.name === 'type') {
            // The ranges depend on the type, so a changed type invalidates
            // any range already chosen — including the hidden value that
            // would otherwise travel into the draft unnoticed.
            if (budgetValue) budgetValue.value = '';
            renderBudgets();
        }

        if (field.name === 'budget_choice' && budgetValue) {
            budgetValue.value = field.value;
        }
    });

    if (fileInput && fileList) {
        fileInput.addEventListener('change', () => {
            const names = [...fileInput.files].map((file) => file.name);
            fileList.textContent = names.length ? `Selected: ${names.join(', ')}` : '';
        });
    }

    next.addEventListener('click', () => {
        if (valid()) show(current + 1);
    });

    back.addEventListener('click', () => show(current - 1));

    form.addEventListener('submit', (event) => {
        event.preventDefault();
        if (!valid()) return;

        const originalLabel = submit.textContent;
        const csrf = form.querySelector('input[name="_token"]')?.value || document.querySelector('meta[name="csrf-token"]')?.content || '';
        submit.disabled = true;
        submit.textContent = 'Sending…';
        form.setAttribute('aria-busy', 'true');

        fetch(form.action, {
            method: 'POST',
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrf },
            body: new FormData(form),
            credentials: 'same-origin',
        })
            .then(async (response) => {
                const payload = await response.json().catch(() => ({}));
                if (!response.ok) {
                    const error = new Error(payload.message || 'We could not receive the brief.');
                    error.payload = payload;
                    error.status = response.status;
                    throw error;
                }
                return payload;
            })
            .then((payload) => {
                form.querySelectorAll('[data-step]').forEach((step) => { step.hidden = true; });
                form.querySelector('[data-intake-progress]')?.replaceChildren(document.createTextNode('✓'));
                form.querySelector('[data-intake-total]')?.replaceChildren(document.createTextNode('received'));
                form.querySelector('[data-intake-bar]')?.style.setProperty('width', '100%');
                form.querySelector('.fv-intake__actions')?.setAttribute('hidden', '');
                setNotice(`${payload.message || 'Your project brief is with us.'} Reference: ${payload.reference}`);
            })
            .catch((error) => {
                if (error.status === 422 && error.payload?.errors) {
                    const first = Object.keys(error.payload.errors)[0];
                    const field = form.querySelector(`[name="${CSS.escape(first)}"], [name^="${CSS.escape(first)}["]`);
                    field?.focus();
                }
                setNotice(error.message || 'We could not receive the brief. Please try again.');
                submit.disabled = false;
                submit.textContent = originalLabel;
                form.removeAttribute('aria-busy');
            });
    });

    if (progressTotal) progressTotal.textContent = `of ${String(steps.length).padStart(2, '0')}`;

    show(0, { focus: false });
}
