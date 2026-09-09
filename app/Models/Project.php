<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ProjectStage;
use App\Enums\ProjectStatus;
use App\Models\Concerns\HasMediaAttachments;
use Database\Factories\ProjectFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    /** @use HasFactory<ProjectFactory> */
    use HasFactory, HasMediaAttachments, SoftDeletes;

    /** @var list<string> */
    protected $fillable = [
        'slug',
        'index_label',
        'name',
        'title',
        'category_id',
        'client_id',
        'year',
        'statement',
        'description',
        'challenge',
        'solution',
        'outcome',
        'result_value',
        'result_label',
        'disciplines',
        'stack',
        'cover_media_id',
        'plate_seed',
        'plate_variant',
        'plate_ratio',
        'alt',
        'is_featured',
        'status',
        'stage',
        'started_at',
        'completed_at',
        'published_at',
        'position',
        'meta_title',
        'meta_description',
        'created_by',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'disciplines' => 'array',
            'stack' => 'array',
            'is_featured' => 'boolean',
            'status' => ProjectStatus::class,
            'stage' => ProjectStage::class,
            'year' => 'integer',
            'plate_seed' => 'integer',
            'position' => 'integer',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'published_at' => 'datetime',
        ];
    }

    /** @param Builder<static> $query */
    public function scopePublished(Builder $query): void
    {
        $query->where('status', ProjectStatus::Published)
            ->where(function (Builder $query): void {
                $query->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            });
    }

    /** @param Builder<static> $query */
    public function scopeFeatured(Builder $query): void
    {
        $query->where('is_featured', true);
    }

    /** @param Builder<static> $query */
    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('position')->orderBy('id');
    }

    /** @return BelongsTo<Category, $this> */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /** @return BelongsTo<Client, $this> */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /** @return BelongsTo<User, $this> */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** @return BelongsTo<Media, $this> */
    public function coverMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'cover_media_id');
    }

    /** @return BelongsToMany<Service, $this> */
    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'project_service');
    }

    /** @return HasOne<CaseStudy, $this> */
    public function caseStudy(): HasOne
    {
        return $this->hasOne(CaseStudy::class);
    }

    /** @return HasMany<Testimonial, $this> */
    public function testimonials(): HasMany
    {
        return $this->hasMany(Testimonial::class);
    }

    /** @return HasMany<Inquiry, $this> */
    public function inquiries(): HasMany
    {
        return $this->hasMany(Inquiry::class);
    }
}
