<?php

/*
|--------------------------------------------------------------------------
| Site — brand, navigation, metadata
|--------------------------------------------------------------------------
|
| Global values that sit outside any single chapter. When the CMS arrives
| this becomes a `settings` singleton; the shape stays identical.
|
*/

return [
    'brand' => [
        'name' => 'FORMIVA',
        'etymology' => 'FORM + VIVA',
        'tagline' => 'Digital products. Business systems. One studio.',
        'discipline' => 'Digital product & business systems studio',
        'founded' => '2016',
    ],

    'meta' => [
        'title' => 'FORMIVA — Digital products & business systems studio.',
        'description' => 'FORMIVA designs and builds digital products — websites, e-commerce, web and mobile applications — and the business systems that run behind them: ERP, Odoo, CRM and automation.',
        'keywords' => 'digital product studio, business systems, ERP implementation, Odoo implementation, CRM, business automation, web application development, e-commerce, UI/UX, brand identity',
        'locale' => 'en_GB',
        'theme_color' => '#0D0D0C',
    ],

    /*
     | Navigation lives in the markup, not here: the primary nav is four
     | routes, set once in components/navbar.blade.php, and each homepage
     | chapter declares its own 3D state via `data-world` on the section.
     | A duplicate index in this file only ever drifted out of step with
     | both, so there is not one.
     */

    'contact' => [
        'email' => 'hello@formiva.studio',
        'new_business' => 'new@formiva.studio',
        'phone' => '+20 2 2461 0114',
        'street' => 'Smart Village, Building B2',
        'city' => 'Giza',
        'postcode' => '12577',
        'country' => 'Egypt',
        'timezone' => 'EET',
    ],

    'social' => [
        ['label' => 'Instagram', 'handle' => '@formiva.studio', 'url' => 'https://instagram.com'],
        ['label' => 'LinkedIn',  'handle' => 'formiva',         'url' => 'https://linkedin.com'],
        ['label' => 'Behance',   'handle' => 'formiva',         'url' => 'https://behance.net'],
        ['label' => 'GitHub',    'handle' => 'formiva',         'url' => 'https://github.com'],
    ],

    'legal' => [
        'entity' => 'Formiva Studio',
        'links' => [
            ['label' => 'Privacy', 'url' => '#'],
            ['label' => 'Terms', 'url' => '#'],
            ['label' => 'Cookies', 'url' => '#'],
        ],
    ],
];
