@props(['action', 'label', 'first' => false, 'last' => false])

{{--
    Reorder controls: two buttons, each a form of its own.

    Chosen over a drag handle deliberately. Every ordered list here is short,
    and two buttons are operable from the keyboard, announce what they do, and
    cannot leave an order half-applied if a pointer is released in the wrong
    place.
--}}

<form method="POST" action="{{ $action }}" class="admin-move" {{ $attributes }}>
    @csrf
    <button
        type="submit"
        name="direction"
        value="up"
        class="admin-btn admin-btn--icon"
        aria-label="Move {{ $label }} up"
        title="Move up"
        @disabled($first)
    ><x-admin.icon name="up" /></button>

    <button
        type="submit"
        name="direction"
        value="down"
        class="admin-btn admin-btn--icon"
        aria-label="Move {{ $label }} down"
        title="Move down"
        @disabled($last)
    ><x-admin.icon name="down" /></button>
</form>
