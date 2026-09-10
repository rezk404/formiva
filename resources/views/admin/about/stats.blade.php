@php
    $statRows = old('stats', $stats->map(fn ($stat) => [
        'id' => $stat->id,
        'value' => $stat->value,
        'suffix' => $stat->suffix,
        'label' => $stat->label,
        'note' => $stat->note,
    ])->all());
@endphp

@extends('layouts.admin', [
    'title' => 'Stats — About — FORMIVA',
    'breadcrumbs' => [['label' => 'About', 'url' => route('admin.about.overview')], ['label' => 'Stats']],
])

@section('content')

<x-admin.page-header
    kicker="About"
    title="The numbers"
    description="Four figures carry the studio's scale on both the homepage and /studio. A statistic without a note is a number without a claim, so every one has to say what it counts."
/>

<x-admin.about-nav current="stats" :counts="$counts" />

<form method="POST" action="{{ route('admin.about.stats.update') }}">
    @csrf
    @method('PUT')

    <x-admin.panel kicker="Figures" title="Statistics" flush>
        <div class="admin-repeater" data-repeater="stats">
            <div data-repeater-list>
                @foreach ($statRows as $index => $row)
                    <div class="admin-repeater__row" data-repeater-row>
                        <div class="admin-repeater__head">
                            <span class="admin-repeater__index" data-repeater-index>{{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}</span>
                            <div class="admin-repeater__tools">
                                <button type="button" class="admin-btn admin-btn--icon" data-repeater-up aria-label="Move statistic up"><x-admin.icon name="up" /></button>
                                <button type="button" class="admin-btn admin-btn--icon" data-repeater-down aria-label="Move statistic down"><x-admin.icon name="down" /></button>
                                <button type="button" class="admin-btn admin-btn--icon" data-repeater-remove aria-label="Remove statistic"><x-admin.icon name="close" /></button>
                            </div>
                        </div>

                        <input type="hidden" name="stats[{{ $index }}][id]" value="{{ $row['id'] ?? '' }}">

                        <div class="admin-form-grid admin-form-grid--stat">
                            <x-admin.field
                                :name="'stats.'.$index.'.value'"
                                :input="'stats['.$index.'][value]'"
                                label="Value"
                                :value="$row['value'] ?? ''"
                                mono
                                required
                            />
                            <x-admin.field
                                :name="'stats.'.$index.'.suffix'"
                                :input="'stats['.$index.'][suffix]'"
                                label="Suffix"
                                :value="$row['suffix'] ?? ''"
                                hint="Such as + or %. May be empty."
                                mono
                            />
                            <x-admin.field
                                :name="'stats.'.$index.'.label'"
                                :input="'stats['.$index.'][label]'"
                                label="Label"
                                :value="$row['label'] ?? ''"
                                required
                            />
                            <x-admin.field
                                :name="'stats.'.$index.'.note'"
                                :input="'stats['.$index.'][note]'"
                                label="Note"
                                :value="$row['note'] ?? ''"
                                hint="What it counts, and since when."
                                required
                            />
                        </div>
                    </div>
                @endforeach
            </div>

            <p class="admin-repeater__empty" data-repeater-empty @if (count($statRows) > 0) hidden @endif>
                No statistics yet. The row is drawn from these, so the chapter renders without it.
            </p>

            <template>
                <div class="admin-repeater__row" data-repeater-row>
                    <div class="admin-repeater__head">
                        <span class="admin-repeater__index" data-repeater-index>00</span>
                        <div class="admin-repeater__tools">
                            <button type="button" class="admin-btn admin-btn--icon" data-repeater-up aria-label="Move statistic up"><x-admin.icon name="up" /></button>
                            <button type="button" class="admin-btn admin-btn--icon" data-repeater-down aria-label="Move statistic down"><x-admin.icon name="down" /></button>
                            <button type="button" class="admin-btn admin-btn--icon" data-repeater-remove aria-label="Remove statistic"><x-admin.icon name="close" /></button>
                        </div>
                    </div>
                    <input type="hidden" name="stats[new][id]" value="">
                    <div class="admin-form-grid admin-form-grid--stat">
                        <div class="admin-field">
                            <label class="admin-field__label">Value</label>
                            <input type="text" class="admin-input admin-input--mono" name="stats[new][value]" required>
                        </div>
                        <div class="admin-field">
                            <label class="admin-field__label">Suffix</label>
                            <input type="text" class="admin-input admin-input--mono" name="stats[new][suffix]" value="">
                        </div>
                        <div class="admin-field">
                            <label class="admin-field__label">Label</label>
                            <input type="text" class="admin-input" name="stats[new][label]" required>
                        </div>
                        <div class="admin-field">
                            <label class="admin-field__label">Note</label>
                            <input type="text" class="admin-input" name="stats[new][note]" required>
                        </div>
                    </div>
                </div>
            </template>

            <div class="admin-panel__foot">
                <button type="button" class="admin-btn admin-btn--ghost admin-btn--sm" data-repeater-add>
                    <x-admin.icon name="plus" /> Add statistic
                </button>
                <span class="admin-field__hint">Order here is the order on the public page.</span>
            </div>
        </div>
    </x-admin.panel>

    <div class="admin-form-actions">
        <x-admin.button>Save statistics</x-admin.button>
        <x-admin.button href="{{ route('admin.about.overview') }}" variant="quiet" type="button">← Overview</x-admin.button>
    </div>
</form>

@endsection
