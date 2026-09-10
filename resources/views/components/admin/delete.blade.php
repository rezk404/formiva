@props([
    'action',
    'confirm',
    'title' => 'Delete this record?',
    'accept' => 'Delete',
    'label' => 'Delete',
    'icon' => 'trash',
    'variant' => 'icon',
])

{{--
    A destructive action: a real form, a real DELETE, a real CSRF token.

    The button carries the question rather than the handler, so the shared
    dialog in the layout can name the record. With JavaScript unavailable the
    form still submits — the confirmation is a courtesy, not the safeguard.
    The safeguard is the policy on the other end.
--}}

<form method="POST" action="{{ $action }}" {{ $attributes }}>
    @csrf
    @method('DELETE')

    <button
        type="submit"
        class="admin-btn admin-btn--{{ $variant === 'icon' ? 'icon' : 'danger' }}{{ $variant === 'sm' ? ' admin-btn--sm' : '' }}"
        data-confirm="{{ $confirm }}"
        data-confirm-title="{{ $title }}"
        data-confirm-accept="{{ $accept }}"
        @if ($variant === 'icon') aria-label="{{ $label }}" title="{{ $label }}" @endif
    >
        @if ($variant === 'icon')
            <x-admin.icon :name="$icon" />
        @else
            {{ $label }}
        @endif
    </button>
</form>
