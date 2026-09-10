<form method="POST" action="{{ $action }}">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="admin-layout">
        <div>
            <x-admin.panel kicker="01" title="Who they are">
                <div class="admin-form-grid">
                    <x-admin.field
                        name="name"
                        label="Name"
                        :value="old('name', $member->name)"
                        data-slug-source="f-slug"
                        required
                        autofocus
                    />

                    <x-admin.field
                        name="slug"
                        label="Slug"
                        :value="old('slug', $member->slug)"
                        hint="Also seeds the portrait plate, so the same person always gets the same artwork."
                        mono
                        :data-slug-locked="$member->exists ? 'true' : 'false'"
                    />

                    <x-admin.field
                        name="role"
                        label="Role"
                        :value="old('role', $member->role)"
                        hint="How they would introduce themselves, e.g. Technical Director."
                        required
                    />

                    <x-admin.field
                        name="since_year"
                        label="At the studio since"
                        type="number"
                        min="1990"
                        :max="date('Y')"
                        :value="old('since_year', $member->since_year)"
                        required
                    />
                </div>

                <x-admin.field
                    name="bio"
                    label="Bio"
                    type="textarea"
                    rows="4"
                    :value="old('bio', $member->bio)"
                    hint="One or two sentences. Written without pronouns keeps them short and assumes nothing the entry does not state."
                    required
                />

                <x-admin.field
                    name="email"
                    label="Work email"
                    type="email"
                    :value="old('email', $member->email)"
                    hint="Internal only — never rendered on the public site."
                    optional
                />
            </x-admin.panel>

            <x-admin.panel
                kicker="02"
                title="Portrait"
                description="The studio page draws a plate rather than showing a photograph. Leave the seed empty and one is derived from the slug, which is stable across deploys."
            >
                <div class="admin-form-grid--3 admin-form-grid">
                    <x-admin.field
                        name="plate_seed"
                        label="Plate seed"
                        type="number"
                        min="1000"
                        max="9999"
                        :value="old('plate_seed', $member->plate_seed)"
                        mono
                        optional
                    />
                    <x-admin.field
                        name="plate_variant"
                        label="Variant"
                        type="select"
                        prompt="Default (ink)"
                        :value="old('plate_variant', $member->plate_variant)"
                        :options="['ink' => 'Ink', 'bone' => 'Bone', 'signal' => 'Signal']"
                    />
                    <x-admin.field
                        name="plate_ratio"
                        label="Ratio"
                        type="select"
                        prompt="Default (3/4)"
                        :value="old('plate_ratio', $member->plate_ratio)"
                        :options="['3/4' => '3 / 4', '1/1' => '1 / 1', '4/3' => '4 / 3']"
                    />
                </div>

                <x-admin.field
                    name="alt"
                    label="Portrait description"
                    :value="old('alt', $member->alt)"
                    hint="Read aloud in place of the plate. Leave empty and one is written from the name."
                    optional
                />
            </x-admin.panel>
        </div>

        <aside class="admin-layout__aside">
            <x-admin.panel kicker="03" title="Visibility">
                <label class="admin-check">
                    <input type="hidden" name="is_published" value="0">
                    <input type="checkbox" name="is_published" value="1" @checked(old('is_published', $member->is_published))>
                    <span class="admin-check__text">
                        <b>Show on /studio</b>
                        <span>Hidden people keep their record but do not appear anywhere on the public site.</span>
                    </span>
                </label>
            </x-admin.panel>
        </aside>
    </div>

    <div class="admin-form-actions">
        <x-admin.button>{{ $submit }}</x-admin.button>
        <x-admin.button href="{{ route('admin.team.index') }}" variant="quiet" type="button">Cancel</x-admin.button>

        @if ($member->exists && $member->is_published)
            <span class="admin-form-actions__spacer"></span>
            <x-admin.button href="{{ route('studio') }}" variant="ghost" type="button" icon="external">View the studio page</x-admin.button>
        @endif
    </div>
</form>
