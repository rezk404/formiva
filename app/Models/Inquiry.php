<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\InquiryKind;
use App\Enums\InquiryPriority;
use App\Enums\InquiryStatus;
use App\Models\Concerns\HasMediaAttachments;
use Database\Factories\InquiryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Inquiry extends Model
{
    /** @use HasFactory<InquiryFactory> */
    use HasFactory, HasMediaAttachments, SoftDeletes;

    /** @var list<string> */
    protected $fillable = [
        'reference',
        'kind',
        'name',
        'company',
        'email',
        'phone',
        'country',
        'project_type',
        'project_group',
        'industry',
        'company_size',
        'problem',
        'services',
        'scope',
        'budget_range',
        'timeline',
        'message',
        'notes',
        'status',
        'priority',
        'assigned_to',
        'client_id',
        'project_id',
        'reviewed_at',
        'qualified_at',
        'converted_at',
        'declined_reason',
        'source',
        'utm',
        'ip_address',
        'user_agent',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'kind' => InquiryKind::class,
            'services' => 'array',
            'utm' => 'array',
            'status' => InquiryStatus::class,
            'priority' => InquiryPriority::class,
            'reviewed_at' => 'datetime',
            'qualified_at' => 'datetime',
            'converted_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
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

    /** @return HasMany<InquiryNote, $this> */
    public function inquiryNotes(): HasMany
    {
        return $this->hasMany(InquiryNote::class)->orderBy('created_at');
    }

    /** @return MorphMany<ActivityLog, $this> */
    public function activityLogs(): MorphMany
    {
        return $this->morphMany(ActivityLog::class, 'subject')->latest('created_at');
    }
}
