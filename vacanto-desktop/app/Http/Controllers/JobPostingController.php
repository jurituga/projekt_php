<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\Cv;
use App\Models\JobApplication;
use App\Models\JobPosting;
use App\Services\CvService;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;

class JobPostingController extends Controller
{
    public function __construct(private NotificationService $notifications) {}
    public function index(Request $request): View
    {
        $search = trim($request->input('q', ''));
        $location = trim($request->input('location', ''));
        $jobType = $request->input('type', '');

        $jobs = JobPosting::query()
            ->published()
            ->with('company')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($location !== '', fn ($q) => $q->where('location', 'like', "%{$location}%"))
            ->when($jobType !== '', fn ($q) => $q->where('job_type', $jobType))
            ->latest()
            ->get();

        return view('jobs.index', compact('jobs', 'search', 'location', 'jobType'));
    }

    public function show(JobPosting $job): View|RedirectResponse
    {
        if ($job->status !== 'published') {
            return redirect()->route('jobs.index');
        }

        $job->load('company');

        $canApply = auth()->check()
            && auth()->user()->role === UserRole::User;

        $alreadyApplied = false;
        $userCvs = collect();

        if ($canApply) {
            $alreadyApplied = JobApplication::where('job_id', $job->id)
                ->where('user_id', auth()->id())
                ->exists();

            $userCvs = Cv::where('user_id', auth()->id())
                ->orderByDesc('is_default')
                ->latest()
                ->get();
        }

        return view('jobs.show', compact('job', 'canApply', 'alreadyApplied', 'userCvs'));
    }

    public function apply(Request $request, JobPosting $job, CvService $cvService): RedirectResponse
    {
        if ($job->status !== 'published') {
            return redirect()->route('jobs.index');
        }

        if (! auth()->check() || auth()->user()->role !== UserRole::User) {
            return redirect()->route('login');
        }

        if (JobApplication::where('job_id', $job->id)->where('user_id', auth()->id())->exists()) {
            return redirect()->route('jobs.show', $job);
        }

        $validated = $request->validate([
            'cover_letter' => ['nullable', 'string'],
            'cv_id' => ['nullable', 'integer'],
            'cv_file' => ['nullable', 'file', 'mimes:pdf', 'max:5120'],
        ]);

        $cvId = $validated['cv_id'] ?? null;

        if ($request->hasFile('cv_file')) {
            try {
                $cv = $cvService->uploadForUser(auth()->user(), $request->file('cv_file'));
                $cvId = $cv->id;
            } catch (InvalidArgumentException $e) {
                return back()->withInput()->withErrors(['cv_file' => $e->getMessage()]);
            }
        } elseif ($cvId && ! Cv::where('user_id', auth()->id())->where('id', $cvId)->exists()) {
            $cvId = null;
        }

        JobApplication::create([
            'job_id' => $job->id,
            'user_id' => auth()->id(),
            'cv_id' => $cvId,
            'cover_letter' => $validated['cover_letter'] ?? null,
            'status' => 'pending',
        ]);

        $job->load('company.user');
        if ($companyUser = $job->company?->user) {
            $this->notifications->send(
                $companyUser,
                'New job application',
                auth()->user()->name.' applied for '.$job->title,
                route('company.jobs.applications', $job),
                'briefcase'
            );
        }

        return redirect()->route('jobs.show', $job)->with('success', 'Application submitted successfully.');
    }
}
