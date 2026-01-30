<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MeritOrder extends Model
{
    use HasUuids;

    protected $fillable = [
        'order_number',
        'user_id',
        'location_id',
        'package_id',
        'prayer_name',
        'prayer_birthdate',
        'prayer_wish',
        'prayer_phone',
        'price',
        'slip_url',
        'paid_at',
        'status',
        'proof_urls',
        'proof_video_url',
        'completed_at',
        'admin_note',
    ];

    protected function casts(): array
    {
        return [
            'prayer_birthdate' => 'date',
            'price' => 'decimal:2',
            'paid_at' => 'datetime',
            'completed_at' => 'datetime',
            'proof_urls' => 'array',
        ];
    }

    // Auto-generate order number
    protected static function booted(): void
    {
        static::creating(function (MeritOrder $order) {
            if (empty($order->order_number)) {
                $order->order_number = 'MRT' . now()->format('Ymd') . '-' . str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(MeritLocation::class, 'location_id');
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(MeritPackage::class, 'package_id');
    }

    // Status helpers
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isProcessing(): bool
    {
        return $this->status === 'processing';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending' => 'รอชำระเงิน',
            'paid' => 'รอดำเนินการ',
            'processing' => 'กำลังไหว้',
            'completed' => 'เสร็จสิ้น',
            'cancelled' => 'ยกเลิก',
            default => $this->status,
        };
    }

    public function getPriceFormattedAttribute(): string
    {
        return '฿' . number_format($this->price, 0);
    }
}
