<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\JobApplication;
use App\Models\JobPosting;
use App\Models\ServiceRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View|RedirectResponse
    {
        $company = auth()->user()->company;

        if (! $company) {
            return redirect()->route('company.profile.edit')
                ->with('error', 'Please complete your company profile first.');
        }

        $jobsCount = JobPosting::where('company_id', $company->id)
            ->where('status', 'published')
            ->count();

        $applications = JobApplication::with(['jobPosting', 'user', 'cv'])
            ->whereHas('jobPosting', fn ($q) => $q->where('company_id', $company->id))
            ->latest()
            ->limit(10)
            ->get();

        $serviceRequests = ServiceRequest::with(['service.freelancer'])
            ->where('requester_id', auth()->id())
            ->latest()
            ->limit(10)
            ->get();

        return view('company.dashboard', compact('jobsCount', 'applications', 'serviceRequests'));
    }
}
