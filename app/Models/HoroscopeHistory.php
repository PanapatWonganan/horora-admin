<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HoroscopeHistory extends Model
{
    use HasUuids;

    protected $table = 'horoscope_history';

    protected $fillable = [
        'user_id',
        'thai_animal',
        'date',
        'content',
        'love_score',
        'career_score',
        'health_score',
        'lucky_number',
        'lucky_color',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'love_score' => 'integer',
            'career_score' => 'integer',
            'health_score' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
