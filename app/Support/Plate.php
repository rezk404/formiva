<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Plate — the studio's generated artwork system.
 *
 * Every image on the site is drawn, not photographed. A plate is a layered
 * composition of horizontal slabs: the same visual DNA as the 3D system in
 * resources/js/three/strata.js, flattened to two dimensions.
 *
 * Compositions are deterministic. A given seed always produces the same
 * artwork, so a project's visual is stable across deploys and can be cached
 * indefinitely. Nothing here touches randomness the runtime controls.
 *
 * When real photography replaces these, the Blade component swaps its inner
 * markup and every layout around it stays exactly as it is.
 */
final class Plate
{
    /**
     * Viewbox dimensions per aspect ratio. Kept as round numbers so the
     * generated coordinates stay readable in devtools.
     *
     * @var array<string, array{int, int}>
     */
    private const RATIOS = [
        '1/1' => [1000, 1000],
        '4/3' => [1200, 900],
        '3/4' => [900, 1200],
        '3/2' => [1500, 1000],
        '2/3' => [1000, 1500],
        '16/9' => [1600, 900],
        '16/10' => [1600, 1000],
    ];

    /**
     * Tonal ramps. Each variant is a closed set — slabs only ever draw from
     * its own ramp, which is what keeps four unrelated projects reading as
     * one body of work.
     *
     * @var array<string, array{field: string, ramp: array<int, string>, accent: string, rule: string}>
     */
    private const VARIANTS = [
        'ink' => [
            'field' => '#0E0E10',
            'ramp' => ['#161619', '#1C1C20', '#232328', '#2B2B31', '#35353C', '#414149'],
            'accent' => '#E5502A',
            'rule' => '#4A4A52',
        ],
        'bone' => [
            'field' => '#EFEBE1',
            'ramp' => ['#E6E1D5', '#DCD6C8', '#D1CABA', '#C4BCA9', '#B6AD99', '#A79D88'],
            'accent' => '#C4552D',
            'rule' => '#9C927E',
        ],
        'signal' => [
            'field' => '#121215',
            'ramp' => ['#1A1A1E', '#212126', '#2A2A30', '#33333A', '#3E3E46', '#4A4A53'],
            'accent' => '#E5502A',
            'rule' => '#57575F',
        ],
    ];

    /** Deterministic 32-bit LCG state. */
    private int $state;

    private function __construct(int $seed)
    {
        // Mix the seed so adjacent seeds (1101, 1214) diverge immediately
        // instead of producing near-identical first draws.
        $this->state = ($seed * 1_664_525 + 1_013_904_223) & 0x7FFFFFFF;
    }

    /**
     * Build a complete composition description for the Blade component.
     *
     * @return array{
     *     uid: string,
     *     width: int,
     *     height: int,
     *     field: string,
     *     accent: string,
     *     rule: string,
     *     slabs: array<int, array{x: float, y: float, w: float, h: float, r: float, fill: string, opacity: float, accent: bool}>,
     *     ticks: array<int, array{x: float, y: float, w: float}>,
     *     tickSide: string
     * }
     */
    public static function compose(int $seed, string $variant = 'ink', string $ratio = '4/3'): array
    {
        $plate = new self($seed);

        $palette = self::VARIANTS[$variant] ?? self::VARIANTS['ink'];
        [$width, $height] = self::RATIOS[$ratio] ?? self::RATIOS['4/3'];

        $count = $plate->intBetween(9, 15);
        $margin = $height * 0.06;
        $usable = $height - ($margin * 2);

        // Distribute vertical mass unevenly: a few heavy slabs carrying the
        // composition, the rest thin. An even distribution reads as a chart.
        $weights = [];
        $total = 0.0;
        for ($i = 0; $i < $count; $i++) {
            $heavy = $plate->chance(0.28);
            $w = $heavy ? $plate->floatBetween(1.6, 3.4) : $plate->floatBetween(0.35, 0.9);
            $weights[] = $w;
            $total += $w;
        }

        $gap = $usable * 0.012;
        $available = $usable - ($gap * ($count - 1));

        // The accent slab is never first or last — it needs mass around it.
        $accentAt = $plate->intBetween(1, max(1, $count - 2));

        $slabs = [];
        $y = $margin;

        for ($i = 0; $i < $count; $i++) {
            $h = ($weights[$i] / $total) * $available;

            // Alignment alternates between flush-left, inset and flush-right
            // so the stack develops an edge rhythm rather than a silhouette.
            $mode = $plate->intBetween(0, 5);
            $inset = $width * 0.055;

            if ($mode <= 2) {
                $x = $inset;
                $w = $width * $plate->floatBetween(0.42, 0.94) - $inset;
            } elseif ($mode <= 4) {
                $x = $width * $plate->floatBetween(0.14, 0.40);
                $w = $width * $plate->floatBetween(0.34, 0.72);
            } else {
                $w = $width * $plate->floatBetween(0.30, 0.62);
                $x = $width - $inset - $w;
            }

            $w = min($w, $width - $x - $inset * 0.4);

            $isAccent = $i === $accentAt;
            $tone = $palette['ramp'][$plate->intBetween(0, count($palette['ramp']) - 1)];

            $slabs[] = [
                'x' => round($x, 1),
                'y' => round($y, 1),
                'w' => round(max($w, $width * 0.08), 1),
                'h' => round(max($h, 3.0), 1),
                'r' => round(min($h * 0.22, 6.0), 1),
                'fill' => $isAccent ? $palette['accent'] : $tone,
                'opacity' => $isAccent ? 1.0 : round($plate->floatBetween(0.72, 1.0), 2),
                'accent' => $isAccent,
            ];

            $y += $h + $gap;
        }

        // Measurement ticks: the architectural detail that says "drawn",
        // and the only element that breaks the horizontal reading.
        $tickSide = $plate->chance(0.5) ? 'left' : 'right';
        $tickCount = $plate->intBetween(14, 26);
        $ticks = [];

        for ($i = 0; $i < $tickCount; $i++) {
            $major = $i % 5 === 0;
            $len = $major ? $width * 0.032 : $width * 0.014;
            $ty = $margin + ($usable / max($tickCount - 1, 1)) * $i;

            $ticks[] = [
                'x' => $tickSide === 'left' ? round($width * 0.018, 1) : round($width - $width * 0.018 - $len, 1),
                'y' => round($ty, 1),
                'w' => round($len, 1),
            ];
        }

        return [
            'uid' => 'plate-'.$seed.'-'.substr(md5($variant.$ratio.$seed), 0, 6),
            'width' => $width,
            'height' => $height,
            'field' => $palette['field'],
            'accent' => $palette['accent'],
            'rule' => $palette['rule'],
            'slabs' => $slabs,
            'ticks' => $ticks,
            'tickSide' => $tickSide,
        ];
    }

    /** Next value in the sequence, normalised to 0..1. */
    private function next(): float
    {
        $this->state = ($this->state * 1_103_515_245 + 12_345) & 0x7FFFFFFF;

        return $this->state / 0x7FFFFFFF;
    }

    private function floatBetween(float $min, float $max): float
    {
        return $min + $this->next() * ($max - $min);
    }

    private function intBetween(int $min, int $max): int
    {
        return $min + (int) floor($this->next() * (($max - $min) + 1));
    }

    private function chance(float $probability): bool
    {
        return $this->next() < $probability;
    }
}
