<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Affiliate extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'referral_code',
        'tier',
        'status',
        'bank_name',
        'bank_account_number',
        'bank_account_name',
        'promptpay_number',
        'total_referrals',
        'total_orders',
        'total_commission_earned',
        'total_commission_paid',
        'available_balance',
        'monthly_orders',
        'monthly_period',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'total_commission_earned' => 'decimal:2',
            'total_commission_paid' => 'decimal:2',
            'available_balance' => 'decimal:2',
            'total_referrals' => 'integer',
            'total_orders' => 'integer',
            'monthly_orders' => 'integer',
        ];
    }

    // Auto-generate referral code
    protected static function booted(): void
    {
        static::creating(function (Affiliate $affiliate) {
            if (empty($affiliate->referral_code)) {
                $affiliate->referral_code = self::generateUniqueCode();
            }
        });
    }

    public static function generateUniqueCode(): string
    {
        do {
            $code = strtoupper(Str::random(6));
        } while (self::where('referral_code', $code)->exists());

        return $code;
    }

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function referrals(): HasMany
    {
        return $this->hasMany(AffiliateReferral::class);
    }

    public function commissions(): HasMany
    {
        return $this->hasMany(AffiliateCommission::class);
    }

    public function withdrawals(): HasMany
    {
        return $this->hasMany(AffiliateWithdrawal::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(MeritOrder::class);
    }

    // Helpers
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Get commission rate for a package based on affiliate's tier
     */
    public function getCommissionRate(string $packageId): float
    {
        $rate = AffiliateCommissionRate::where('package_id', $packageId)
            ->where('tier', $this->tier)
            ->first();

        return $rate ? (float) $rate->rate : $this->getDefaultRate();
    }

    /**
     * Default rates by tier if no specific rate is set
     */
    public function getDefaultRate(): float
    {
        return match ($this->tier) {
            'platinum' => 18.00,
            'gold' => 15.00,
            'silver' => 12.00,
            default => 10.00,
        };
    }

    /**
     * Update tier based on monthly orders
     */
    public function updateTier(): void
    {
        $currentPeriod = now()->format('Y-m');

        // Reset monthly if new period
        if ($this->monthly_period !== $currentPeriod) {
            $this->monthly_orders = 0;
            $this->monthly_period = $currentPeriod;
        }

        $newTier = match (true) {
            $this->monthly_orders >= 50 => 'platinum',
            $this->monthly_orders >= 31 => 'gold',
            $this->monthly_orders >= 11 => 'silver',
            default => 'bronze',
        };

        if ($this->tier !== $newTier) {
            $this->tier = $newTier;
        }

        $this->save();
    }

    /**
     * Recalculate available balance
     */
    public function recalculateBalance(): void
    {
        $earned = $this->commissions()
            ->whereIn('status', ['approved', 'paid'])
            ->sum('commission_amount');

        $paid = $this->withdrawals()
            ->whereIn('status', ['completed'])
            ->sum('amount');

        $pendingWithdrawals = $this->withdrawals()
            ->whereIn('status', ['pending', 'processing'])
            ->sum('amount');

        $this->total_commission_earned = $earned;
        $this->total_commission_paid = $paid;
        $this->available_balance = $earned - $paid - $pendingWithdrawals;
        $this->save();
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    // Attribute helpers
    public function getTierLabelAttribute(): string
    {
        return match ($this->tier) {
            'platinum' => 'Platinum',
            'gold' => 'Gold',
            'silver' => 'Silver',
            default => 'Bronze',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'active' => 'ใช้งาน',
            'suspended' => 'ระงับ',
            default => 'รอตรวจสอบ',
        };
    }

    public function getAvailableBalanceFormattedAttribute(): string
    {
        return '฿' . number_format($this->available_balance, 0);
    }
}
