<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\JobApplication;
use App\Models\ServiceRequest;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $applications = JobApplication::with(['jobPosting.company'])
            ->where('user_id', auth()->id())
            ->latest()
            ->limit(10)
            ->get();

        $serviceRequests = ServiceRequest::with(['service.freelancer'])
            ->where('requester_id', auth()->id())
            ->latest()
            ->limit(10)
            ->get();

        $cvs = auth()->user()->cvs()->orderByDesc('is_default')->orderByDesc('created_at')->get();

        return view('user.dashboard', compact('applications', 'serviceRequests', 'cvs'));
    }
}
