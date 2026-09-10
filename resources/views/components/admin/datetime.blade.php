@props([
    'name',
    'label',
    'value' => null,
    'hint' => null,
    'min' => null,
    'required' => false,
    'id' => null,
])

{{--
    A date and time, chosen once.

    The control itself is the native datetime-local input. That is a
    deliberate choice, not a shortcut: the platform picker is already
    keyboard-operable, localised, screen-reader-correct and familiar on every
    phone — and no hand-rolled calendar in a CMS this size will beat it on any
    of those. What the platform does not give is context, so this adds it:
    the field is dressed to match the rest of the workspace, three presets
    cover the answers people actually pick, and the chosen moment is written
    back out in full so nobody has to decode a numeric field to check they
    scheduled the right day.

    Everything below degrades: with JavaScript off the presets are simply
    absent and the input still works.
--}}

@php
    $fieldId = $id ?? 'f-'.trim(preg_replace('/[^a-z0-9]+/i', '-', $name), '-');
    $error = $errors->first($name);
    $hintId = $hint ? $fieldId.'-hint' : null;
    $errorId = $error ? $fieldId.'-error' : null;
    $readoutId = $fieldId.'-readout';
    $described = trim(implode(' ', array_filter([$hintId, $readoutId, $errorId])));
@endphp

<div class="admin-field admin-datetime" data-datetime>
    <label class="admin-field__label" for="{{ $fieldId }}">
        <span>{{ $label }}</span>
        @if ($required)
            <span class="admin-field__required" aria-hidden="true">*</span>
            <span class="admin-sr">(required)</span>
        @endif
    </label>

    <div class="admin-datetime__control">
        <input
            type="datetime-local"
            id="{{ $fieldId }}"
            name="{{ $name }}"
            value="{{ $value }}"
            @if ($min) min="{{ $min }}" @endif
            @if ($required) required @endif
            @if ($described !== '') aria-describedby="{{ $described }}" @endif
            @if ($error) aria-invalid="true" @endif
            class="admin-input admin-input--mono"
            data-datetime-input
        >

        <div class="admin-datetime__presets" data-datetime-presets hidden>
            <button type="button" class="admin-chip" data-datetime-preset="tomorrow-09">Tomorrow, 09:00</button>
            <button type="button" class="admin-chip" data-datetime-preset="next-week">In a week</button>
            <button type="button" class="admin-chip" data-datetime-preset="next-month">In a month</button>
        </div>
    </div>

    <p class="admin-datetime__readout" id="{{ $readoutId }}" data-datetime-readout aria-live="polite"></p>

    @if ($hint)
        <p class="admin-field__hint" id="{{ $hintId }}">{{ $hint }}</p>
    @endif

    @if ($error)
        <p class="admin-field__error" id="{{ $errorId }}">{{ $error }}</p>
    @endif
</div>
