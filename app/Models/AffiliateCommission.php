<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AffiliateCommission extends Model
{
    use HasUuids;

    protected $fillable = [
        'affiliate_id',
        'order_id',
        'referral_id',
        'order_amount',
        'commission_rate',
        'commission_amount',
        'status',
        'available_at',
    ];

    protected function casts(): array
    {
        return [
            'order_amount' => 'decimal:2',
            'commission_rate' => 'decimal:2',
            'commission_amount' => 'decimal:2',
            'available_at' => 'datetime',
        ];
    }

    // Relationships
    public function affiliate(): BelongsTo
    {
        return $this->belongsTo(Affiliate::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(MeritOrder::class, 'order_id');
    }

    public function referral(): BelongsTo
    {
        return $this->belongsTo(AffiliateReferral::class, 'referral_id');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeAvailable($query)
    {
        return $query->where('status', 'approved')
            ->where('available_at', '<=', now());
    }

    // Helpers
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'approved' => 'อนุมัติแล้ว',
            'paid' => 'จ่ายแล้ว',
            'cancelled' => 'ยกเลิก',
            default => 'รอตรวจสอบ',
        };
    }

    public function getCommissionFormattedAttribute(): string
    {
        return '฿' . number_format($this->commission_amount, 0);
    }
}
