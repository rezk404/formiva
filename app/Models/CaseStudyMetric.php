<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\CaseStudyMetricFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CaseStudyMetric extends Model
{
    /** @use HasFactory<CaseStudyMetricFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'case_study_id',
        'value',
        'label',
        'note',
        'position',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'position' => 'integer',
        ];
    }

    /** @return BelongsTo<CaseStudy, $this> */
    public function caseStudy(): BelongsTo
    {
        return $this->belongsTo(CaseStudy::class);
    }
}
