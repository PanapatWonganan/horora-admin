<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ThaiElement extends Model
{
    protected $fillable = [
        'element_name',
        'element_full',
        'lucky_colors',
        'characteristics',
    ];

    protected function casts(): array
    {
        return [
            'lucky_colors' => 'array',
        ];
    }
}
