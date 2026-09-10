@extends('layouts.admin', [
    'title' => 'Intake options — FORMIVA',
    'breadcrumbs' => [['label' => 'Intake options']],
])

@section('content')

<x-admin.page-header
    kicker="Configuration"
    title="Intake options"
    description="Every answer a visitor can choose on /start-a-project. The form reads these at request time — nothing about the intake is written into a template — so switching an option off removes it from the next visitor's brief."
>
    <x-slot:actions>
        <x-admin.button href="{{ route('admin.intake.create') }}" icon="plus">New option</x-admin.button>
    </x-slot:actions>
</x-admin.page-header>

<x-admin.tabs
    param="state"
    :current="$filters['state'] ?: 'all'"
    :tabs="[
        'all' => ['label' => 'All', 'count' => $counts['all']],
        'active' => ['label' => 'Offered', 'count' => $counts['active']],
        'inactive' => ['label' => 'Withdrawn', 'count' => $counts['inactive']],
    ]"
/>

<x-admin.filters
    :active="$active"
    :query="$filters['q']"
    :keep="['state' => $filters['state']]"
    placeholder="Search options"
>
    <x-admin.filter-select
        name="kind"
        label="Step"
        any="Every step"
        :value="$filters['kind']"
        :options="collect($kinds)->mapWithKeys(fn ($kind) => [$kind->value => $kind->label()])->all()"
    />
</x-admin.filters>

@foreach ($kinds as $kind)
    @php
        $options = $groups[$kind->value] ?? collect();

        // A step is shown when it is the one being filtered to, or when no
        // filter is narrowing the screen. Under a search, an empty step is
        // simply absent rather than five empty panels reporting nothing.
        $show = ($filters['kind'] === '' || $filters['kind'] === $kind->value)
            && ($options->isNotEmpty() || count($active) === 0);
    @endphp

    @if ($show)
        <div class="admin-intake-group">
            <x-admin.panel :kicker="$kind->step()" :title="$kind->label()" flush>
                <x-slot:actions>
                    <x-admin.button
                        href="{{ route('admin.intake.create', ['kind' => $kind->value]) }}"
                        variant="ghost"
                        size="sm"
                        icon="plus"
                    >Add</x-admin.button>
                </x-slot:actions>

                @forelse ($options as $option)
                    <div class="admin-option {{ $option->is_active ? '' : 'is-inactive' }}">
                        <div>
                            <a class="admin-option__label" href="{{ route('admin.intake.edit', $option) }}">{{ $option->label }}</a>
                            <span class="admin-option__value">
                                {{ $option->value }}
                                @if ($kind->usesGroups())
                                    · group: {{ $option->group }}
                                @endif
                            </span>
                        </div>

                        <x-admin.badge :tone="$option->is_active ? 'positive' : 'muted'">
                            {{ $option->is_active ? 'Offered' : 'Withdrawn' }}
                        </x-admin.badge>

                        <div class="admin-option__tools">
                            <x-admin.move :action="route('admin.intake.move', $option)" :label="$option->label" />

                            <form method="POST" action="{{ route('admin.intake.toggle', $option) }}">
                                @csrf
                                <button
                                    type="submit"
                                    class="admin-btn admin-btn--icon"
                                    aria-label="{{ $option->is_active ? 'Withdraw' : 'Offer' }} {{ $option->label }}"
                                    title="{{ $option->is_active ? 'Stop offering this' : 'Offer this again' }}"
                                >
                                    <x-admin.icon :name="$option->is_active ? 'eye-off' : 'eye'" />
                                </button>
                            </form>

                            <x-admin.button href="{{ route('admin.intake.edit', $option) }}" variant="ghost" size="sm">Edit</x-admin.button>

                            <x-admin.delete
                                :action="route('admin.intake.destroy', $option)"
                                :label="'Delete '.$option->label"
                                title="Delete this option?"
                                :confirm="'“'.$option->label.'” will no longer be offered, and briefs already submitted keep the answer as recorded. Withdrawing it instead keeps the record.'"
                            />
                        </div>
                    </div>
                @empty
                    <x-admin.empty
                        icon="intake"
                        :title="'No options for '.strtolower($kind->label())"
                        description="This step of the intake has nothing to offer, so a visitor cannot answer it."
                    >
                        <x-slot:actions>
                            <x-admin.button href="{{ route('admin.intake.create', ['kind' => $kind->value]) }}" icon="plus">Add an option</x-admin.button>
                        </x-slot:actions>
                    </x-admin.empty>
                @endforelse
            </x-admin.panel>
        </div>
    @endif
@endforeach

@if (count($active) > 0 && $groups->isEmpty())
    <div class="admin-panel">
        <x-admin.empty
            icon="search"
            title="Nothing matches that"
            description="No intake option answers to this search and filter combination."
        >
            <x-slot:actions>
                <x-admin.button href="{{ route('admin.intake.index') }}" variant="ghost">Clear filters</x-admin.button>
            </x-slot:actions>
        </x-admin.empty>
    </div>
@endif

@endsection
