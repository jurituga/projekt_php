<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FreelancerProfile extends Model
{
    protected $fillable = [
        'user_id',
        'freelancer_type',
        'bio',
        'skills',
        'hourly_rate',
        'avatar_path',
        'government_id_ref',
        'government_id_path',
        'qualifications',
        'certification_path',
    ];

    protected function casts(): array
    {
        return [
            'hourly_rate' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isScheduled(): bool
    {
        return in_array($this->freelancer_type, ['electrician', 'plumber'], true);
    }

    public function canManageAvailability(): bool
    {
        return true;
    }
}
