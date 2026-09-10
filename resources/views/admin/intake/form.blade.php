@php
    $kindOptions = collect($kinds)->mapWithKeys(fn ($kind) => [$kind->value => $kind->label()])->all();
    $currentKind = $option->kind;
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
                title="The option"
                description="The label is what the visitor reads. The value is what lands in the brief, so it is worth keeping stable once briefs have been submitted with it."
            >
                <x-admin.field
                    name="kind"
                    label="Intake step"
                    type="select"
                    :value="old('kind', $currentKind?->value)"
                    :options="$kindOptions"
                    hint="Which of the nine steps this answer belongs to."
                    required
                />

                <x-admin.field
                    name="label"
                    label="Label"
                    :value="old('label', $option->label)"
                    hint="Shown on the form exactly as written."
                    required
                    autofocus
                />

                <x-admin.field
                    name="value"
                    label="Stored value"
                    :value="old('value', $option->displayValue())"
                    hint="Leave blank to derive it from the label. Budget ranges store their group alongside the value, which is handled for you."
                    mono
                    optional
                />
            </x-admin.panel>

            <x-admin.panel
                kicker="02"
                title="Grouping"
                description="Project types declare a group; budget ranges are offered by it. Every other step is a single list and uses the shared group automatically."
            >
                {{-- A text input with suggestions rather than a select: an
                     existing group is one keystroke away, and a genuinely new
                     one — a new family of work — is still possible. --}}
                <x-admin.field
                    name="group"
                    label="Project-type group"
                    :value="old('group', $option->group)"
                    hint="Lowercase and hyphenated, e.g. web, commerce, systems. Ignored for steps that are a single flat list."
                    list="intake-groups"
                    mono
                />

                <datalist id="intake-groups">
                    @foreach ($projectGroups as $group)
                        <option value="{{ $group }}"></option>
                    @endforeach
                </datalist>
            </x-admin.panel>
        </div>

        <aside class="admin-layout__aside">
            <x-admin.panel kicker="03" title="Availability">
                <label class="admin-check">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $option->is_active))>
                    <span class="admin-check__text">
                        <b>Offer this option</b>
                        <span>Withdrawn options disappear from the public form but keep their record, so briefs already submitted still read correctly.</span>
                    </span>
                </label>
            </x-admin.panel>
        </aside>
    </div>

    <div class="admin-form-actions">
        <x-admin.button>{{ $submit }}</x-admin.button>
        <x-admin.button href="{{ route('admin.intake.index') }}" variant="quiet" type="button">Cancel</x-admin.button>
    </div>
</form>
