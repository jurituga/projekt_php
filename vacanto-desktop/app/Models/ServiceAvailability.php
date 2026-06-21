<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceAvailability extends Model
{
    public $timestamps = false;

    protected $table = 'service_availability';

    protected $fillable = [
        'service_id',
        'available_date',
        'slot_time',
        'is_booked',
    ];

    protected function casts(): array
    {
        return [
            'available_date' => 'date',
            'is_booked' => 'boolean',
            'created_at' => 'datetime',
        ];
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}
