@extends('layouts.admin', [
    'title' => 'Process — FORMIVA',
    'breadcrumbs' => [['label' => 'About', 'url' => route('admin.about.overview')], ['label' => 'Process']],
])

@section('content')

<x-admin.page-header
    kicker="About"
    title="The process"
    description="Six stages, one continuous line. The public page draws them as a single structure, so the order and the overlaps are the argument — not decoration."
>
    <x-slot:actions>
        <x-admin.button href="{{ route('admin.process.create') }}" icon="plus">Add a stage</x-admin.button>
    </x-slot:actions>
</x-admin.page-header>

<x-admin.about-nav current="process" :counts="$about" />

<div class="admin-layout">
    <div>
        <x-admin.panel
            kicker="Sequence"
            title="Stages"
            description="Each stage sits on the timeline between its start and end, as a percentage of the whole engagement. Stages marked as overlapping deliberately run alongside the one before."
            flush
        >
            @forelse ($stages as $stage)
                <article class="admin-stage">
                    <div class="admin-stage__index">
                        <span class="admin-stage__dot {{ $stage->overlap ? 'admin-stage__dot--overlap' : '' }}" aria-hidden="true"></span>
                        {{ $stage->index_label }}
                    </div>

                    <div>
                        <a class="admin-stage__title" href="{{ route('admin.process.edit', $stage) }}">{{ $stage->title }}</a>
                        <span class="admin-stage__window">{{ $stage->window }}</span>
                        <p class="admin-stage__body">{{ $stage->body }}</p>
                        <span class="admin-stage__output">{{ $stage->output }}</span>

                        <div
                            class="admin-stage__span"
                            role="img"
                            aria-label="Runs from {{ $stage->span_start }}% to {{ $stage->span_end }}% of the engagement{{ $stage->overlap ? ', overlapping the previous stage' : '' }}"
                        >
                            <i
                                class="{{ $stage->overlap ? 'is-overlap' : '' }}"
                                style="left: {{ $stage->span_start }}%; width: {{ max(1, $stage->spanWidth()) }}%"
                            ></i>
                        </div>
                    </div>

                    <div class="admin-stage__tools">
                        <x-admin.move
                            :action="route('admin.process.move', $stage)"
                            :label="$stage->title"
                            :first="$loop->first"
                            :last="$loop->last"
                        />
                        <x-admin.button href="{{ route('admin.process.edit', $stage) }}" variant="ghost" size="sm">Edit</x-admin.button>
                        <x-admin.delete
                            :action="route('admin.process.destroy', $stage)"
                            :label="'Delete '.$stage->title"
                            title="Remove this stage?"
                            :confirm="'“'.$stage->title.'” will be removed from the sequence and the public page redrawn without it.'"
                        />
                    </div>
                </article>
            @empty
                <x-admin.empty
                    icon="process"
                    title="No stages yet"
                    description="The process chapter draws a line through however many stages exist. With none, the page renders an empty rule."
                >
                    <x-slot:actions>
                        <x-admin.button href="{{ route('admin.process.create') }}" icon="plus">Add the first stage</x-admin.button>
                    </x-slot:actions>
                </x-admin.empty>
            @endforelse
        </x-admin.panel>
    </div>

    <aside class="admin-layout__aside">
        <x-admin.panel
            kicker="Framing"
            title="Chapter opening"
            description="The eyebrow, headline and lede around the diagram."
        >
            <form method="POST" action="{{ route('admin.process.framing') }}">
                @csrf
                @method('PUT')

                <x-admin.field name="eyebrow" label="Eyebrow" :value="old('eyebrow', $eyebrow)" mono required />

                <x-admin.field
                    name="headline.0"
                    input="headline[0]"
                    label="Headline, line one"
                    :value="old('headline.0', $headline[0] ?? '')"
                    required
                />

                <x-admin.field
                    name="headline.1"
                    input="headline[1]"
                    label="Headline, line two"
                    :value="old('headline.1', $headline[1] ?? '')"
                    hint="Set in italic on the public page."
                    required
                />

                <x-admin.field
                    name="lede"
                    label="Lede"
                    type="textarea"
                    rows="3"
                    :value="old('lede', $lede)"
                    hint="One sentence explaining why the stages overlap the way they do."
                    required
                />

                <x-admin.button class="admin-form-submit" block>Save framing</x-admin.button>
            </form>
        </x-admin.panel>

        <x-admin.panel kicker="Coverage" title="The timeline">
            <p class="admin-field__hint">
                The sequence currently runs from {{ $span['start'] }}% to {{ $span['end'] }}% of an engagement across
                {{ $stages->count() }} {{ Str::plural('stage', $stages->count()) }},
                {{ $stages->where('overlap', true)->count() }} of which overlap the stage before.
            </p>
        </x-admin.panel>
    </aside>
</div>

@endsection
