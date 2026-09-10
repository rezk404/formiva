@props([
    'name',
    'label',
    'input' => null,
    'type' => 'text',
    'value' => null,
    'hint' => null,
    'required' => false,
    'optional' => false,
    'options' => [],
    'placeholder' => null,
    'rows' => 5,
    'id' => null,
    'mono' => false,
    'prompt' => null,
])

{{--
    A labelled control, complete.

    One component rather than five because the label, the hint, the error and
    the control are a single unit — every time they are assembled by hand,
    something loses its `for`, its aria-describedby or its invalid state. The
    slot is there for the controls this cannot express (a checkbox group, a
    radio set); everything else passes a type.
--}}

@php
    $fieldId = $id ?? 'f-'.trim(preg_replace('/[^a-z0-9]+/i', '-', $name), '-');
    $error = $errors->first($name);
    $hintId = $hint ? $fieldId.'-hint' : null;
    $errorId = $error ? $fieldId.'-error' : null;
    $described = trim(implode(' ', array_filter([$hintId, $errorId])));

    $control = $attributes->merge([
        'id' => $fieldId,
        // `name` is the validation key, dotted as the error bag stores it.
        // Repeater rows also pass `input`, because their HTML name is
        // bracketed (items[0][title]) while the error is still items.0.title.
        'name' => $input ?? $name,
        'aria-describedby' => $described !== '' ? $described : null,
        'aria-invalid' => $error ? 'true' : null,
        'required' => $required,
        'placeholder' => $placeholder,
    ]);
@endphp

<div class="admin-field">
    <label class="admin-field__label" for="{{ $fieldId }}">
        <span>{{ $label }}</span>
        @if ($required)
            <span class="admin-field__required" aria-hidden="true">*</span>
            <span class="admin-sr">(required)</span>
        @elseif ($optional)
            <span class="admin-field__optional">Optional</span>
        @endif
    </label>

    @if ($slot->isNotEmpty())
        {{ $slot }}
    @elseif ($type === 'textarea')
        <textarea {{ $control->class('admin-textarea') }} rows="{{ $rows }}">{{ $value }}</textarea>
    @elseif ($type === 'select')
        <select {{ $control->class('admin-select') }}>
            @if ($prompt !== null)
                <option value="">{{ $prompt }}</option>
            @endif
            @foreach ($options as $optionValue => $optionLabel)
                <option value="{{ $optionValue }}" @selected((string) $optionValue === (string) $value)>{{ $optionLabel }}</option>
            @endforeach
        </select>
    @else
        <input type="{{ $type }}" value="{{ $value }}" {{ $control->class(['admin-input', 'admin-input--mono' => $mono]) }}>
    @endif

    @if ($hint)
        <p class="admin-field__hint" id="{{ $hintId }}">{{ $hint }}</p>
    @endif

    @if ($error)
        <p class="admin-field__error" id="{{ $errorId }}">{{ $error }}</p>
    @endif
</div>
