<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ContentStatus;
use App\Models\Concerns\HasMediaAttachments;
use Database\Factories\InsightFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Insight extends Model
{
    /** @use HasFactory<InsightFactory> */
    use HasFactory, HasMediaAttachments, SoftDeletes;

    /** @var list<string> */
    protected $fillable = [
        'slug',
        'index_label',
        'category_id',
        'title',
        'dek',
        'body',
        'reading_minutes',
        'author_id',
        'plate_seed',
        'plate_variant',
        'plate_ratio',
        'alt',
        'cover_media_id',
        'status',
        'published_at',
        'meta_title',
        'meta_description',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'reading_minutes' => 'integer',
            'plate_seed' => 'integer',
            'status' => ContentStatus::class,
            'published_at' => 'datetime',
        ];
    }

    /** @param Builder<static> $query */
    public function scopePublished(Builder $query): void
    {
        $query->where('status', ContentStatus::Published)
            ->where(function (Builder $query): void {
                $query->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            });
    }

    /** @param Builder<static> $query */
    public function scopeScheduled(Builder $query): void
    {
        $query->where('status', ContentStatus::Scheduled)
            ->where('published_at', '>', now());
    }

    /** @param Builder<static> $query */
    public function scopeOrdered(Builder $query): void
    {
        $query->orderByDesc('published_at')->orderBy('id');
    }

    /** @return BelongsTo<Category, $this> */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /** @return BelongsTo<User, $this> */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /** @return BelongsTo<Media, $this> */
    public function coverMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'cover_media_id');
    }
}
