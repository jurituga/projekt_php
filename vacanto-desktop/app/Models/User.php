<?php

namespace App\Models;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'role' => UserRole::class,
            'status' => UserStatus::class,
        ];
    }

    public function company(): HasOne
    {
        return $this->hasOne(Company::class);
    }

    public function freelancerProfile(): HasOne
    {
        return $this->hasOne(FreelancerProfile::class);
    }

    public function userProfile(): HasOne
    {
        return $this->hasOne(UserProfile::class);
    }

    public function cvs(): HasMany
    {
        return $this->hasMany(Cv::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(JobApplication::class);
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class, 'freelancer_id');
    }

    public function serviceRequests(): HasMany
    {
        return $this->hasMany(ServiceRequest::class, 'requester_id');
    }

    public function ratingsGiven(): HasMany
    {
        return $this->hasMany(FreelancerRating::class, 'reviewer_id');
    }

    public function ratingsReceived(): HasMany
    {
        return $this->hasMany(FreelancerRating::class, 'freelancer_id');
    }

    public function sentMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function passwordResetTokens(): HasMany
    {
        return $this->hasMany(PasswordResetToken::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    public function isActive(): bool
    {
        return $this->status === UserStatus::Active;
    }

    public function canAccessApp(): bool
    {
        if ($this->status === UserStatus::Blocked) {
            return false;
        }

        if ($this->status === UserStatus::Pending && ! $this->isAdmin()) {
            return false;
        }

        return true;
    }
}
