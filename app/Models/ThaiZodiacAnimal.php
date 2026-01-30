<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ThaiZodiacAnimal extends Model
{
    protected $fillable = [
        'animal_name',
        'thai_name',
        'english_name',
        'characteristics',
        'compatibility',
        'lucky_numbers',
    ];

    protected function casts(): array
    {
        return [
            'lucky_numbers' => 'array',
        ];
    }
}
