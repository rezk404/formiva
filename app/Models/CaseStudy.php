<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CaseStudyStatus;
use Database\Factories\CaseStudyFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CaseStudy extends Model
{
    /** @use HasFactory<CaseStudyFactory> */
    use HasFactory, SoftDeletes;

    /** @var list<string> */
    protected $fillable = [
        'project_id',
        'eyebrow',
        'title',
        'summary',
        'client_name',
        'duration',
        'team_size',
        'role',
        'status',
        'published_at',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'status' => CaseStudyStatus::class,
            'published_at' => 'datetime',
        ];
    }

    /** @param Builder<static> $query */
    public function scopePublished(Builder $query): void
    {
        $query->where('status', CaseStudyStatus::Published)
            ->where(function (Builder $query): void {
                $query->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            });
    }

    /** @return BelongsTo<Project, $this> */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /** @return HasMany<CaseStudyBeat, $this> */
    public function beats(): HasMany
    {
        return $this->hasMany(CaseStudyBeat::class)->orderBy('position')->orderBy('id');
    }

    /** @return HasMany<CaseStudyMetric, $this> */
    public function metrics(): HasMany
    {
        return $this->hasMany(CaseStudyMetric::class)->orderBy('position')->orderBy('id');
    }
}
