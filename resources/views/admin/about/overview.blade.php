@php
    $storyRows = old('story', $story);
    $positionRows = old('positions', $positions->map(fn ($position) => [
        'id' => $position->id,
        'index_label' => $position->index_label,
        'title' => $position->title,
        'body' => $position->body,
    ])->all());
    $headlineRows = old('headline', $headline);
@endphp

@extends('layouts.admin', [
    'title' => 'About — FORMIVA',
    'breadcrumbs' => [['label' => 'About', 'url' => route('admin.about.overview')], ['label' => 'Overview']],
])

@section('content')

<x-admin.page-header
    kicker="About"
    title="Who FORMIVA is"
    description="The opening of /studio: how the studio introduces itself, and the positions it will be held to. Positions are things the studio actually refuses or insists on — if it could appear on any agency's site, it is a value, not a position."
>
    <x-slot:actions>
        <x-admin.button href="{{ route('studio') }}" variant="ghost" icon="external">View the page</x-admin.button>
    </x-slot:actions>
</x-admin.page-header>

<x-admin.about-nav current="overview" :counts="$counts" />

<form method="POST" action="{{ route('admin.about.overview.update') }}">
    @csrf
    @method('PUT')

    <x-admin.panel
        kicker="01"
        title="Opening"
        description="The eyebrow, the two-line headline, and the story underneath them."
    >
        <x-admin.field name="eyebrow" label="Eyebrow" :value="old('eyebrow', $eyebrow)" mono required />

        <div class="admin-form-grid">
            <x-admin.field
                name="headline.0"
                input="headline[0]"
                label="Headline, line one"
                :value="$headlineRows[0] ?? ''"
                required
            />
            <x-admin.field
                name="headline.1"
                input="headline[1]"
                label="Headline, line two"
                :value="$headlineRows[1] ?? ''"
                hint="Set in italic on the public page, so it should read as the turn in the sentence."
                required
            />
        </div>

        <div class="admin-repeater admin-repeater--plain" data-repeater="story">
            <span class="admin-field__label">Story</span>
            <p class="admin-field__hint">One paragraph per row. The homepage shows the first two; /studio shows them all.</p>

            <div data-repeater-list>
                @foreach ($storyRows as $index => $paragraph)
                    <div class="admin-repeater__row" data-repeater-row>
                        <div class="admin-repeater__head">
                            <span class="admin-repeater__index" data-repeater-index>{{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}</span>
                            <div class="admin-repeater__tools">
                                <button type="button" class="admin-btn admin-btn--icon" data-repeater-up aria-label="Move paragraph up"><x-admin.icon name="up" /></button>
                                <button type="button" class="admin-btn admin-btn--icon" data-repeater-down aria-label="Move paragraph down"><x-admin.icon name="down" /></button>
                                <button type="button" class="admin-btn admin-btn--icon" data-repeater-remove aria-label="Remove paragraph"><x-admin.icon name="close" /></button>
                            </div>
                        </div>
                        <x-admin.field
                            :name="'story.'.$index"
                            :input="'story['.$index.']'"
                            label="Paragraph"
                            type="textarea"
                            rows="3"
                            :value="$paragraph"
                            required
                        />
                    </div>
                @endforeach
            </div>

            <template>
                <div class="admin-repeater__row" data-repeater-row>
                    <div class="admin-repeater__head">
                        <span class="admin-repeater__index" data-repeater-index>00</span>
                        <div class="admin-repeater__tools">
                            <button type="button" class="admin-btn admin-btn--icon" data-repeater-up aria-label="Move paragraph up"><x-admin.icon name="up" /></button>
                            <button type="button" class="admin-btn admin-btn--icon" data-repeater-down aria-label="Move paragraph down"><x-admin.icon name="down" /></button>
                            <button type="button" class="admin-btn admin-btn--icon" data-repeater-remove aria-label="Remove paragraph"><x-admin.icon name="close" /></button>
                        </div>
                    </div>
                    <div class="admin-field">
                        <label class="admin-field__label">Paragraph</label>
                        <textarea class="admin-textarea" name="story[new]" rows="3" required></textarea>
                    </div>
                </div>
            </template>

            <div class="admin-repeater__foot">
                <button type="button" class="admin-btn admin-btn--ghost admin-btn--sm" data-repeater-add>
                    <x-admin.icon name="plus" /> Add paragraph
                </button>
            </div>
        </div>
    </x-admin.panel>

    <x-admin.panel
        kicker="02"
        title="Positions"
        description="Each one is a thing the studio actually refuses or insists on, phrased so a client could hold it to them."
        flush
    >
        <div class="admin-repeater" data-repeater="positions">
            <div data-repeater-list>
                @foreach ($positionRows as $index => $row)
                    <div class="admin-repeater__row" data-repeater-row>
                        <div class="admin-repeater__head">
                            <span class="admin-repeater__index" data-repeater-index>{{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}</span>
                            <div class="admin-repeater__tools">
                                <button type="button" class="admin-btn admin-btn--icon" data-repeater-up aria-label="Move position up"><x-admin.icon name="up" /></button>
                                <button type="button" class="admin-btn admin-btn--icon" data-repeater-down aria-label="Move position down"><x-admin.icon name="down" /></button>
                                <button type="button" class="admin-btn admin-btn--icon" data-repeater-remove aria-label="Remove position"><x-admin.icon name="close" /></button>
                            </div>
                        </div>

                        <input type="hidden" name="positions[{{ $index }}][id]" value="{{ $row['id'] ?? '' }}">

                        <div class="admin-form-grid admin-form-grid--index">
                            <x-admin.field
                                :name="'positions.'.$index.'.index_label'"
                                :input="'positions['.$index.'][index_label]'"
                                label="Index"
                                :value="$row['index_label'] ?? ''"
                                mono
                                required
                            />
                            <x-admin.field
                                :name="'positions.'.$index.'.title'"
                                :input="'positions['.$index.'][title]'"
                                label="Position"
                                :value="$row['title'] ?? ''"
                                required
                            />
                        </div>

                        <x-admin.field
                            :name="'positions.'.$index.'.body'"
                            :input="'positions['.$index.'][body]'"
                            label="Explanation"
                            type="textarea"
                            rows="2"
                            :value="$row['body'] ?? ''"
                            required
                        />
                    </div>
                @endforeach
            </div>

            <template>
                <div class="admin-repeater__row" data-repeater-row>
                    <div class="admin-repeater__head">
                        <span class="admin-repeater__index" data-repeater-index>00</span>
                        <div class="admin-repeater__tools">
                            <button type="button" class="admin-btn admin-btn--icon" data-repeater-up aria-label="Move position up"><x-admin.icon name="up" /></button>
                            <button type="button" class="admin-btn admin-btn--icon" data-repeater-down aria-label="Move position down"><x-admin.icon name="down" /></button>
                            <button type="button" class="admin-btn admin-btn--icon" data-repeater-remove aria-label="Remove position"><x-admin.icon name="close" /></button>
                        </div>
                    </div>
                    <input type="hidden" name="positions[new][id]" value="">
                    <div class="admin-form-grid admin-form-grid--index">
                        <div class="admin-field">
                            <label class="admin-field__label">Index</label>
                            <input type="text" class="admin-input admin-input--mono" name="positions[new][index_label]" required>
                        </div>
                        <div class="admin-field">
                            <label class="admin-field__label">Position</label>
                            <input type="text" class="admin-input" name="positions[new][title]" required>
                        </div>
                    </div>
                    <div class="admin-field">
                        <label class="admin-field__label">Explanation</label>
                        <textarea class="admin-textarea" name="positions[new][body]" rows="2" required></textarea>
                    </div>
                </div>
            </template>

            <div class="admin-panel__foot">
                <button type="button" class="admin-btn admin-btn--ghost admin-btn--sm" data-repeater-add>
                    <x-admin.icon name="plus" /> Add position
                </button>
                <span class="admin-field__hint">Order here is the order on the public page.</span>
            </div>
        </div>
    </x-admin.panel>

    <div class="admin-form-actions">
        <x-admin.button>Save overview</x-admin.button>
        <x-admin.button href="{{ route('admin.about.stats') }}" variant="quiet" type="button">Statistics →</x-admin.button>
        <span class="admin-form-actions__spacer"></span>
        <span class="admin-field__hint">Saved in one transaction — the public page never sees a half-written chapter.</span>
    </div>
</form>

@endsection
