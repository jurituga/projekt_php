<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RatingImage extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'rating_id',
        'file_path',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function rating(): BelongsTo
    {
        return $this->belongsTo(FreelancerRating::class, 'rating_id');
    }
}
