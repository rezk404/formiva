@php
    /*
     | Rows come from old input when validation sent the form back, and from
     | the record otherwise. Either way they arrive as plain arrays so the
     | markup below does not have to care which.
     */
    $rows = old('items', $items->map(fn ($item) => [
        'id' => $item->id,
        'title' => $item->title,
        'form' => $item->form,
        'summary' => $item->summary,
    ])->all());
@endphp

<form method="POST" action="{{ $action }}">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="admin-layout">
        <div>

            <x-admin.panel
                kicker="01"
                title="Basic information"
                description="How the pillar is named and indexed on /services and in the homepage capability list."
            >
                <div class="admin-form-grid">
                    <x-admin.field
                        name="title"
                        label="Title"
                        :value="old('title', $service->title)"
                        data-slug-source="f-slug"
                        required
                        autofocus
                    />

                    <x-admin.field
                        name="slug"
                        label="Slug"
                        :value="old('slug', $service->slug)"
                        hint="Used as the anchor on the services page. Leave blank to derive it."
                        mono
                        :data-slug-locked="$service->exists ? 'true' : 'false'"
                    />

                    <x-admin.field
                        name="index_label"
                        label="Index"
                        :value="old('index_label', $service->index_label)"
                        hint="The two-digit number set beside the title, e.g. 01."
                        mono
                        required
                    />

                    <x-admin.field
                        name="form"
                        label="Form keyword"
                        :value="old('form', $service->form)"
                        hint="The geometry the 3D system adopts while this pillar is open — modular, networked, layered."
                        mono
                        required
                    />
                </div>
            </x-admin.panel>

            <x-admin.panel
                kicker="02"
                title="Narrative content"
                description="The argument for this pillar, in the order a reader meets it: the promise, the reason, the result, the terms."
            >
                <x-admin.field
                    name="lede"
                    label="Lede"
                    type="textarea"
                    rows="2"
                    :value="old('lede', $service->lede)"
                    hint="One sentence. It has to earn the expansion, so it is not a summary."
                    required
                />

                <x-admin.field
                    name="why"
                    label="Why it matters"
                    type="textarea"
                    rows="4"
                    :value="old('why', $service->why)"
                    hint="The problem this pillar exists to answer, stated as something a client would recognise."
                    required
                />

                <x-admin.field
                    name="outcome"
                    label="Outcome"
                    type="textarea"
                    rows="2"
                    :value="old('outcome', $service->outcome)"
                    hint="What the client is left holding."
                    required
                />

                <x-admin.field
                    name="note"
                    label="Note"
                    :value="old('note', $service->note)"
                    hint="The practical footnote — typical duration, shape of engagement."
                    required
                />
            </x-admin.panel>

            <x-admin.panel
                kicker="03"
                title="Service items"
                description="The capabilities inside this pillar. They expand under it on the public page, in this order."
                flush
            >
                <div class="admin-repeater" data-repeater="items">
                    <div data-repeater-list>
                        @foreach ($rows as $index => $row)
                            <div class="admin-repeater__row" data-repeater-row>
                                <div class="admin-repeater__head">
                                    <span class="admin-repeater__index" data-repeater-index>
                                        {{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}
                                    </span>
                                    <div class="admin-repeater__tools">
                                        <button type="button" class="admin-btn admin-btn--icon" data-repeater-up aria-label="Move capability up"><x-admin.icon name="up" /></button>
                                        <button type="button" class="admin-btn admin-btn--icon" data-repeater-down aria-label="Move capability down"><x-admin.icon name="down" /></button>
                                        <button type="button" class="admin-btn admin-btn--icon" data-repeater-remove aria-label="Remove capability"><x-admin.icon name="close" /></button>
                                    </div>
                                </div>

                                <input type="hidden" name="items[{{ $index }}][id]" value="{{ $row['id'] ?? '' }}">

                                <div class="admin-form-grid">
                                    <x-admin.field
                                        :name="'items.'.$index.'.title'"
                                        :input="'items['.$index.'][title]'"
                                        label="Capability"
                                        :value="$row['title'] ?? ''"
                                        required
                                    />
                                    <x-admin.field
                                        :name="'items.'.$index.'.form'"
                                        :input="'items['.$index.'][form]'"
                                        label="Form keyword"
                                        :value="$row['form'] ?? ''"
                                        mono
                                        required
                                    />
                                </div>

                                <x-admin.field
                                    :name="'items.'.$index.'.summary'"
                                    :input="'items['.$index.'][summary]'"
                                    label="Summary"
                                    type="textarea"
                                    rows="2"
                                    :value="$row['summary'] ?? ''"
                                    required
                                />
                            </div>
                        @endforeach
                    </div>

                    <p class="admin-repeater__empty" data-repeater-empty @if (count($rows) > 0) hidden @endif>
                        No capabilities yet. A pillar with none renders as a heading with nothing under it.
                    </p>

                    <template>
                        <div class="admin-repeater__row" data-repeater-row>
                            <div class="admin-repeater__head">
                                <span class="admin-repeater__index" data-repeater-index>00</span>
                                <div class="admin-repeater__tools">
                                    <button type="button" class="admin-btn admin-btn--icon" data-repeater-up aria-label="Move capability up"><x-admin.icon name="up" /></button>
                                    <button type="button" class="admin-btn admin-btn--icon" data-repeater-down aria-label="Move capability down"><x-admin.icon name="down" /></button>
                                    <button type="button" class="admin-btn admin-btn--icon" data-repeater-remove aria-label="Remove capability"><x-admin.icon name="close" /></button>
                                </div>
                            </div>

                            <input type="hidden" name="items[new][id]" value="">

                            <div class="admin-form-grid">
                                <div class="admin-field">
                                    <label class="admin-field__label">Capability <span class="admin-field__required" aria-hidden="true">*</span></label>
                                    <input type="text" class="admin-input" name="items[new][title]" required>
                                </div>
                                <div class="admin-field">
                                    <label class="admin-field__label">Form keyword <span class="admin-field__required" aria-hidden="true">*</span></label>
                                    <input type="text" class="admin-input admin-input--mono" name="items[new][form]" value="modular" required>
                                </div>
                            </div>

                            <div class="admin-field">
                                <label class="admin-field__label">Summary <span class="admin-field__required" aria-hidden="true">*</span></label>
                                <textarea class="admin-textarea" name="items[new][summary]" rows="2" required></textarea>
                            </div>
                        </div>
                    </template>

                    <div class="admin-panel__foot">
                        <button type="button" class="admin-btn admin-btn--ghost admin-btn--sm" data-repeater-add>
                            <x-admin.icon name="plus" /> Add capability
                        </button>
                        <span class="admin-field__hint">Order here is the order on the public page.</span>
                    </div>
                </div>
            </x-admin.panel>

            <x-admin.panel
                kicker="04"
                title="SEO"
                description="Overrides for this pillar's anchor. Left empty, the site falls back to the global defaults in Site settings."
            >
                <x-admin.field
                    name="meta_title"
                    label="Meta title"
                    :value="old('meta_title', $service->meta_title)"
                    optional
                />
                <x-admin.field
                    name="meta_description"
                    label="Meta description"
                    type="textarea"
                    rows="2"
                    :value="old('meta_description', $service->meta_description)"
                    hint="Around 155 characters reads well in a search result."
                    optional
                />
            </x-admin.panel>
        </div>

        <aside class="admin-layout__aside">
            @if ($service->exists)
                <x-admin.record-summary
                    :record="$service"
                    :url="route('services.index').'#'.$service->slug"
                    :rows="['Capabilities' => $items->count(), 'Position' => $service->position]"
                />
            @else
                <x-admin.panel kicker="Before you save" title="Nothing is live yet">
                    <p class="admin-field__hint">
                        A new service is written first and published second. Save it as a draft to keep
                        working, or publish it straight away if the narrative is finished.
                    </p>
                </x-admin.panel>
            @endif
        </aside>
    </div>

    <div class="admin-form-actions">
        @if ($service->exists)
            <x-admin.button>{{ $submit }}</x-admin.button>
        @else
            {{-- Two intents, two buttons. The state a record starts in is a
                 decision, and a dropdown that defaults to "draft" hides it. --}}
            <x-admin.button name="intent" value="draft">Save as draft</x-admin.button>
            <x-admin.button name="intent" value="publish" variant="publish">Save &amp; publish</x-admin.button>
        @endif

        <x-admin.button href="{{ route('admin.services.index') }}" variant="quiet" type="button">Cancel</x-admin.button>
    </div>
</form>
