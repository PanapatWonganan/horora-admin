<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'birth_date',
        'birth_time',
        'birth_place',
        'avatar_url',
        'is_premium',
        'subscription_end_date',
        'thai_animal',
        'thai_year_name',
        'thai_element',
        'thai_element_full',
        'is_admin',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'birth_date' => 'date',
            'subscription_end_date' => 'datetime',
            'is_premium' => 'boolean',
            'is_admin' => 'boolean',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->is_admin;
    }

    // Relationships
    public function horoscopeHistory(): HasMany
    {
        return $this->hasMany(HoroscopeHistory::class);
    }

    public function tarotReadings(): HasMany
    {
        return $this->hasMany(TarotReading::class);
    }

    public function chatSessions(): HasMany
    {
        return $this->hasMany(ChatSession::class);
    }

    public function meritOrders(): HasMany
    {
        return $this->hasMany(MeritOrder::class);
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    // Calculate Thai Zodiac from birth year
    public static function calculateThaiZodiac(int $year): array
    {
        $animals = ['วอก', 'ระกา', 'จอ', 'กุน', 'ชวด', 'ฉลู', 'ขาล', 'เถาะ', 'มะโรง', 'มะเส็ง', 'มะเมีย', 'มะแม'];
        $elements = ['ทอง', 'ทอง', 'น้ำ', 'น้ำ', 'ไม้', 'ไม้', 'ไฟ', 'ไฟ', 'ดิน', 'ดิน'];

        $animalIndex = $year % 12;
        $elementIndex = $year % 10;

        $animal = $animals[$animalIndex];
        $element = $elements[$elementIndex];

        return [
            'thai_animal' => $animal,
            'thai_year_name' => 'ปี' . $animal,
            'thai_element' => $element,
            'thai_element_full' => 'ธาตุ' . $element,
        ];
    }

    // Auto-calculate Thai zodiac when birth_date is set
    protected static function booted(): void
    {
        static::saving(function (User $user) {
            if ($user->isDirty('birth_date') && $user->birth_date) {
                $zodiac = self::calculateThaiZodiac($user->birth_date->year);
                $user->thai_animal = $zodiac['thai_animal'];
                $user->thai_year_name = $zodiac['thai_year_name'];
                $user->thai_element = $zodiac['thai_element'];
                $user->thai_element_full = $zodiac['thai_element_full'];
            }
        });
    }
}
