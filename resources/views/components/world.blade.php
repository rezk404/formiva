@php
    /*
     | The fallback composition.
     |
     | Drawn as the same lattice the 3D object is built from — a connected
     | grid of bars with two solid cells picked out — so a visitor without
     | WebGL sees the studio's own diagram, not an apology. Only the third
     | dimension is missing.
     */
    $cols = 6;
    $rows = 7;
    $x0 = 40; $x1 = 360;
    $y0 = 40; $y1 = 460;
    $stepX = ($x1 - $x0) / ($cols - 1);
    $stepY = ($y1 - $y0) / ($rows - 1);

    $hLines = [];
    for ($r = 0; $r < $rows; $r++) {
        $hLines[] = ['x1' => $x0, 'y' => $y0 + $r * $stepY, 'x2' => $x1];
    }

    $vLines = [];
    for ($c = 0; $c < $cols; $c++) {
        $vLines[] = ['x' => $x0 + $c * $stepX, 'y1' => $y0, 'y2' => $y1];
    }
@endphp

<div class="world" data-world-root aria-hidden="true">
    <canvas class="world__canvas" data-world-canvas></canvas>

    {{-- Shown only when WebGL is unavailable, refused, or the context is
         lost mid-session. See three/world.js. --}}
    <div class="world__fallback">
        <svg viewBox="0 0 400 500" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            @foreach ($hLines as $i => $line)
                <line class="world__fallback-line" x1="{{ $line['x1'] }}" y1="{{ $line['y'] }}" x2="{{ $line['x2'] }}" y2="{{ $line['y'] }}" style="animation-delay: {{ $i * 90 }}ms" />
            @endforeach
            @foreach ($vLines as $i => $line)
                <line class="world__fallback-line" x1="{{ $line['x'] }}" y1="{{ $line['y1'] }}" x2="{{ $line['x'] }}" y2="{{ $line['y2'] }}" style="animation-delay: {{ $i * 90 + 60 }}ms" />
            @endforeach
            <rect class="world__fallback-cell world__fallback-cell--a" x="{{ $x0 + 2 * $stepX }}" y="{{ $y0 + 2 * $stepY }}" width="{{ $stepX }}" height="{{ $stepY }}" />
            <rect class="world__fallback-cell world__fallback-cell--b" x="{{ $x0 + 4 * $stepX }}" y="{{ $y0 + 4 * $stepY }}" width="{{ $stepX }}" height="{{ $stepY }}" />
        </svg>
    </div>
</div>
