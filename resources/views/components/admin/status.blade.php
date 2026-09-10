@props(['status', 'at' => null])

{{--
    A content status, always drawn the same way. Tone comes from the enum, so
    "scheduled" cannot be blue on one screen and grey on another.

    Pass :at with the record's published_at to have a scheduled item say when,
    which is the only question anyone has when they see that state.
--}}

<span class="admin-badge admin-badge--{{ $status->tone() }}" title="{{ $status->description() }}">
    {{ $status->label() }}
    @if ($at && $status === \App\Enums\ContentStatus::Scheduled)
        <span class="admin-sr">, </span>{{ $at->format('j M') }}
    @endif
</span>
