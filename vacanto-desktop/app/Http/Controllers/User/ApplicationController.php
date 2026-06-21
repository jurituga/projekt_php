<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\JobApplication;
use Illuminate\View\View;

class ApplicationController extends Controller
{
    public function index(): View
    {
        $applications = JobApplication::with(['jobPosting.company'])
            ->where('user_id', auth()->id())
            ->latest()
            ->get();

        return view('user.applications', compact('applications'));
    }
}
