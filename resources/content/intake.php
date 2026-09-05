<?php

return [
    'projectTypes' => [
        ['value' => 'corporate-website', 'label' => 'Corporate website', 'group' => 'web'],
        ['value' => 'e-commerce', 'label' => 'E-commerce', 'group' => 'commerce'],
        ['value' => 'web-application', 'label' => 'Web application', 'group' => 'software'],
        ['value' => 'custom-software', 'label' => 'Custom software', 'group' => 'software'],
        ['value' => 'erp', 'label' => 'ERP solution', 'group' => 'systems'],
        ['value' => 'odoo-implementation', 'label' => 'Odoo implementation', 'group' => 'systems'],
        ['value' => 'odoo-customization', 'label' => 'Odoo customization', 'group' => 'systems'],
        ['value' => 'crm', 'label' => 'CRM solution', 'group' => 'systems'],
        ['value' => 'automation', 'label' => 'Business automation', 'group' => 'systems'],
        ['value' => 'mobile-application', 'label' => 'Mobile application', 'group' => 'software'],
        ['value' => 'ui-ux', 'label' => 'UI / UX', 'group' => 'experience'],
        ['value' => 'brand-identity', 'label' => 'Brand identity', 'group' => 'experience'],
        ['value' => 'creative-development', 'label' => 'Creative development', 'group' => 'experience'],
        ['value' => 'other', 'label' => 'Something else', 'group' => 'other'],
    ],
    'budgets' => [
        'web' => ['EGP 20k–50k', 'EGP 50k–100k', 'EGP 100k–200k', 'EGP 200k+'],
        'commerce' => ['EGP 30k–75k', 'EGP 75k–150k', 'EGP 150k–300k', 'EGP 300k+'],
        'software' => ['EGP 100k–250k', 'EGP 250k–500k', 'EGP 500k–1M', 'EGP 1M+'],
        'systems' => ['EGP 100k–250k', 'EGP 250k–500k', 'EGP 500k–1M', 'EGP 1M+'],
        'experience' => ['EGP 20k–50k', 'EGP 50k–100k', 'EGP 100k–200k', 'EGP 200k+'],
        'other' => ['EGP 20k–50k', 'EGP 50k–100k', 'EGP 100k–250k', 'EGP 250k+'],
    ],
    'timelines' => ['ASAP', '1–2 months', '2–4 months', '4–6 months', '6+ months', 'Flexible'],
    'companySizes' => ['Solo / startup', '2–10 people', '11–50 people', '51–250 people', '250+ people'],
    'services' => ['Strategy', 'Product design', 'Brand identity', 'Web development', 'Mobile development', 'Odoo / ERP', 'CRM / automation', 'Motion / 3D'],
    'rules' => [
        // Indicative only — surfaced on the review step, never presented as a
        // quotation. Kept centralised here so the estimator has one source of
        // truth instead of duplicating this mapping in JavaScript.
        'fit' => ['web' => 'Digital Products', 'commerce' => 'Digital Products', 'software' => 'Digital Products', 'systems' => 'Business Systems', 'experience' => 'Experience', 'other' => 'Digital Products'],
        'complexity' => ['web' => 'Low–Medium', 'commerce' => 'Medium', 'software' => 'Medium–High', 'systems' => 'Medium–High', 'experience' => 'Low–Medium', 'other' => 'To be scoped'],
        'timeline_hint' => ['web' => '4–8 weeks', 'commerce' => '6–10 weeks', 'software' => '10–20 weeks', 'systems' => '8–18 weeks', 'experience' => '3–6 weeks', 'other' => 'To be scoped'],
    ],
];
