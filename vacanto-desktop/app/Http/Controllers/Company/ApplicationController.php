<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Concerns\RequiresCompanyProfile;
use App\Http\Controllers\Controller;
use App\Models\JobApplication;
use App\Models\JobPosting;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ApplicationController extends Controller
{
    use RequiresCompanyProfile;

    public function index(): View|RedirectResponse
    {
        $company = $this->requireCompany();
        if ($company instanceof RedirectResponse) {
            return $company;
        }

        $applications = JobApplication::with(['jobPosting', 'user', 'cv'])
            ->whereHas('jobPosting', fn ($q) => $q->where('company_id', $company->id))
            ->latest()
            ->get();

        return view('company.applications.index', compact('applications'));
    }

    public function forJob(JobPosting $job): View|RedirectResponse
    {
        $company = $this->requireCompany();
        if ($company instanceof RedirectResponse) {
            return $company;
        }

        abort_unless($job->company_id === $company->id, 404);

        $applications = JobApplication::with(['user', 'cv'])
            ->where('job_id', $job->id)
            ->latest()
            ->get();

        return view('company.applications.job', compact('job', 'applications'));
    }
}
