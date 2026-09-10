<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\IntakeOptionKind;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class IntakeOption extends Model
{
    /**
     * The group every flat list shares. Project types and budgets use the
     * column for real (they are keyed to each other on the intake form);
     * timelines, company sizes and services are single lists and store this.
     */
    public const SHARED_GROUP = 'all';

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

    /** @param  Builder<static>  $query */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /** @param  Builder<static>  $query */
    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('position')->orderBy('id');
    }

    /** @param  Builder<static>  $query */
    public function scopeForKind(Builder $query, IntakeOptionKind|string $kind): void
    {
        $query->where('kind', $kind instanceof IntakeOptionKind ? $kind : IntakeOptionKind::from($kind));
    }

    /** @param  Builder<static>  $query */
    public function scopeSearch(Builder $query, ?string $term): void
    {
        $term = trim((string) $term);

        if ($term === '') {
            return;
        }

        $query->where(function (Builder $query) use ($term): void {
            $query->where('label', 'like', "%{$term}%")
                ->orWhere('value', 'like', "%{$term}%")
                ->orWhere('group', 'like', "%{$term}%");
        });
    }

    /**
     * Budget ranges legitimately repeat across project-type groups — "EGP
     * 20k–50k" is offered for both a website and a brand identity — while
     * the table keeps (kind, value) unique. Namespacing the stored value by
     * group is what lets both facts be true at once; the label, which is
     * what the visitor and the brief actually see, stays clean.
     */
    public static function qualifiedValue(IntakeOptionKind $kind, string $group, string $value): string
    {
        return $kind === IntakeOptionKind::Budget ? $group.':'.$value : $value;
    }

    /** The stored value with any group prefix removed, for editing. */
    public function displayValue(): string
    {
        $value = (string) $this->value;

        if ($this->kind === IntakeOptionKind::Budget && $this->group) {
            $prefix = $this->group.':';

            return str_starts_with($value, $prefix) ? substr($value, strlen($prefix)) : $value;
        }

        return $value;
    }
}
