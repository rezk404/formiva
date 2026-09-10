<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ContentStatus;
use App\Models\Concerns\HasMediaAttachments;
use App\Models\Concerns\Publishable;
use Database\Factories\InsightFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Insight extends Model
{
    /** @use HasFactory<InsightFactory> */
    use HasFactory, HasMediaAttachments, Publishable, SoftDeletes;

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

    /**
     * Newest first — the journal reads in reverse chronology, and an entry
     * with no date yet sorts to the bottom rather than jumping the queue.
     *
     * @param  Builder<static>  $query
     */
    public function scopeOrdered(Builder $query): void
    {
        $query->orderByDesc('published_at')->orderBy('id');
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
                ->orWhere('dek', 'like', "%{$term}%");
        });
    }

    /**
     * Roughly how long the body takes to read, at 200 words a minute. Used
     * as the default when a writer leaves the field alone.
     */
    public static function estimateReadingMinutes(?string $body): int
    {
        $words = str_word_count(strip_tags((string) $body));

        return max(1, min(255, (int) ceil($words / 200)));
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
