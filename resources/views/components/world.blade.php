@php
    /*
     | The fallback composition.
     |
     | Drawn with the same rule as the 3D monolith state — a barrel taper
     | across a stack of slabs — so a visitor without WebGL sees the studio's
     | object, not an apology. Only the dimension is missing.
     */
    $slabs = 26;
    $stack = [];

    for ($i = 0; $i < $slabs; $i++) {
        $c = ($i / ($slabs - 1)) * 2 - 1;
        $scale = 1 - (abs($c) ** 1.7) * 0.3;

        $stack[] = [
            'w' => round(300 * $scale, 1),
            'x' => round(200 - (300 * $scale) / 2 + $c * 6, 1),
            'y' => round(40 + $i * 15.4, 1),
            'o' => round(0.42 + (1 - abs($c)) * 0.5, 2),
            'accent' => $i === 9,
        ];
    }
@endphp

<div class="world" data-world-root aria-hidden="true">
    <canvas class="world__canvas" data-world-canvas></canvas>

    {{-- Shown only when WebGL is unavailable, refused, or the context is
         lost mid-session. See three/world.js. --}}
    <div class="world__fallback">
        <svg viewBox="0 0 400 460" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            @foreach ($stack as $slab)
                <rect
                    class="world__fallback-slab"
                    x="{{ $slab['x'] }}"
                    y="{{ $slab['y'] }}"
                    width="{{ $slab['w'] }}"
                    height="9"
                    rx="2"
                    fill="{{ $slab['accent'] ? '#e5502a' : '#b9b6b1' }}"
                    opacity="{{ $slab['o'] }}"
                />
            @endforeach
        </svg>
    </div>
</div>
