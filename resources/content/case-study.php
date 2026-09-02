<?php

/*
|--------------------------------------------------------------------------
| Featured case study — Kiln
|--------------------------------------------------------------------------
|
| Told as six beats. Each beat pins while its visual resolves, so the
| narrative is carried by the imagery rather than by paragraph length.
| Keep `body` to two sentences; the layout is designed around that limit.
|
*/

return [
    'project' => 'kiln',
    'eyebrow' => 'Featured case',
    'name' => 'Kiln',
    'title' => 'A shop that refused to be a shop.',
    'summary' => 'A ceramics maison founded in 1984, fourteen galleries, two thousand pieces — and a website that was a PDF with a phone number.',
    'client' => 'Kiln Ceramics',
    'duration' => '19 weeks',
    'team' => 'Six people',
    'role' => 'Strategy, art direction, e-commerce, infrastructure',

    'beats' => [
        [
            'index' => '01',
            'label' => 'Problem',
            'heading' => 'Two thousand pieces, no truth.',
            'body' => 'Kiln had made ceramics for four decades and sold them through galleries who each kept their own records. Nobody in the building could answer what was in stock, what it cost, or what it looked like.',
            'plate' => ['seed' => 1101, 'variant' => 'ink', 'ratio' => '3/2'],
            'alt' => 'Dense, misaligned slabs crowding a dark field — inventory without an index.',
        ],
        [
            'index' => '02',
            'label' => 'Insight',
            'heading' => 'Nobody was browsing a shop.',
            'body' => 'We read four years of enquiry emails. Not one of them started with a product category — every single one started with a maker, a glaze or a year.',
            'plate' => ['seed' => 1214, 'variant' => 'bone', 'ratio' => '3/2'],
            'alt' => 'A single slab pulled clear of the stack and set against measurement ticks.',
        ],
        [
            'index' => '03',
            'label' => 'Concept',
            'heading' => 'Build the archive. Let it sell.',
            'body' => 'So we stopped designing a storefront and designed an archive indexed by maker, glaze, firing and year. Purchase became something you could do from any point in it, rather than the only route through.',
            'plate' => ['seed' => 1330, 'variant' => 'signal', 'ratio' => '3/2'],
            'alt' => 'Slabs reorganising into an ordered index, one marked in oxide orange.',
        ],
        [
            'index' => '04',
            'label' => 'Design',
            'heading' => 'One template, six states.',
            'body' => 'Every piece resolves through the same layout, shifting weight according to how much is known about it. Forty years of inconsistent photography was reshot against a single seamless grey until the catalogue read as one body of work.',
            'plate' => ['seed' => 1455, 'variant' => 'bone', 'ratio' => '3/2'],
            'alt' => 'Six variations of a single composition set in an even grid.',
        ],
        [
            'index' => '05',
            'label' => 'Build',
            'heading' => 'The 1994 system stayed on.',
            'body' => 'Headless Shopify behind a bespoke archive index, with stock reconciled nightly against an inventory system older than most of the team. Nobody was allowed to switch it off, so we built around it instead of arguing.',
            'plate' => ['seed' => 1572, 'variant' => 'ink', 'ratio' => '3/2'],
            'alt' => 'Interlocking structural slabs forming a load-bearing frame.',
        ],
        [
            'index' => '06',
            'label' => 'Result',
            'heading' => 'It reads like a collection.',
            'body' => 'Revenue per session rose 38% in the first quarter and has not fallen since. The gallery managers now use the archive themselves, which was never in the brief.',
            'plate' => ['seed' => 1699, 'variant' => 'signal', 'ratio' => '3/2'],
            'alt' => 'A resolved helix of evenly spaced slabs against a warm field.',
        ],
    ],

    'metrics' => [
        ['value' => '+38%', 'label' => 'Revenue per session', 'note' => 'Q1 after launch'],
        ['value' => '2,140', 'label' => 'Pieces catalogued', 'note' => '1984 — 2026'],
        ['value' => '11 → 3', 'label' => 'Clicks to purchase', 'note' => 'Median path'],
        ['value' => '0.9s', 'label' => 'Largest contentful paint', 'note' => 'Mobile, 4G'],
    ],
];
