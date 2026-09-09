<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\SettingType;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'group',
        'key',
        'value',
        'type',
        'is_public',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'value' => 'array',
            'type' => SettingType::class,
            'is_public' => 'boolean',
        ];
    }

    public function typedValue(): mixed
    {
        $value = $this->value;

        return match ($this->type) {
            SettingType::Bool => (bool) $value,
            SettingType::Media => $value === null || $value === '' ? null : (int) $value,
            SettingType::Json => is_array($value) ? $value : [],
            default => is_scalar($value) || $value === null ? $value : json_encode($value),
        };
    }

    public static function retrieve(string $group, string $key, mixed $default = null): mixed
    {
        $setting = static::query()
            ->where('group', $group)
            ->where('key', $key)
            ->first();

        return $setting?->typedValue() ?? $default;
    }

    public static function put(string $group, string $key, mixed $value, SettingType $type, bool $isPublic = true): self
    {
        return static::query()->updateOrCreate(
            ['group' => $group, 'key' => $key],
            [
                'value' => $value,
                'type' => $type,
                'is_public' => $isPublic,
            ],
        );
    }
}
