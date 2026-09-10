@props(['name', 'label', 'options' => [], 'value' => '', 'any' => 'Any'])

<div class="admin-filters__field">
    <label for="filter-{{ $name }}">{{ $label }}</label>
    <select id="filter-{{ $name }}" name="{{ $name }}" class="admin-select">
        <option value="">{{ $any }}</option>
        @foreach ($options as $optionValue => $optionLabel)
            <option value="{{ $optionValue }}" @selected((string) $optionValue === (string) $value)>{{ $optionLabel }}</option>
        @endforeach
    </select>
</div>
