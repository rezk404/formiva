<form method="POST" action="{{ $action }}">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="admin-panel">
        <div class="admin-panel__body">
            <div class="admin-form-grid">
                <x-admin.field
                    name="name"
                    label="Name"
                    :value="old('name', $category->name)"
                    hint="Shown on the public site exactly as written."
                    data-slug-source="f-slug"
                    required
                    autofocus
                />

                <x-admin.field
                    name="slug"
                    label="Slug"
                    :value="old('slug', $category->slug)"
                    hint="Lowercase, hyphenated. Leave blank to derive it from the name."
                    mono
                    :data-slug-locked="$category->exists ? 'true' : 'false'"
                />

                <x-admin.field
                    name="type"
                    label="Used for"
                    type="select"
                    :value="old('type', $category->type?->value)"
                    :options="collect($types)->mapWithKeys(fn ($type) => [$type->value => $type->label()])->all()"
                    hint="Slugs are unique per type, so the same name can label both a family of work and a journal category."
                    required
                />
            </div>
        </div>
    </div>

    <div class="admin-form-actions">
        <x-admin.button>{{ $submit }}</x-admin.button>
        <x-admin.button href="{{ route('admin.categories.index') }}" variant="quiet" type="button">Cancel</x-admin.button>

        @if ($category->exists)
            <span class="admin-form-actions__spacer"></span>
        @endif
    </div>
</form>
