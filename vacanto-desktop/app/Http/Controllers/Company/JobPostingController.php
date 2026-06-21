<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Concerns\RequiresCompanyProfile;
use App\Http\Controllers\Controller;
use App\Models\JobPosting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class JobPostingController extends Controller
{
    use RequiresCompanyProfile;

    public function index(): View|RedirectResponse
    {
        $company = $this->requireCompany();
        if ($company instanceof RedirectResponse) {
            return $company;
        }

        $jobs = JobPosting::where('company_id', $company->id)
            ->withCount('applications')
            ->latest()
            ->get();

        return view('company.jobs.index', compact('jobs'));
    }

    public function create(): View|RedirectResponse
    {
        $company = $this->requireCompany();
        if ($company instanceof RedirectResponse) {
            return $company;
        }

        return view('company.jobs.form', ['job' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        $company = $this->requireCompany();
        if ($company instanceof RedirectResponse) {
            return $company;
        }

        $validated = $this->validatedJobData($request);

        JobPosting::create([
            ...$validated,
            'company_id' => $company->id,
        ]);

        return redirect()->route('company.jobs.index')->with('success', 'Job published.');
    }

    public function edit(JobPosting $job): View|RedirectResponse
    {
        $company = $this->requireCompany();
        if ($company instanceof RedirectResponse) {
            return $company;
        }

        abort_unless($job->company_id === $company->id, 404);

        return view('company.jobs.form', compact('job'));
    }

    public function update(Request $request, JobPosting $job): RedirectResponse
    {
        $company = $this->requireCompany();
        if ($company instanceof RedirectResponse) {
            return $company;
        }

        abort_unless($job->company_id === $company->id, 404);

        $job->update($this->validatedJobData($request));

        return redirect()->route('company.jobs.edit', $job)->with('success', 'Job updated.');
    }

    public function destroy(JobPosting $job): RedirectResponse
    {
        $company = $this->requireCompany();
        if ($company instanceof RedirectResponse) {
            return $company;
        }

        abort_unless($job->company_id === $company->id, 404);

        $job->delete();

        return redirect()->route('company.jobs.index')->with('success', 'Job deleted.');
    }

    private function validatedJobData(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'min:2', 'max:255'],
            'description' => ['required', 'string'],
            'location' => ['nullable', 'string', 'max:255'],
            'job_type' => ['required', 'in:full_time,part_time,contract,internship'],
            'salary_min' => ['nullable', 'numeric', 'min:0'],
            'salary_max' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', 'in:draft,published,closed'],
        ]);
    }
}
