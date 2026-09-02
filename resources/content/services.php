<?php

/*
|--------------------------------------------------------------------------
| Services — the capability index
|--------------------------------------------------------------------------
|
| `form` is the geometric behaviour the shared 3D system adopts while the
| service is active. It is not decoration: each service reshapes the same
| object rather than introducing a new one.
|
*/

return [
    [
        'index' => '01',
        'slug' => 'digital-products',
        'title' => 'Digital Products',
        'form' => 'modular',
        'lede' => 'We build the thing itself — from the first concept that survives scrutiny to a product that holds up under real users.',
        'capabilities' => ['Product strategy', 'Design systems', 'Prototyping', 'Front-end engineering'],
        'note' => 'Twelve to twenty weeks, concept to launch.',
    ],
    [
        'index' => '02',
        'slug' => 'web-experiences',
        'title' => 'Web Experiences',
        'form' => 'layered',
        'lede' => 'Sites that carry a brand instead of describing it. Built fast first, then made cinematic — never the other way round.',
        'capabilities' => ['Art direction', 'Motion design', 'WebGL', 'Headless CMS'],
        'note' => 'No build ships below 95 on Lighthouse performance.',
    ],
    [
        'index' => '03',
        'slug' => 'mobile-experiences',
        'title' => 'Mobile Experiences',
        'form' => 'compressed',
        'lede' => 'Small screens punish anything unnecessary. We design for the thumb, the tunnel and the two-bar signal.',
        'capabilities' => ['iOS &amp; Android', 'React Native', 'Offline-first', 'Motion systems'],
        'note' => 'Reviewed on device weekly, never only in Figma.',
    ],
    [
        'index' => '04',
        'slug' => 'ui-ux',
        'title' => 'UI / UX',
        'form' => 'interface',
        'lede' => 'Research that changes decisions, and interfaces that survive contact with a roadmap.',
        'capabilities' => ['Discovery research', 'Information architecture', 'Interaction design', 'Design systems'],
        'note' => 'Handover includes tokens, not just screens.',
    ],
    [
        'index' => '05',
        'slug' => 'e-commerce',
        'title' => 'E-Commerce',
        'form' => 'networked',
        'lede' => 'Storefronts judged on conversion, returns and repeat purchase. The awards are a side effect.',
        'capabilities' => ['Shopify &amp; headless', 'Merchandising', 'Checkout', 'Post-purchase'],
        'note' => 'Median uplift across the last six builds: +31% revenue per session.',
    ],
    [
        'index' => '06',
        'slug' => 'digital-infrastructure',
        'title' => 'Digital Infrastructure',
        'form' => 'structural',
        'lede' => 'The unglamorous half. Content models, pipelines and platforms your team can actually operate.',
        'capabilities' => ['Architecture', 'Content modelling', 'CI/CD', 'Observability'],
        'note' => 'Built so you own it the day we leave.',
    ],
];
