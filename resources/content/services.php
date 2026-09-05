<?php

/*
|--------------------------------------------------------------------------
| Services — the capability index
|--------------------------------------------------------------------------
|
| Three pillars, not six flat cards: FORMIVA's real commercial promise is
| that the same studio ships the product and the system that runs behind
| it, so the index is grouped the way a client actually buys — by the kind
| of problem, not by discipline.
|
| `form` is the geometric behaviour the shared 3D system adopts while the
| pillar is active — see resources/js/three/states.js. Each item may carry
| its own `form` too, used when a single capability inside a pillar is
| highlighted (hover/focus on the expanded list).
|
*/

return [
    [
        'index' => '01',
        'slug' => 'digital-products',
        'title' => 'Digital Products',
        'form' => 'modular',
        'lede' => 'The thing itself — the site, the store, the application your customers or team actually use.',
        'why' => 'Most digital work fails before launch: scope no one agreed to, a build that never matches the brief. We keep strategy, design and engineering in the same room so what ships is what was promised.',
        'outcome' => 'A product that survives real users, on a codebase your next developer can actually read.',
        'note' => 'Twelve to twenty weeks, concept to launch.',
        'items' => [
            [
                'title' => 'Corporate Websites',
                'form' => 'modular',
                'summary' => 'Marketing and institutional sites built to represent the business accurately, load fast, and be easy to hand over.',
            ],
            [
                'title' => 'E-Commerce',
                'form' => 'networked',
                'summary' => 'Storefronts judged on conversion, checkout speed and repeat purchase — Shopify, headless or custom.',
            ],
            [
                'title' => 'Web Applications',
                'form' => 'layered',
                'summary' => 'Product interfaces for internal tools, customer portals and SaaS — designed as a system, not a set of screens.',
            ],
            [
                'title' => 'Custom Software',
                'form' => 'compressed',
                'summary' => 'Purpose-built software where an off-the-shelf product does not fit the way the business actually works.',
            ],
            [
                'title' => 'Mobile Applications',
                'form' => 'compressed',
                'summary' => 'iOS and Android apps designed for the thumb, the tunnel and the two-bar signal, not the boardroom demo.',
            ],
        ],
    ],
    [
        'index' => '02',
        'slug' => 'business-systems',
        'title' => 'Business Systems',
        'form' => 'structural',
        'lede' => 'The operational layer most agencies will not touch — ERP, CRM and the automation that connects them.',
        'why' => 'A beautiful storefront on top of a spreadsheet-run back office does not scale. We implement and customise the systems that let a growing business actually run itself.',
        'outcome' => 'Fewer spreadsheets, less duplicate entry, and a system your team can operate without us in the room.',
        'note' => 'Eight to eighteen weeks, discovery to go-live.',
        'items' => [
            [
                'title' => 'ERP Solutions',
                'form' => 'structural',
                'summary' => 'Enterprise resource planning scoped to how the business actually operates — inventory, finance and operations in one system of record.',
            ],
            [
                'title' => 'Odoo Implementation',
                'form' => 'structural',
                'summary' => 'End-to-end Odoo rollout: modules selected for the business, data migrated, and the team trained to run it.',
            ],
            [
                'title' => 'Odoo Customization',
                'form' => 'structural',
                'summary' => 'Custom modules, workflows and integrations for teams already on Odoo whose process has outgrown the defaults.',
            ],
            [
                'title' => 'CRM Solutions',
                'form' => 'networked',
                'summary' => 'Pipeline, contact and follow-up systems built around how the sales team actually sells.',
            ],
            [
                'title' => 'Business Automation',
                'form' => 'networked',
                'summary' => 'The manual handoffs between systems — quotes, approvals, stock, invoicing — replaced with rules that run themselves.',
            ],
        ],
    ],
    [
        'index' => '03',
        'slug' => 'experience',
        'title' => 'Digital Experience',
        'form' => 'interface',
        'lede' => 'The layer that makes a serious product feel like one — research, interface and motion with a point of view.',
        'why' => 'A capable system with a confusing interface gets abandoned by the people it was built for. Design is where trust is won or lost in the first ten seconds.',
        'outcome' => 'An identity and an interface your team is proud to put in front of a client.',
        'note' => 'Runs alongside product and systems work, or stands alone.',
        'items' => [
            [
                'title' => 'UI / UX',
                'form' => 'interface',
                'summary' => 'Research, information architecture and interface design — handover includes tokens, not just screens.',
            ],
            [
                'title' => 'Brand Identity',
                'form' => 'interface',
                'summary' => 'Names, marks and systems built to hold up across a product, a pitch deck and a shopfront sign.',
            ],
            [
                'title' => 'Creative Development',
                'form' => 'layered',
                'summary' => 'Front-end craft for the moments that carry the brand — launch pages, product tours, campaign sites.',
            ],
            [
                'title' => 'Motion / Interactive',
                'form' => 'layered',
                'summary' => 'Purposeful animation and WebGL used to explain, not decorate — spent only where it changes understanding.',
            ],
        ],
    ],
];
