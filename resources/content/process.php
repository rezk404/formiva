<?php

/*
|--------------------------------------------------------------------------
| Process — the system
|--------------------------------------------------------------------------
|
| Rendered as one drawn structure that assembles on scroll, not as six
| cards. `span` positions the stage on the timeline rule; `weight` sets how
| much vertical mass the stage's marker carries.
|
*/

return [
    'eyebrow' => 'The system',
    'headline' => ['Six stages.', 'One continuous line.'],
    'lede' => 'Engineering starts in week three, not after sign-off. The overlap is the point.',

    'stages' => [
        [
            'index' => '01',
            'title' => 'Discover',
            'window' => 'Weeks 1 — 2',
            'body' => 'Two weeks of asking the questions nobody has had time for. We talk to the people who will use it and the people who will maintain it.',
            'output' => 'Findings, not a report',
            'span' => [0, 12],
            'weight' => 0.55,
        ],
        [
            'index' => '02',
            'title' => 'Strategise',
            'window' => 'Week 3',
            'body' => 'One page. What we are making, who for, what it must do — and, more usefully, what we are refusing to do.',
            'output' => 'A single page, signed',
            'span' => [12, 22],
            'weight' => 0.4,
        ],
        [
            'index' => '03',
            'title' => 'Design',
            'window' => 'Weeks 4 — 11',
            'body' => 'Art direction and system develop in parallel. The look and the rules that govern it are the same piece of work.',
            'output' => 'Direction, system, tokens',
            'span' => [22, 55],
            'weight' => 1.0,
            'overlap' => true,
        ],
        [
            'index' => '04',
            'title' => 'Build',
            'window' => 'Weeks 5 — 17',
            'body' => 'Engineering begins while design is still moving. Every fortnight there is something real to click, on a real device.',
            'output' => 'Shipping increments',
            'span' => [32, 82],
            'weight' => 0.9,
            'overlap' => true,
        ],
        [
            'index' => '05',
            'title' => 'Launch',
            'window' => 'Week 18',
            'body' => 'Quietly, then loudly. Instrumented from the first hour so the first surprise is measured rather than argued about.',
            'output' => 'Live, monitored',
            'span' => [82, 90],
            'weight' => 0.45,
        ],
        [
            'index' => '06',
            'title' => 'Evolve',
            'window' => 'Ongoing',
            'body' => 'The first ninety days decide whether it lives. We stay close, then deliberately step back.',
            'output' => 'Handover, documented',
            'span' => [90, 100],
            'weight' => 0.7,
        ],
    ],
];
