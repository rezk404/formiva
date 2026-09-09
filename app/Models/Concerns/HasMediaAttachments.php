<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Enums\MediaCollection;
use App\Models\Media;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

trait HasMediaAttachments
{
    /** @return MorphToMany<Media, $this> */
    public function media(): MorphToMany
    {
        return $this->morphToMany(Media::class, 'mediable')
            ->withPivot(['collection', 'position'])
            ->orderByPivot('position');
    }

    /** @return MorphToMany<Media, $this> */
    public function galleryMedia(): MorphToMany
    {
        return $this->media()->wherePivot('collection', MediaCollection::Gallery->value);
    }
}
