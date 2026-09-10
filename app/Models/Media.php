<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\MediaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Throwable;

class Media extends Model
{
    /** @use HasFactory<MediaFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'media';

    /** @var list<string> */
    protected $fillable = [
        'disk',
        'path',
        'filename',
        'original_name',
        'mime_type',
        'extension',
        'size',
        'width',
        'height',
        'alt',
        'caption',
        'folder_id',
        'uploaded_by',
        'checksum',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'size' => 'integer',
            'width' => 'integer',
            'height' => 'integer',
        ];
    }

    public function isGeneratedPlate(): bool
    {
        return $this->disk === 'generated';
    }

    /**
     * A browsable URL, or null when there is not one.
     *
     * Generated plates are drawn rather than stored, and a record can name a
     * disk this installation does not configure. Both cases return null so a
     * template can fall back to a placeholder instead of rendering a broken
     * image — which is the whole reason this is not just a path accessor.
     */
    public function url(): ?string
    {
        if ($this->isGeneratedPlate() || $this->path === null || $this->path === '') {
            return null;
        }

        try {
            return Storage::disk($this->disk)->url($this->path);
        } catch (Throwable) {
            return null;
        }
    }

    /** @return BelongsTo<MediaFolder, $this> */
    public function folder(): BelongsTo
    {
        return $this->belongsTo(MediaFolder::class, 'folder_id');
    }

    /** @return BelongsTo<User, $this> */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /** @return MorphToMany<Project, $this> */
    public function projects(): MorphToMany
    {
        return $this->morphedByMany(Project::class, 'mediable');
    }

    /** @return MorphToMany<Insight, $this> */
    public function insights(): MorphToMany
    {
        return $this->morphedByMany(Insight::class, 'mediable');
    }

    /** @return MorphToMany<TeamMember, $this> */
    public function teamMembers(): MorphToMany
    {
        return $this->morphedByMany(TeamMember::class, 'mediable');
    }

    /** @return MorphToMany<Client, $this> */
    public function clients(): MorphToMany
    {
        return $this->morphedByMany(Client::class, 'mediable');
    }

    /** @return MorphToMany<CaseStudyBeat, $this> */
    public function caseStudyBeats(): MorphToMany
    {
        return $this->morphedByMany(CaseStudyBeat::class, 'mediable');
    }

    /** @return MorphToMany<Inquiry, $this> */
    public function inquiries(): MorphToMany
    {
        return $this->morphedByMany(Inquiry::class, 'mediable');
    }
}
