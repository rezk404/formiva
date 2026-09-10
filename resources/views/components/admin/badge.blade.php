@props(['tone' => 'neutral', 'plain' => false])

<span {{ $attributes->class(['admin-badge', 'admin-badge--'.$tone, 'admin-badge--plain' => $plain]) }}>{{ $slot }}</span>
