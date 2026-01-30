<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MeritPackage extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'name_th',
        'name_en',
        'description',
        'items',
        'price',
        'photo_count',
        'has_video',
        'has_live',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'items' => 'array',
            'price' => 'decimal:2',
            'has_video' => 'boolean',
            'has_live' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function orders(): HasMany
    {
        return $this->hasMany(MeritOrder::class, 'package_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    public function getPriceFormattedAttribute(): string
    {
        return '฿' . number_format($this->price, 0);
    }
}
