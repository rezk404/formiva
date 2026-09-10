<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ContentStatus;
use App\Models\Concerns\Publishable;
use Database\Factories\ServiceFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model
{
    /** @use HasFactory<ServiceFactory> */
    use HasFactory, Publishable, SoftDeletes;

    /** @var list<string> */
    protected $fillable = [
        'slug',
        'index_label',
        'title',
        'form',
        'lede',
        'why',
        'outcome',
        'note',
        'position',
        'status',
        'published_at',
        'meta_title',
        'meta_description',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'status' => ContentStatus::class,
            'published_at' => 'datetime',
            'position' => 'integer',
        ];
    }

    /** @param  Builder<static>  $query */
    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('position')->orderBy('id');
    }

    /** @param  Builder<static>  $query */
    public function scopeSearch(Builder $query, ?string $term): void
    {
        $term = trim((string) $term);

        if ($term === '') {
            return;
        }

        $query->where(function (Builder $query) use ($term): void {
            $query->where('title', 'like', "%{$term}%")
                ->orWhere('slug', 'like', "%{$term}%")
                ->orWhere('lede', 'like', "%{$term}%");
        });
    }

    /** @return HasMany<ServiceItem, $this> */
    public function items(): HasMany
    {
        return $this->hasMany(ServiceItem::class)->orderBy('position')->orderBy('id');
    }

    /** @return BelongsToMany<Project, $this> */
    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class, 'project_service');
    }
}
