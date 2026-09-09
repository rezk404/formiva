<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ClientStatus;
use App\Models\Concerns\HasMediaAttachments;
use Database\Factories\ClientFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    /** @use HasFactory<ClientFactory> */
    use HasFactory, HasMediaAttachments, SoftDeletes;

    /** @var list<string> */
    protected $fillable = [
        'name',
        'slug',
        'wordmark',
        'sector',
        'country',
        'website',
        'logo_media_id',
        'status',
        'is_featured',
        'source_inquiry_id',
        'notes',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'status' => ClientStatus::class,
            'is_featured' => 'boolean',
        ];
    }

    /** @return BelongsTo<Media, $this> */
    public function logo(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'logo_media_id');
    }

    /** @return BelongsTo<Inquiry, $this> */
    public function sourceInquiry(): BelongsTo
    {
        return $this->belongsTo(Inquiry::class, 'source_inquiry_id');
    }

    /** @return HasMany<Project, $this> */
    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    /** @return HasMany<ClientContact, $this> */
    public function contacts(): HasMany
    {
        return $this->hasMany(ClientContact::class);
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
