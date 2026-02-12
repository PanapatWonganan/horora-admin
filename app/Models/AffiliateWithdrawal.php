<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AffiliateWithdrawal extends Model
{
    use HasUuids;

    protected $fillable = [
        'affiliate_id',
        'amount',
        'method',
        'bank_name',
        'bank_account_number',
        'bank_account_name',
        'promptpay_number',
        'status',
        'transfer_slip_url',
        'admin_note',
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'processed_at' => 'datetime',
        ];
    }

    // Relationships
    public function affiliate(): BelongsTo
    {
        return $this->belongsTo(Affiliate::class);
    }

    // Helpers
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'processing' => 'กำลังดำเนินการ',
            'completed' => 'โอนแล้ว',
            'rejected' => 'ปฏิเสธ',
            default => 'รอดำเนินการ',
        };
    }

    public function getAmountFormattedAttribute(): string
    {
        return '฿' . number_format($this->amount, 0);
    }

    public function getMethodLabelAttribute(): string
    {
        return match ($this->method) {
            'promptpay' => 'พร้อมเพย์',
            default => 'โอนธนาคาร',
        };
    }
}
