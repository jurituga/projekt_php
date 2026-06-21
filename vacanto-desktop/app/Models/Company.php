<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    protected $fillable = [
        'user_id',
        'company_name',
        'description',
        'industry',
        'website',
        'phone',
        'address',
        'logo_path',
        'business_registration_number',
        'tax_id_vat',
        'government_id_ref',
        'government_id_path',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function jobPostings(): HasMany
    {
        return $this->hasMany(JobPosting::class, 'company_id');
    }
}
