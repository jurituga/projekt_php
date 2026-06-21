<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Enums\UserRole;
use App\Models\Company;
use App\Models\JobApplication;
use App\Models\JobPosting;
use App\Models\Service;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'users' => User::where('role', '!=', UserRole::Admin)->count(),
            'pending' => User::where('role', '!=', UserRole::Admin)->where('status', 'pending')->count(),
            'companies' => Company::count(),
            'jobs' => JobPosting::count(),
            'applications' => JobApplication::count(),
            'services' => Service::count(),
            'service_requests' => ServiceRequest::count(),
        ];

        return view('admin.dashboard', compact('stats'));
    }
}
