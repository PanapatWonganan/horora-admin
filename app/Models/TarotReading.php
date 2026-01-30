<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TarotReading extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'spread_type',
        'question',
        'cards',
        'interpretation',
    ];

    protected function casts(): array
    {
        return [
            'cards' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getSpreadTypeLabelAttribute(): string
    {
        return match($this->spread_type) {
            'single' => 'ไพ่ใบเดียว',
            'three_card' => 'ไพ่ 3 ใบ',
            'celtic_cross' => 'เคลติกครอส',
            default => $this->spread_type,
        };
    }
}
