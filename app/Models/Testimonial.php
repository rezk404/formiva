<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\TestimonialFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Testimonial extends Model
{
    /** @use HasFactory<TestimonialFactory> */
    use HasFactory, SoftDeletes;

    /** @var list<string> */
    protected $fillable = [
        'quote',
        'author_name',
        'author_role',
        'company',
        'client_id',
        'project_id',
        'is_published',
        'position',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'position' => 'integer',
        ];
    }

    /** @param  Builder<static>  $query */
    public function scopePublished(Builder $query): void
    {
        $query->where('is_published', true);
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
            $query->where('quote', 'like', "%{$term}%")
                ->orWhere('author_name', 'like', "%{$term}%")
                ->orWhere('company', 'like', "%{$term}%");
        });
    }

    /** @return BelongsTo<Client, $this> */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /** @return BelongsTo<Project, $this> */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
