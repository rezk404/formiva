<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\IntakeOptionKind;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class IntakeOption extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'kind',
        'group',
        'value',
        'label',
        'position',
        'is_active',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'kind' => IntakeOptionKind::class,
            'position' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /** @param Builder<static> $query */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /** @param Builder<static> $query */
    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('position')->orderBy('id');
    }

    /** @param Builder<static> $query */
    public function scopeForKind(Builder $query, IntakeOptionKind|string $kind): void
    {
        $query->where('kind', $kind instanceof IntakeOptionKind ? $kind : IntakeOptionKind::from($kind));
    }
}
