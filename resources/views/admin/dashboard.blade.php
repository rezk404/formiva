@extends('layouts.admin', ['title' => 'Workspace — FORMIVA'])

@section('content')

{{--
    The order is the order of a working morning: what needs a decision, then
    what the studio touched last, then how the whole body of content stands.
    Attention leads because it is the only part anyone has to act on.
--}}

<section class="admin-masthead">
    <div class="admin-masthead__identity">
        <p class="admin-masthead__mark">FORM<span>+</span>VIVA</p>
        <h1>{{ $user->name }}.</h1>
        <p class="admin-masthead__line">
            {{ count($attention) === 0
                ? 'Nothing is waiting. The site is saying what you last told it to say.'
                : Str::plural('decision', count($attention)).' to make — '.count($attention).' below.' }}
        </p>
    </div>

    <dl class="admin-masthead__meta">
        <div>
            <dt>Today</dt>
            <dd>{{ now()->format('l j F') }}</dd>
        </div>
        <div>
            <dt>Signed in as</dt>
            <dd>{{ $user->role->label() }}</dd>
        </div>
        <div>
            <dt>Public content</dt>
            <dd>{{ config('formiva.content_source') === 'database' ? 'This workspace' : 'Repository files' }}</dd>
        </div>
    </dl>

    @if (count($actions) > 0)
        <div class="admin-masthead__actions">
            @foreach (array_slice($actions, 0, 2) as $action)
                <a class="admin-btn admin-btn--onink" href="{{ $action['href'] }}">
                    <x-admin.icon :name="$action['icon']" />
                    {{ $action['label'] }}
                </a>
            @endforeach
        </div>
    @endif
</section>

<section class="admin-metrics" aria-label="Studio at a glance">
    @foreach ($metrics as $metric)
        @if ($metric['href'])
            <a class="admin-metric" href="{{ $metric['href'] }}">
                <span class="admin-metric__label">{{ $metric['label'] }}</span>
                <span class="admin-metric__value">{{ $metric['value'] }}</span>
                <span class="admin-metric__detail">{{ $metric['detail'] }}</span>
            </a>
        @else
            <div class="admin-metric">
                <span class="admin-metric__label">{{ $metric['label'] }}</span>
                <span class="admin-metric__value">{{ $metric['value'] }}</span>
                <span class="admin-metric__detail">{{ $metric['detail'] }}</span>
            </div>
        @endif
    @endforeach
</section>

<div class="admin-dash">
    <div class="admin-dash__column">

        <x-admin.panel
            kicker="Attention"
            title="What needs a decision"
            description="Only things a person can act on. An empty panel here is the good state, not a missing feature."
            flush
        >
            @forelse ($attention as $signal)
                <div class="admin-signal admin-signal--{{ $signal['tone'] }}">
                    <span class="admin-signal__rule" aria-hidden="true"></span>
                    <div class="admin-signal__body">
                        <h3>{{ $signal['title'] }}</h3>
                        <p>{{ $signal['body'] }}</p>
                    </div>
                    <a class="admin-signal__action" href="{{ $signal['href'] }}">
                        {{ $signal['action'] }}
                        <span aria-hidden="true">→</span>
                    </a>
                </div>
            @empty
                <x-admin.empty
                    icon="check"
                    title="Nothing is waiting"
                    description="No draft stuck, no schedule overdue, nothing on the site missing a field it needs."
                />
            @endforelse
        </x-admin.panel>

        <x-admin.panel kicker="Recent" title="Last touched" flush>
            <x-slot:actions>
                <x-admin.button href="{{ route('admin.insights.index') }}" variant="quiet" size="sm">
                    All insights →
                </x-admin.button>
            </x-slot:actions>

            @forelse ($recent as $item)
                <a class="admin-recent__item" href="{{ $item['href'] }}">
                    <span class="admin-recent__kind">{{ $item['kind'] }}</span>
                    <span class="admin-recent__main">
                        <span class="admin-recent__title">{{ $item['title'] }}</span>
                        <span class="admin-recent__meta">
                            <span>{{ $item['meta'] }}</span>
                            @if ($item['by'])<span>{{ $item['by'] }}</span>@endif
                            <span>{{ $item['updated']?->diffForHumans() }}</span>
                        </span>
                    </span>
                    <x-admin.status :status="$item['status']" :at="$item['at']" />
                </a>
            @empty
                <x-admin.empty
                    icon="insights"
                    title="Nothing written yet"
                    description="Services and insights are the two things the studio publishes. Start with either."
                >
                    <x-slot:actions>
                        <x-admin.button href="{{ route('admin.insights.create') }}" icon="plus">Write the first insight</x-admin.button>
                    </x-slot:actions>
                </x-admin.empty>
            @endforelse
        </x-admin.panel>
    </div>

    <div class="admin-dash__column">

        <x-admin.panel kicker="Content health" title="Published, and not">
            @foreach ($health as $type => $group)
                @php $counts = $group['counts']; @endphp

                <div class="admin-health">
                    <div class="admin-health__head">
                        <h3><a href="{{ route($group['route']) }}">{{ $type }}</a></h3>
                        <span class="admin-health__total">{{ $counts['total'] }}</span>
                    </div>

                    @if ($counts['total'] > 0)
                        <div class="admin-health__bar" role="img" aria-label="{{ $counts['published'] }} published, {{ $counts['scheduled'] }} scheduled, {{ $counts['draft'] }} draft, {{ $counts['archived'] }} archived">
                            @foreach (['published', 'scheduled', 'draft', 'archived'] as $state)
                                @if ($counts[$state] > 0)
                                    <span class="admin-health__segment admin-health__segment--{{ $state }}" style="flex-grow: {{ $counts[$state] }}"></span>
                                @endif
                            @endforeach
                        </div>

                        <ul class="admin-health__key">
                            @foreach (['published', 'scheduled', 'draft', 'archived'] as $state)
                                @if ($counts[$state] > 0)
                                    <li>
                                        <i class="admin-health__segment--{{ $state }}"></i>
                                        <b>{{ $counts[$state] }}</b>
                                        <a href="{{ route($group['route'], ['status' => $state]) }}">{{ ucfirst($state) }}</a>
                                    </li>
                                @endif
                            @endforeach
                        </ul>
                    @else
                        <p class="admin-field__hint">Nothing written yet.</p>
                    @endif
                </div>
            @endforeach
        </x-admin.panel>

        @if (count($actions) > 0)
            <x-admin.panel kicker="Start" title="Quick actions" flush>
                <div class="admin-actions">
                    @foreach ($actions as $action)
                        <a class="admin-action" href="{{ $action['href'] }}">
                            <x-admin.icon :name="$action['icon']" />
                            <span class="admin-action__text">
                                <span class="admin-action__label">{{ $action['label'] }}</span>
                                <span class="admin-action__meta">{{ $action['meta'] }}</span>
                            </span>
                            <span class="admin-action__arrow" aria-hidden="true">→</span>
                        </a>
                    @endforeach
                </div>
            </x-admin.panel>
        @endif
    </div>
</div>

@endsection
