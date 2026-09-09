<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\InquiryNoteFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InquiryNote extends Model
{
    /** @use HasFactory<InquiryNoteFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'inquiry_id',
        'user_id',
        'body',
        'is_pinned',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'is_pinned' => 'boolean',
        ];
    }

    /** @return BelongsTo<Inquiry, $this> */
    public function inquiry(): BelongsTo
    {
        return $this->belongsTo(Inquiry::class);
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
