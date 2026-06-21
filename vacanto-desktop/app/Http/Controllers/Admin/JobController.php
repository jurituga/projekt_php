<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JobPosting;
use App\Models\Service;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class JobController extends Controller
{
    public function index(): View
    {
        $jobs = JobPosting::with('company')->latest()->get();

        return view('admin.jobs.index', compact('jobs'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'job_id' => ['required', 'integer', 'exists:jobs,id'],
        ]);

        JobPosting::where('id', $validated['job_id'])->delete();

        return redirect()->route('admin.jobs.index')->with('success', 'Job deleted.');
    }
}
