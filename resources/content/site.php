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
        'tagline' => 'Digital products with a pulse.',
        'discipline' => 'Creative technology studio',
        'founded' => '2016',
    ],

    'meta' => [
        'title' => 'FORMIVA — Digital products with a pulse.',
        'description' => 'FORMIVA is a creative technology studio creating websites, digital products and brand experiences with strategy, design and engineering under one roof.',
        'keywords' => 'creative technology studio, digital product design, web experience, WebGL, design systems, e-commerce',
        'locale' => 'en_GB',
        'theme_color' => '#0B0B0C',
    ],

    /*
     | Chapters double as the navigation model and as the scroll-progress
     | index. `world` names the state the shared 3D system holds while the
     | chapter is on screen — see resources/js/three/states.js.
     */
    'chapters' => [
        ['id' => 'hero',       'index' => '01', 'label' => 'Index',    'nav' => false, 'world' => 'monolith'],
        ['id' => 'manifesto',  'index' => '02', 'label' => 'Manifesto','nav' => false, 'world' => 'breathe'],
        ['id' => 'trust',      'index' => '03', 'label' => 'Clients',  'nav' => false, 'world' => 'breathe'],
        ['id' => 'services',   'index' => '04', 'label' => 'Services', 'nav' => true,  'world' => 'fan'],
        ['id' => 'work',       'index' => '05', 'label' => 'Work',     'nav' => true,  'world' => 'disperse'],
        ['id' => 'case-study', 'index' => '06', 'label' => 'Case',     'nav' => false, 'world' => 'disperse'],
        ['id' => 'studio',     'index' => '07', 'label' => 'Studio',   'nav' => true,  'world' => 'stair'],
        ['id' => 'process',    'index' => '08', 'label' => 'Process',  'nav' => true,  'world' => 'stair'],
        ['id' => 'people',     'index' => '09', 'label' => 'People',   'nav' => false, 'world' => 'stair'],
        ['id' => 'signals',    'index' => '10', 'label' => 'Signals',  'nav' => true,  'world' => 'resolve'],
        ['id' => 'contact',    'index' => '11', 'label' => 'Contact',  'nav' => true,  'world' => 'resolve'],
    ],

    'contact' => [
        'email' => 'hello@formiva.studio',
        'new_business' => 'new@formiva.studio',
        'phone' => '+351 213 908 114',
        'street' => 'Rua da Boavista 84',
        'city' => 'Lisboa',
        'postcode' => '1200-068',
        'country' => 'Portugal',
        'timezone' => 'WEST',
    ],

    'social' => [
        ['label' => 'Instagram', 'handle' => '@formiva.studio', 'url' => 'https://instagram.com'],
        ['label' => 'LinkedIn',  'handle' => 'formiva',         'url' => 'https://linkedin.com'],
        ['label' => 'Are.na',    'handle' => 'formiva',         'url' => 'https://are.na'],
        ['label' => 'GitHub',    'handle' => 'formiva',         'url' => 'https://github.com'],
    ],

    'legal' => [
        'entity' => 'Formiva Studio, Lda.',
        'registration' => 'NIPC 514 902 337',
        'links' => [
            ['label' => 'Privacy', 'url' => '#'],
            ['label' => 'Terms', 'url' => '#'],
            ['label' => 'Cookies', 'url' => '#'],
        ],
    ],
];
