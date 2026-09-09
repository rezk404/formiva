<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasMediaAttachments;
use Database\Factories\CaseStudyBeatFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CaseStudyBeat extends Model
{
    /** @use HasFactory<CaseStudyBeatFactory> */
    use HasFactory, HasMediaAttachments;

    /** @var list<string> */
    protected $fillable = [
        'case_study_id',
        'index_label',
        'label',
        'heading',
        'body',
        'plate_seed',
        'plate_variant',
        'plate_ratio',
        'alt',
        'position',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'plate_seed' => 'integer',
            'position' => 'integer',
        ];
    }

    /** @return BelongsTo<CaseStudy, $this> */
    public function caseStudy(): BelongsTo
    {
        return $this->belongsTo(CaseStudy::class);
    }
}
