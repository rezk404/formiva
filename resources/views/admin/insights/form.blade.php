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
                description="How the entry is addressed and introduced. The standfirst is the line that has to earn the click — it is not a summary."
            >
                <x-admin.field
                    name="title"
                    label="Title"
                    :value="old('title', $insight->title)"
                    data-slug-source="f-slug"
                    required
                    autofocus
                />

                <div class="admin-form-grid">
                    <x-admin.field
                        name="slug"
                        label="Slug"
                        :value="old('slug', $insight->slug)"
                        hint="The public URL: /insights/{slug}. Changing it on a published entry breaks inbound links."
                        mono
                        :data-slug-locked="$insight->exists ? 'true' : 'false'"
                    />

                    <x-admin.field
                        name="index_label"
                        label="Index"
                        :value="old('index_label', $insight->index_label)"
                        hint="The numeral set beside the entry, e.g. 01."
                        mono
                        required
                    />
                </div>

                <x-admin.field
                    name="dek"
                    label="Standfirst"
                    type="textarea"
                    rows="2"
                    :value="old('dek', $insight->dek)"
                    hint="One opinionated sentence. It is what appears in the journal index and in link previews."
                    required
                />
            </x-admin.panel>

            <x-admin.panel
                kicker="02"
                title="Article content"
                description="Plain prose. The public entry renders the body as text, so anything typed here appears exactly as typed — no markup is interpreted."
            >
                <x-admin.field
                    name="body"
                    label="Body"
                    type="textarea"
                    rows="16"
                    :value="old('body', $insight->body)"
                    hint="Reading time is calculated from this if you leave it blank below."
                    required
                />
            </x-admin.panel>

            <x-admin.panel
                kicker="03"
                title="Classification"
                description="Where the entry sits in the journal, who wrote it, and how its generated cover plate is drawn."
            >
                <div class="admin-form-grid">
                    <x-admin.field
                        name="category_id"
                        label="Category"
                        type="select"
                        prompt="Uncategorised"
                        :value="old('category_id', $insight->category_id)"
                        :options="$categories->pluck('name', 'id')->all()"
                        hint="Only categories filed under Insight appear here."
                    />

                    <x-admin.field
                        name="author_id"
                        label="Author"
                        type="select"
                        prompt="No author"
                        :value="old('author_id', $insight->author_id)"
                        :options="$authors->pluck('name', 'id')->all()"
                    />

                    <x-admin.field
                        name="reading_minutes"
                        label="Reading time"
                        type="number"
                        min="1"
                        max="255"
                        :value="old('reading_minutes', $insight->reading_minutes)"
                        hint="Minutes. Leave empty and it is estimated from the body."
                        optional
                    />

                    <x-admin.field
                        name="plate_seed"
                        label="Plate seed"
                        type="number"
                        min="1000"
                        max="9999"
                        :value="old('plate_seed', $insight->plate_seed)"
                        hint="Four digits. Leave empty for one derived from the slug."
                        mono
                        optional
                    />

                    <x-admin.field
                        name="plate_variant"
                        label="Plate variant"
                        type="select"
                        :value="old('plate_variant', $insight->plate_variant ?? 'ink')"
                        :options="['ink' => 'Ink', 'bone' => 'Bone', 'signal' => 'Signal']"
                        required
                    />

                    <x-admin.field
                        name="plate_ratio"
                        label="Plate ratio"
                        type="select"
                        :value="old('plate_ratio', $insight->plate_ratio ?? '16/10')"
                        :options="['16/10' => '16 / 10', '16/9' => '16 / 9', '4/3' => '4 / 3', '3/2' => '3 / 2']"
                        required
                    />
                </div>

                <x-admin.field
                    name="alt"
                    label="Cover description"
                    :value="old('alt', $insight->alt)"
                    hint="Read aloud in place of the artwork. Describe the shape, not the fact that it is an image."
                    required
                />
            </x-admin.panel>

            <x-admin.panel
                kicker="04"
                title="SEO"
                description="Overrides for this entry's page. Left empty, the title and standfirst are used."
            >
                <x-admin.field
                    name="meta_title"
                    label="Meta title"
                    :value="old('meta_title', $insight->meta_title)"
                    optional
                />
                <x-admin.field
                    name="meta_description"
                    label="Meta description"
                    type="textarea"
                    rows="2"
                    :value="old('meta_description', $insight->meta_description)"
                    optional
                />
            </x-admin.panel>
        </div>

        <aside class="admin-layout__aside">
            @if ($insight->exists)
                <x-admin.record-summary
                    :record="$insight"
                    :url="route('insights.show', $insight->slug)"
                    :rows="[
                        'Author' => $insight->author?->name ?? 'None',
                        'Category' => $insight->category?->name ?? 'Uncategorised',
                        'Reading' => $insight->reading_minutes.' min',
                    ]"
                />
            @else
                <x-admin.panel kicker="Before you save" title="Nothing is live yet">
                    <p class="admin-field__hint">
                        The journal reads in reverse chronology, so publishing an entry also dates it.
                        Save it as a draft while it is still being written.
                    </p>
                </x-admin.panel>
            @endif
        </aside>
    </div>

    <div class="admin-form-actions">
        @if ($insight->exists)
            <x-admin.button>{{ $submit }}</x-admin.button>
        @else
            <x-admin.button name="intent" value="draft">Save as draft</x-admin.button>
            <x-admin.button name="intent" value="publish" variant="publish">Save &amp; publish</x-admin.button>
        @endif

        <x-admin.button href="{{ route('admin.insights.index') }}" variant="quiet" type="button">Cancel</x-admin.button>
    </div>
</form>
