<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobApplication extends Model
{
    protected $table = 'applications';

    protected $fillable = [
        'job_id',
        'user_id',
        'cv_id',
        'cover_letter',
        'status',
    ];

    public function jobPosting(): BelongsTo
    {
        return $this->belongsTo(JobPosting::class, 'job_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function cv(): BelongsTo
    {
        return $this->belongsTo(Cv::class);
    }
}
