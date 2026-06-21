<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ServiceRequest extends Model
{
    protected $fillable = [
        'service_id',
        'requester_id',
        'message',
        'booking_date',
        'booking_time',
        'booking_slot_id',
        'rejection_reason',
        'payment_status',
        'payment_amount',
        'stripe_session_id',
        'stripe_payment_intent',
        'paid_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'booking_date' => 'date',
            'payment_amount' => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function bookingSlot(): BelongsTo
    {
        return $this->belongsTo(ServiceAvailability::class, 'booking_slot_id');
    }

    public function rating(): HasOne
    {
        return $this->hasOne(FreelancerRating::class);
    }
}
