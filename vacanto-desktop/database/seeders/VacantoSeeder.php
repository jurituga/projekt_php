<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\Company;
use App\Models\FreelancerProfile;
use App\Models\JobPosting;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class VacantoSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make('password');

        $companyUser = User::create([
            'name' => 'Sarah Chen',
            'email' => 'sarah@techcorp.com',
            'password' => $password,
            'role' => UserRole::Company,
            'status' => UserStatus::Active,
        ]);

        $company = Company::create([
            'user_id' => $companyUser->id,
            'company_name' => 'TechCorp Solutions',
            'description' => 'We build web and mobile applications for startups and enterprises.',
            'industry' => 'Technology',
            'website' => 'https://techcorp.example.com',
        ]);

        JobPosting::insert([
            [
                'company_id' => $company->id,
                'title' => 'Senior PHP Developer',
                'description' => 'We are looking for an experienced PHP developer to work on our core platform. You will work with MySQL, REST APIs, and modern PHP.',
                'location' => 'Remote',
                'job_type' => 'full_time',
                'salary_min' => 80000,
                'salary_max' => 120000,
                'status' => 'published',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'company_id' => $company->id,
                'title' => 'Frontend Developer',
                'description' => 'Join our team to build responsive, accessible UIs. Strong HTML, CSS, and JavaScript skills required.',
                'location' => 'New York, NY',
                'job_type' => 'full_time',
                'salary_min' => 70000,
                'salary_max' => 95000,
                'status' => 'published',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $freelancerUser = User::create([
            'name' => 'Alex Rivera',
            'email' => 'alex@freelance.dev',
            'password' => $password,
            'role' => UserRole::Freelancer,
            'status' => UserStatus::Active,
        ]);

        FreelancerProfile::create([
            'user_id' => $freelancerUser->id,
            'freelancer_type' => 'general',
            'bio' => 'Full-stack developer with 8+ years experience building secure, scalable web applications.',
            'skills' => 'PHP, MySQL, JavaScript, Laravel, API Development',
            'hourly_rate' => 85.00,
        ]);

        Service::insert([
            [
                'freelancer_id' => $freelancerUser->id,
                'title' => 'PHP Backend Development',
                'description' => 'Custom PHP backend development: REST APIs, database design, authentication, and integration with frontends.',
                'price' => 85.00,
                'price_type' => 'hourly',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'freelancer_id' => $freelancerUser->id,
                'title' => 'Laravel Project Setup',
                'description' => 'One-time setup of a new Laravel project: structure, auth, roles, and basic CRUD.',
                'price' => 450.00,
                'price_type' => 'fixed',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        User::create([
            'name' => 'Jane Seeker',
            'email' => 'seeker@example.com',
            'password' => $password,
            'role' => UserRole::User,
            'status' => UserStatus::Active,
        ]);
    }
}
