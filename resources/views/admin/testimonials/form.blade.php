<form method="POST" action="{{ $action }}">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="admin-layout">
        <div>
            <x-admin.panel
                kicker="01"
                title="The quote"
                description="A quote that only says the studio was good to work with earns nothing. Keep the one that says something specific — a judgement, a handover, a decision."
            >
                <x-admin.field
                    name="quote"
                    label="Quote"
                    type="textarea"
                    rows="5"
                    :value="old('quote', $testimonial->quote)"
                    hint="Set without quotation marks — the page adds them."
                    required
                    autofocus
                />
            </x-admin.panel>

            <x-admin.panel kicker="02" title="Attribution">
                <div class="admin-form-grid">
                    <x-admin.field
                        name="author_name"
                        label="Name"
                        :value="old('author_name', $testimonial->author_name)"
                        required
                    />
                    <x-admin.field
                        name="author_role"
                        label="Role"
                        :value="old('author_role', $testimonial->author_role)"
                        hint="As they would introduce themselves, e.g. VP Product."
                        required
                    />
                    <x-admin.field
                        name="company"
                        label="Company"
                        :value="old('company', $testimonial->company)"
                        required
                    />
                </div>
            </x-admin.panel>

            <x-admin.panel
                kicker="03"
                title="Connections"
                description="Optional. Linking a quote to the project it came from lets the site place it beside that work."
            >
                <div class="admin-form-grid">
                    <x-admin.field
                        name="client_id"
                        label="Client"
                        type="select"
                        prompt="Not linked"
                        :value="old('client_id', $testimonial->client_id)"
                        :options="$clients->pluck('name', 'id')->all()"
                    />
                    <x-admin.field
                        name="project_id"
                        label="Project"
                        type="select"
                        prompt="Not linked"
                        :value="old('project_id', $testimonial->project_id)"
                        :options="$projects->pluck('name', 'id')->all()"
                    />
                </div>
            </x-admin.panel>
        </div>

        <aside class="admin-layout__aside">
            <x-admin.panel kicker="04" title="Publication">
                <label class="admin-check">
                    <input type="hidden" name="is_published" value="0">
                    <input type="checkbox" name="is_published" value="1" @checked(old('is_published', $testimonial->is_published))>
                    <span class="admin-check__text">
                        <b>Show on the site</b>
                        <span>The homepage shows the first published quote in this order; the rest appear where the layout calls for proof.</span>
                    </span>
                </label>
            </x-admin.panel>
        </aside>
    </div>

    <div class="admin-form-actions">
        <x-admin.button>{{ $submit }}</x-admin.button>
        <x-admin.button href="{{ route('admin.testimonials.index') }}" variant="quiet" type="button">Cancel</x-admin.button>
    </div>
</form>
