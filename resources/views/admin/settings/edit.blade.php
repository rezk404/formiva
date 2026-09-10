@php
    $socialRows = old('social', $social);
    $legalRows = old('legal_links', $legal['links']);
@endphp

@extends('layouts.admin', [
    'title' => 'Site settings — FORMIVA',
    'breadcrumbs' => [['label' => 'Site settings']],
])

@section('content')

<x-admin.page-header
    kicker="Configuration"
    title="Site settings"
    description="The values that sit outside any single chapter — how the studio names itself, how it is described to a search engine, and how it is reached. Every public page reads from this one record."
/>

<form method="POST" action="{{ route('admin.settings.update') }}">
    @csrf
    @method('PUT')

    <x-admin.panel
        kicker="01"
        title="General"
        description="The studio's own account of itself. The etymology is the argument the name makes: FORM + VIVA."
    >
        <div class="admin-form-grid">
            <x-admin.field name="brand_name" label="Studio name" :value="old('brand_name', $brand['name'])" required autofocus />
            <x-admin.field name="brand_etymology" label="Etymology" :value="old('brand_etymology', $brand['etymology'])" mono required />
            <x-admin.field name="brand_founded" label="Founded" :value="old('brand_founded', $brand['founded'])" mono required />
            <x-admin.field name="brand_discipline" label="Discipline" :value="old('brand_discipline', $brand['discipline'])" required />
        </div>

        <x-admin.field
            name="brand_tagline"
            label="Tagline"
            :value="old('brand_tagline', $brand['tagline'])"
            hint="One line. It appears wherever the studio has to introduce itself in a sentence."
            required
        />
    </x-admin.panel>

    <x-admin.panel
        kicker="02"
        title="SEO defaults"
        description="Used on every page that does not override them, and in link previews shared into a chat or a feed."
    >
        <x-admin.field name="meta_title" label="Default title" :value="old('meta_title', $meta['title'])" required />

        <x-admin.field
            name="meta_description"
            label="Default description"
            type="textarea"
            rows="3"
            :value="old('meta_description', $meta['description'])"
            hint="Around 155 characters is what a search result shows."
            required
        />

        <x-admin.field
            name="meta_keywords"
            label="Keywords"
            type="textarea"
            rows="2"
            :value="old('meta_keywords', $meta['keywords'])"
            hint="Comma separated."
            required
        />

        <div class="admin-form-grid">
            <x-admin.field name="meta_locale" label="Locale" :value="old('meta_locale', $meta['locale'])" mono required />
            <x-admin.field
                name="meta_theme_color"
                label="Theme colour"
                :value="old('meta_theme_color', $meta['theme_color'])"
                hint="Six-digit hex. Paints the browser chrome on mobile, so it should match the hero, not the page ground."
                mono
                required
            />
        </div>
    </x-admin.panel>

    <x-admin.panel kicker="03" title="Contact" description="Shown in the footer and used by the project intake to address a prepared brief.">
        <div class="admin-form-grid">
            <x-admin.field name="contact_email" label="General email" type="email" :value="old('contact_email', $contact['email'])" required />
            <x-admin.field name="contact_new_business" label="New business email" type="email" :value="old('contact_new_business', $contact['new_business'])" hint="Where a completed intake brief is addressed." required />
            <x-admin.field name="contact_phone" label="Phone" :value="old('contact_phone', $contact['phone'])" mono required />
            <x-admin.field name="contact_timezone" label="Timezone" :value="old('contact_timezone', $contact['timezone'])" mono required />
            <x-admin.field name="contact_street" label="Street" :value="old('contact_street', $contact['street'])" required />
            <x-admin.field name="contact_city" label="City" :value="old('contact_city', $contact['city'])" required />
            <x-admin.field name="contact_postcode" label="Postcode" :value="old('contact_postcode', $contact['postcode'])" mono required />
            <x-admin.field name="contact_country" label="Country" :value="old('contact_country', $contact['country'])" required />
        </div>
    </x-admin.panel>

    <x-admin.panel kicker="04" title="Social" description="Listed in the footer, in this order." flush>
        <div class="admin-repeater" data-repeater="social">
            <div data-repeater-list>
                @foreach ($socialRows as $index => $row)
                    <div class="admin-repeater__row" data-repeater-row>
                        <div class="admin-repeater__head">
                            <span class="admin-repeater__index" data-repeater-index>{{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}</span>
                            <div class="admin-repeater__tools">
                                <button type="button" class="admin-btn admin-btn--icon" data-repeater-up aria-label="Move account up"><x-admin.icon name="up" /></button>
                                <button type="button" class="admin-btn admin-btn--icon" data-repeater-down aria-label="Move account down"><x-admin.icon name="down" /></button>
                                <button type="button" class="admin-btn admin-btn--icon" data-repeater-remove aria-label="Remove account"><x-admin.icon name="close" /></button>
                            </div>
                        </div>

                        <div class="admin-form-grid admin-form-grid--3">
                            <x-admin.field :name="'social.'.$index.'.label'" :input="'social['.$index.'][label]'" label="Network" :value="$row['label'] ?? ''" required />
                            <x-admin.field :name="'social.'.$index.'.handle'" :input="'social['.$index.'][handle]'" label="Handle" :value="$row['handle'] ?? ''" mono required />
                            <x-admin.field :name="'social.'.$index.'.url'" :input="'social['.$index.'][url]'" label="URL" type="url" :value="$row['url'] ?? ''" mono required />
                        </div>
                    </div>
                @endforeach
            </div>

            <p class="admin-repeater__empty" data-repeater-empty @if (count($socialRows) > 0) hidden @endif>
                No accounts listed. The footer simply omits the row.
            </p>

            <template>
                <div class="admin-repeater__row" data-repeater-row>
                    <div class="admin-repeater__head">
                        <span class="admin-repeater__index" data-repeater-index>00</span>
                        <div class="admin-repeater__tools">
                            <button type="button" class="admin-btn admin-btn--icon" data-repeater-up aria-label="Move account up"><x-admin.icon name="up" /></button>
                            <button type="button" class="admin-btn admin-btn--icon" data-repeater-down aria-label="Move account down"><x-admin.icon name="down" /></button>
                            <button type="button" class="admin-btn admin-btn--icon" data-repeater-remove aria-label="Remove account"><x-admin.icon name="close" /></button>
                        </div>
                    </div>
                    <div class="admin-form-grid admin-form-grid--3">
                        <div class="admin-field">
                            <label class="admin-field__label">Network</label>
                            <input type="text" class="admin-input" name="social[new][label]" required>
                        </div>
                        <div class="admin-field">
                            <label class="admin-field__label">Handle</label>
                            <input type="text" class="admin-input admin-input--mono" name="social[new][handle]" required>
                        </div>
                        <div class="admin-field">
                            <label class="admin-field__label">URL</label>
                            <input type="url" class="admin-input admin-input--mono" name="social[new][url]" required>
                        </div>
                    </div>
                </div>
            </template>

            <div class="admin-panel__foot">
                <button type="button" class="admin-btn admin-btn--ghost admin-btn--sm" data-repeater-add>
                    <x-admin.icon name="plus" /> Add account
                </button>
            </div>
        </div>
    </x-admin.panel>

    <x-admin.panel kicker="05" title="Legal" description="The entity named in the footer, and the small print beside it." flush>
        <div class="admin-panel__body">
            <x-admin.field name="legal_entity" label="Legal entity" :value="old('legal_entity', $legal['entity'])" required />
        </div>

        <div class="admin-repeater" data-repeater="legal_links">
            <div data-repeater-list>
                @foreach ($legalRows as $index => $row)
                    <div class="admin-repeater__row" data-repeater-row>
                        <div class="admin-repeater__head">
                            <span class="admin-repeater__index" data-repeater-index>{{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}</span>
                            <div class="admin-repeater__tools">
                                <button type="button" class="admin-btn admin-btn--icon" data-repeater-up aria-label="Move link up"><x-admin.icon name="up" /></button>
                                <button type="button" class="admin-btn admin-btn--icon" data-repeater-down aria-label="Move link down"><x-admin.icon name="down" /></button>
                                <button type="button" class="admin-btn admin-btn--icon" data-repeater-remove aria-label="Remove link"><x-admin.icon name="close" /></button>
                            </div>
                        </div>

                        <div class="admin-form-grid">
                            <x-admin.field :name="'legal_links.'.$index.'.label'" :input="'legal_links['.$index.'][label]'" label="Label" :value="$row['label'] ?? ''" required />
                            <x-admin.field :name="'legal_links.'.$index.'.url'" :input="'legal_links['.$index.'][url]'" label="URL" :value="$row['url'] ?? ''" hint="A # is fine until the page exists." mono required />
                        </div>
                    </div>
                @endforeach
            </div>

            <template>
                <div class="admin-repeater__row" data-repeater-row>
                    <div class="admin-repeater__head">
                        <span class="admin-repeater__index" data-repeater-index>00</span>
                        <div class="admin-repeater__tools">
                            <button type="button" class="admin-btn admin-btn--icon" data-repeater-up aria-label="Move link up"><x-admin.icon name="up" /></button>
                            <button type="button" class="admin-btn admin-btn--icon" data-repeater-down aria-label="Move link down"><x-admin.icon name="down" /></button>
                            <button type="button" class="admin-btn admin-btn--icon" data-repeater-remove aria-label="Remove link"><x-admin.icon name="close" /></button>
                        </div>
                    </div>
                    <div class="admin-form-grid">
                        <div class="admin-field">
                            <label class="admin-field__label">Label</label>
                            <input type="text" class="admin-input" name="legal_links[new][label]" required>
                        </div>
                        <div class="admin-field">
                            <label class="admin-field__label">URL</label>
                            <input type="text" class="admin-input admin-input--mono" name="legal_links[new][url]" value="#" required>
                        </div>
                    </div>
                </div>
            </template>

            <div class="admin-panel__foot">
                <button type="button" class="admin-btn admin-btn--ghost admin-btn--sm" data-repeater-add>
                    <x-admin.icon name="plus" /> Add link
                </button>
            </div>
        </div>
    </x-admin.panel>

    <div class="admin-form-actions">
        <x-admin.button>Save settings</x-admin.button>
        <span class="admin-form-actions__spacer"></span>
        <span class="admin-field__hint">Saved as one record. Every field above is written together, so the public site never reads a half-updated shape.</span>
    </div>
</form>

@endsection
