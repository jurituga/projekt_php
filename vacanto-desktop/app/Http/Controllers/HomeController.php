<?php

namespace App\Http\Controllers;

use App\Models\JobPosting;
use App\Models\Service;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $recentJobs = JobPosting::query()
            ->published()
            ->with('company')
            ->latest()
            ->limit(6)
            ->get();

        $recentServices = Service::query()
            ->active()
            ->with('freelancer')
            ->latest()
            ->limit(6)
            ->get();

        return view('home', compact('recentJobs', 'recentServices'));
    }
}
