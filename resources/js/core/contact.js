export function initContactForm() {
    const form = document.querySelector('[data-contact-form]');
    if (!form) return;
    const notice = form.querySelector('.fv-contact-page__notice');

    form.addEventListener('submit', (event) => {
        event.preventDefault();
        if (!form.checkValidity()) { form.reportValidity(); return; }

        const button = form.querySelector('[type="submit"]');
        if (button?.disabled) return;

        const values = new FormData(form);
        const lines = [
            `Name: ${values.get('name')}`,
            `Email: ${values.get('email')}`,
            `Company: ${values.get('company') || 'Not provided'}`,
            `Project type: ${values.get('type')}`,
            `Budget: ${values.get('budget')}`,
            '',
            'Project brief:',
            values.get('message'),
        ];
        const recipient = form.dataset.recipient;
        if (button) {
            button.disabled = true;
            button.textContent = 'Preparing brief…';
        }
        notice.textContent = 'Preparing a local email draft — no project data is sent to FORMIVA yet.';

        window.setTimeout(() => {
            notice.textContent = 'Your brief is ready in your email client. This frontend does not submit or store your data.';
            if (button) {
                button.disabled = false;
                button.innerHTML = 'Prepare email <span>→</span>';
            }
            window.location.href = `mailto:${encodeURIComponent(recipient)}?subject=${encodeURIComponent('New FORMIVA project enquiry')}&body=${encodeURIComponent(lines.join('\n'))}`;
        }, 450);
    });
}
