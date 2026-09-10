<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ProcessStage extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'index_label',
        'title',
        'window',
        'body',
        'output',
        'span_start',
        'span_end',
        'weight',
        'overlap',
        'position',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'span_start' => 'integer',
            'span_end' => 'integer',
            'weight' => 'float',
            'overlap' => 'boolean',
            'position' => 'integer',
        ];
    }

    /** @param Builder<static> $query */
    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('position')->orderBy('id');
    }

    /** How much of the timeline rule this stage occupies, as a percentage. */
    public function spanWidth(): int
    {
        return max(0, $this->span_end - $this->span_start);
    }
}
