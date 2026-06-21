<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->firstOrCreate(
            ['email' => 'admin@platform.com'],
            [
                'name' => 'Admin',
                'password' => 'admin123',
                'role' => UserRole::Admin,
                'status' => UserStatus::Active,
            ]
        );
    }
}
