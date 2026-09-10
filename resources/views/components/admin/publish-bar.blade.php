@props(['record', 'action', 'transitions' => [], 'label' => 'entry'])

@php
    // Fully qualified rather than imported: a `use` statement in a component
    // lands after Blade's own compiled prologue, and this file is not the
    // place to find out how that resolves.
    $scheduled = \App\Enums\ContentStatus::Scheduled;
    $archived = \App\Enums\ContentStatus::Archived;

    $status = $record->status;
    $at = $record->published_at;
    $live = $record->isLive();

    // The one sentence that answers "so is this on the site or not?".
    $summary = match (true) {
        $live => 'Live on the site'.($at ? ', dated '.$at->format('j M Y') : '').'.',
        $status === $scheduled && $at => 'Goes live '.$at->format('j F Y \a\t H:i').'.',
        $status === $archived => 'Retired. Kept for the record, hidden from the site.',
        default => 'Not on the site. Only the studio can see this.',
    };

    $schedule = \App\Support\Publishing::SCHEDULE;
    $publish = \App\Support\Publishing::PUBLISH;

    $labels = collect($transitions)
        ->mapWithKeys(fn (string $transition): array => [$transition => \App\Support\Publishing::label($transition)])
        ->all();

    $dialogId = 'schedule-'.$record->getKey();
@endphp

{{--
    The publishing control area.

    A sibling of the editor form, never a child of it: a form inside a form is
    dropped by the browser, and the button that looks like it publishes
    quietly submits the editor instead. Every transition below is its own
    small POST, and only the ones this record can actually accept are drawn.
--}}

<section class="admin-publish" aria-label="Publishing">
    <div class="admin-publish__state">
        <x-admin.status :status="$status" :at="$at" />
        <p class="admin-publish__summary">{{ $summary }}</p>
    </div>

    <div class="admin-publish__actions">
        @foreach ($transitions as $transition)
            @if ($transition === $schedule)
                <button
                    type="button"
                    class="admin-btn admin-btn--ghost admin-btn--sm"
                    data-dialog-open="{{ $dialogId }}"
                >
                    <x-admin.icon name="calendar" />
                    {{ $status === $scheduled ? 'Reschedule' : 'Schedule' }}
                </button>
            @else
                <form method="POST" action="{{ $action }}">
                    @csrf
                    <input type="hidden" name="action" value="{{ $transition }}">
                    <button
                        type="submit"
                        @class([
                            'admin-btn',
                            'admin-btn--sm',
                            'admin-btn--publish' => $transition === $publish,
                            'admin-btn--ghost' => $transition !== $publish,
                        ])
                    >{{ $labels[$transition] ?? $transition }}</button>
                </form>
            @endif
        @endforeach
    </div>
</section>

@if (in_array($schedule, $transitions, true))
    <dialog class="admin-dialog" id="{{ $dialogId }}" aria-labelledby="{{ $dialogId }}-title">
        <form method="POST" action="{{ $action }}">
            @csrf
            <input type="hidden" name="action" value="{{ $schedule }}">

            <div class="admin-dialog__body">
                <h2 id="{{ $dialogId }}-title">Schedule this {{ $label }}</h2>
                <p>
                    It stays invisible until the moment you choose, then publishes itself.
                    Times are in the studio's timezone.
                </p>

                <x-admin.datetime
                    name="published_at"
                    label="Go live at"
                    :value="old('published_at', $at?->isFuture() ? $at->format('Y-m-d\TH:i') : null)"
                    :min="now()->format('Y-m-d\TH:i')"
                    required
                />
            </div>

            <div class="admin-dialog__actions">
                <button type="button" class="admin-btn admin-btn--ghost" data-dialog-close>Cancel</button>
                <button type="submit" class="admin-btn admin-btn--publish">Schedule it</button>
            </div>
        </form>
    </dialog>
@endif
