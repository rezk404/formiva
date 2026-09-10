<form method="POST" action="{{ $action }}">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="admin-layout">
        <div>
            <x-admin.panel kicker="01" title="The stage">
                <div class="admin-form-grid">
                    <x-admin.field
                        name="index_label"
                        label="Index"
                        :value="old('index_label', $stage->index_label)"
                        hint="Two digits, e.g. 03."
                        mono
                        required
                        autofocus
                    />
                    <x-admin.field
                        name="title"
                        label="Title"
                        :value="old('title', $stage->title)"
                        hint="One verb, ideally. Discover, Strategise, Design."
                        required
                    />
                </div>

                <x-admin.field
                    name="window"
                    label="Time window"
                    :value="old('window', $stage->window)"
                    hint="How it is described to a client, e.g. Weeks 4 — 11, or Ongoing."
                    required
                />

                <x-admin.field
                    name="body"
                    label="What happens"
                    type="textarea"
                    rows="4"
                    :value="old('body', $stage->body)"
                    required
                />

                <x-admin.field
                    name="output"
                    label="Output"
                    :value="old('output', $stage->output)"
                    hint="What the client is handed at the end of it — the thing, not the document about the thing."
                    required
                />
            </x-admin.panel>
        </div>

        <aside class="admin-layout__aside">
            <x-admin.panel
                kicker="02"
                title="On the timeline"
                description="Positions the stage on the drawn line, as percentages of the whole engagement. Stages are allowed to overlap; that is the point of the diagram."
            >
                <div class="admin-form-grid">
                    <x-admin.field
                        name="span_start"
                        label="Starts at"
                        type="number"
                        min="0"
                        max="100"
                        :value="old('span_start', $stage->span_start)"
                        mono
                        required
                    />
                    <x-admin.field
                        name="span_end"
                        label="Ends at"
                        type="number"
                        min="0"
                        max="100"
                        :value="old('span_end', $stage->span_end)"
                        mono
                        required
                    />
                </div>

                <x-admin.field
                    name="weight"
                    label="Marker weight"
                    type="number"
                    step="0.05"
                    min="0.05"
                    max="9.99"
                    :value="old('weight', $stage->weight)"
                    hint="How much vertical mass the stage carries in the drawing. 1.00 is the heaviest stage in the current sequence."
                    mono
                    required
                />

                <label class="admin-check">
                    <input type="hidden" name="overlap" value="0">
                    <input type="checkbox" name="overlap" value="1" @checked(old('overlap', $stage->overlap))>
                    <span class="admin-check__text">
                        <b>Overlaps the previous stage</b>
                        <span>Marks this stage as deliberately running alongside the one before it rather than after.</span>
                    </span>
                </label>
            </x-admin.panel>
        </aside>
    </div>

    <div class="admin-form-actions">
        <x-admin.button>{{ $submit }}</x-admin.button>
        <x-admin.button href="{{ route('admin.process.index') }}" variant="quiet" type="button">Cancel</x-admin.button>
    </div>
</form>
