@php
    $disciplines = old('disciplines', implode(', ', $project->disciplines ?? []));
    $stack = old('stack', implode(', ', $project->stack ?? []));
    $selectedServices = old('services', $project->exists ? $project->services->pluck('id')->all() : []);
    $selectedGallery = old('gallery_media', $gallery->pluck('id')->all());
@endphp

<form method="POST" action="{{ $action }}">
    @csrf
    @if ($method !== 'POST') @method($method) @endif

    <div class="admin-layout">
        <div>
            <x-admin.panel kicker="01" title="Identity" description="The public-facing name, context and address for this piece of work.">
                <div class="admin-form-grid">
                    <x-admin.field name="name" label="Project name" :value="old('name', $project->name)" required autofocus />
                    <x-admin.field name="title" label="Page title" :value="old('title', $project->title)" required />
                    <x-admin.field name="slug" label="Slug" :value="old('slug', $project->slug)" hint="Changing a published slug changes its public URL." mono required />
                    <x-admin.field name="index_label" label="Index" :value="old('index_label', $project->index_label)" mono required />
                    <x-admin.field name="year" label="Year" type="number" :value="old('year', $project->year ?: now()->year)" required />
                    <x-admin.field name="alt" label="Visual alt text" :value="old('alt', $project->alt)" hint="Describe the project visual for someone who cannot see it." required />
                </div>
                <div class="admin-form-grid">
                    <x-admin.field name="category_id" label="Category" type="select" :value="old('category_id', $project->category_id)" :options="$categories->pluck('name', 'id')->all()" prompt="Uncategorised" />
                    <x-admin.field name="client_id" label="Client" type="select" :value="old('client_id', $project->client_id)" :options="$clients->pluck('name', 'id')->all()" prompt="Unassigned" />
                </div>
            </x-admin.panel>

            <x-admin.panel kicker="02" title="Positioning" description="The concise argument for why this work matters, followed by the fuller account.">
                <x-admin.field name="statement" label="Statement" type="textarea" rows="3" :value="old('statement', $project->statement)" required />
                <x-admin.field name="description" label="Description" type="textarea" rows="5" :value="old('description', $project->description)" required />
            </x-admin.panel>

            <x-admin.panel kicker="03" title="Project story" description="Keep the narrative concrete: the problem, the move and what changed.">
                <x-admin.field name="challenge" label="Challenge" type="textarea" rows="4" :value="old('challenge', $project->challenge)" required />
                <x-admin.field name="solution" label="Solution" type="textarea" rows="4" :value="old('solution', $project->solution)" required />
                <x-admin.field name="outcome" label="Outcome" type="textarea" rows="4" :value="old('outcome', $project->outcome)" required />
                <div class="admin-form-grid">
                    <x-admin.field name="result_value" label="Result value" :value="old('result_value', $project->result_value)" optional />
                    <x-admin.field name="result_label" label="Result label" :value="old('result_label', $project->result_label)" optional />
                </div>
            </x-admin.panel>

            <x-admin.panel kicker="04" title="Delivery" description="The shape of the engagement and the systems used to make it real.">
                <div class="admin-form-grid">
                    <x-admin.field name="stage" label="Project stage" type="select" :value="old('stage', $project->stage?->value)" :options="collect(\App\Enums\ProjectStage::cases())->mapWithKeys(fn ($stage) => [$stage->value => str_replace('_', ' ', ucfirst($stage->value))])->all()" />
                    <x-admin.field name="started_at" label="Started at" type="date" :value="old('started_at', $project->started_at?->format('Y-m-d'))" optional />
                    <x-admin.field name="completed_at" label="Completed at" type="date" :value="old('completed_at', $project->completed_at?->format('Y-m-d'))" optional />
                </div>
                <x-admin.field name="disciplines" label="Disciplines" :value="$disciplines" hint="Separate entries with commas." optional />
                <x-admin.field name="stack" label="Technology stack" :value="$stack" hint="Separate entries with commas." optional />
                <div class="admin-field">
                    <span class="admin-field__label">Services</span>
                    <div class="admin-form-grid">
                        @foreach ($services as $service)
                            <label class="admin-check"><input type="checkbox" name="services[]" value="{{ $service->id }}" @checked(in_array($service->id, $selectedServices))> <span>{{ $service->title }}</span></label>
                        @endforeach
                    </div>
                </div>
            </x-admin.panel>

            <x-admin.panel kicker="05" title="Visual" description="Use an existing media record where one exists; generated plates remain a valid fallback.">
                <div class="admin-form-grid">
                    <x-admin.field name="cover_media_id" label="Cover media" type="select" :value="old('cover_media_id', $project->cover_media_id)" :options="$media->mapWithKeys(fn ($item) => [$item->id => $item->original_name ?: $item->filename])->all()" prompt="Generated plate" />
                    <x-admin.field name="plate_variant" label="Plate variant" :value="old('plate_variant', $project->plate_variant)" required />
                    <x-admin.field name="plate_ratio" label="Plate ratio" :value="old('plate_ratio', $project->plate_ratio)" required />
                    <x-admin.field name="plate_seed" label="Plate seed" type="number" :value="old('plate_seed', $project->plate_seed)" required />
                </div>
                <div class="admin-field">
                    <span class="admin-field__label">Gallery</span>
                    <p class="admin-field__hint">Select existing media in display order. Gallery management stays deliberately lightweight until the Media phase.</p>
                    <div class="admin-form-grid">
                        @forelse ($media as $item)
                            <label class="admin-check">
                                <input type="checkbox" name="gallery_media[]" value="{{ $item->id }}" @checked(in_array($item->id, $selectedGallery))>
                                <span>{{ $item->original_name ?: $item->filename }}</span>
                            </label>
                        @empty
                            <span class="admin-field__hint">No media records yet.</span>
                        @endforelse
                    </div>
                </div>
            </x-admin.panel>

            <x-admin.panel kicker="06" title="Discovery" description="Search metadata for the public project page.">
                <x-admin.field name="meta_title" label="Meta title" :value="old('meta_title', $project->meta_title)" optional />
                <x-admin.field name="meta_description" label="Meta description" type="textarea" rows="3" :value="old('meta_description', $project->meta_description)" optional />
            </x-admin.panel>
        </div>

        <aside class="admin-layout__aside">
            @if ($project->exists)
                <x-admin.record-summary :record="$project" :url="route('projects.show', $project->slug)" :rows="['Position' => $project->position, 'Client' => $project->client?->name ?? 'Unassigned', 'Case study' => $project->caseStudy ? 'Attached' : 'None']" />
            @else
                <x-admin.panel kicker="Before you save" title="A body of work takes shape here">
                    <p class="admin-field__hint">Save a draft while the story is still moving, then publish it explicitly when the public page is ready.</p>
                </x-admin.panel>
            @endif
        </aside>
    </div>

    <div class="admin-form-actions">
        @if ($project->exists)
            <x-admin.button>{{ $submit }}</x-admin.button>
        @else
            <x-admin.button name="intent" value="draft">Save as draft</x-admin.button>
            <x-admin.button name="intent" value="publish" variant="publish">Save &amp; publish</x-admin.button>
        @endif
        <x-admin.button href="{{ route('admin.projects.index') }}" variant="quiet" type="button">Cancel</x-admin.button>
    </div>
</form>
