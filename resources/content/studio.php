<?php

/*
|--------------------------------------------------------------------------
| Studio — the about chapter
|--------------------------------------------------------------------------
*/

return [
    'eyebrow' => 'The studio',

    'headline' => ['We build digital systems', 'that outlive their launch.'],

    'story' => [
        'FORMIVA started in 2016 with one rule: nothing in a deck that we cannot build. Three people, and a refusal to hand a client a render of something we had not tested.',
        'Nineteen of us now — designers, engineers, a researcher and a motion director — split evenly between the visible half of the work and the operational half most agencies skip. We design the storefront and implement the ERP behind it, because a beautiful front end running on a spreadsheet is not a finished project.',
    ],

    /*
     | Three positions, not values. Each is a thing we actually refuse or
     | insist on, phrased so a client could hold us to it.
     */
    'positions' => [
        [
            'index' => '01',
            'title' => 'We prototype before we present.',
            'body' => 'If it cannot be built in a week at low fidelity, it is not a concept yet — it is a wish.',
        ],
        [
            'index' => '02',
            'title' => 'We write the words before the layout.',
            'body' => 'Every project starts with the real copy. Design that only works with placeholder text does not work.',
        ],
        [
            'index' => '03',
            'title' => 'We hand over the keys.',
            'body' => 'Documented, instrumented, and with your team already using it. No retainer that exists to keep the lights on.',
        ],
        [
            'index' => '04',
            'title' => 'We document what we automate.',
            'body' => 'A workflow nobody can explain is a liability with a user interface. Every system we implement ships with the runbook, not just the login.',
        ],
    ],

    'stats' => [
        ['value' => '48', 'suffix' => '+', 'label' => 'Products launched', 'note' => 'Since 2016'],
        ['value' => '24', 'suffix' => '', 'label' => 'Markets reached', 'note' => 'Client footprint'],
        ['value' => '91', 'suffix' => '%', 'label' => 'Returning clients', 'note' => 'Second engagement'],
        ['value' => '19', 'suffix' => '', 'label' => 'People', 'note' => 'Cairo &amp; remote'],
    ],
];
