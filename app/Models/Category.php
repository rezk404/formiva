<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CategoryType;
use Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    /** @use HasFactory<CategoryFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'type',
        'name',
        'slug',
        'position',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'type' => CategoryType::class,
            'position' => 'integer',
        ];
    }

    /** @param Builder<static> $query */
    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('position')->orderBy('id');
    }

    /** @param Builder<static> $query */
    public function scopeOfType(Builder $query, CategoryType|string|null $type): void
    {
        if ($type === null || $type === '') {
            return;
        }

        $query->where('type', $type instanceof CategoryType ? $type : CategoryType::from($type));
    }

    /** @param Builder<static> $query */
    public function scopeSearch(Builder $query, ?string $term): void
    {
        $term = trim((string) $term);

        if ($term === '') {
            return;
        }

        $query->where(function (Builder $query) use ($term): void {
            $query->where('name', 'like', "%{$term}%")
                ->orWhere('slug', 'like', "%{$term}%");
        });
    }

    /** Whether anything still points at this category. */
    public function isInUse(): bool
    {
        return ($this->projects_count ?? $this->projects()->count()) > 0
            || ($this->insights_count ?? $this->insights()->count()) > 0;
    }

    /** @return HasMany<Project, $this> */
    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    /** @return HasMany<Insight, $this> */
    public function insights(): HasMany
    {
        return $this->hasMany(Insight::class);
    }
}
