export function initContactForm() {
    const form = document.querySelector('[data-contact-form]');
    if (!form) return;
    const steps = [...form.querySelectorAll('[data-step]')];
    const next = form.querySelector('[data-intake-next]');
    const back = form.querySelector('[data-intake-back]');
    const submit = form.querySelector('[data-intake-submit]');
    const budgets = JSON.parse(form.dataset.budgets || '{}');
    let current = 0;
    const typeInput = () => form.querySelector('input[name="type"]:checked');
    const renderBudgets = () => { form.querySelector('[data-budget-options]').innerHTML = (budgets[typeInput()?.dataset.group || 'other']).map((item) => `<label class="fv-choice"><input type="radio" name="budget_choice" value="${item}" required><span>${item}</span></label>`).join(''); };
    const renderReview = () => {
        const data = Object.fromEntries(new FormData(form).entries());
        const type = typeInput()?.closest('label')?.querySelector('span')?.textContent || 'Not selected';
        form.querySelector('[data-intake-summary]').innerHTML = `<dl><div><dt>Project</dt><dd>${type}</dd></div><div><dt>Contact</dt><dd>${data.name || ''} / ${data.company || ''}</dd></div><div><dt>Timeline</dt><dd>${data.timeline || 'Flexible'}</dd></div><div><dt>Budget</dt><dd>${data.budget_choice || 'To discuss'}</dd></div></dl>`;
        const group = typeInput()?.dataset.group || 'other';
        form.querySelector('[data-intake-estimate]').innerHTML = `<strong>Indicative project fit</strong><span>${group === 'systems' ? 'Business Systems / ERP' : group === 'experience' ? 'Experience' : 'Digital Products'}</span><small>We will qualify the scope together before recommending an engagement.</small>`;
    };
    const show = (index) => { current = index; steps.forEach((step, i) => { step.hidden = i !== current; }); form.querySelector('[data-intake-progress]').textContent = String(current + 1).padStart(2, '0'); form.querySelector('[data-intake-bar]').style.width = `${((current + 1) / steps.length) * 100}%`; back.hidden = current === 0; next.hidden = current === steps.length - 1; submit.hidden = current !== steps.length - 1; if (current === 4) renderBudgets(); if (current === 8) renderReview(); };
    const valid = () => { if (!steps[current].querySelector(':invalid')) return true; steps[current].querySelector(':invalid').focus(); return false; };
    form.addEventListener('change', (event) => { if (event.target.name === 'type') renderBudgets(); if (event.target.name === 'budget_choice') form.querySelector('[name="budget"]').value = event.target.value; });
    next.addEventListener('click', () => { if (valid()) show(Math.min(current + 1, steps.length - 1)); });
    back.addEventListener('click', () => show(Math.max(current - 1, 0)));
    form.addEventListener('submit', (event) => { event.preventDefault(); if (!valid()) return; const data = Object.fromEntries(new FormData(form).entries()); const body = Object.entries(data).filter(([key]) => key !== 'attachments').map(([key, value]) => `${key}: ${value}`).join('\n'); form.querySelector('.fv-contact-page__notice').textContent = 'Preparing a local email draft. Nothing is sent or stored by this frontend.'; submit.disabled = true; window.setTimeout(() => { window.location.href = `mailto:${encodeURIComponent(form.dataset.recipient)}?subject=${encodeURIComponent('FORMIVA project brief')}&body=${encodeURIComponent(body)}`; }, 350); });
    show(0);
}
