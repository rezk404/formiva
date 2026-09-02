@props(['reveal' => false, 'title' => null])
<svg
    {{ $attributes->merge(['class' => 'mark'.($reveal ? ' mark--reveal' : '')]) }}
    viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg"
    @if ($title) role="img" aria-label="{{ $title }}" @else aria-hidden="true" focusable="false" @endif
>
    <path d="M14 9H50V18H23V27H44V36H23V55H14V9Z" fill="currentColor"/>
    <path d="M35 27H50V36H35V27Z" fill="currentColor" opacity="0.45"/>
</svg>
