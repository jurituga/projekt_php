<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\JobApplication;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ApplicationActionController extends Controller
{
    public function __construct(private NotificationService $notifications) {}
    public function __invoke(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'application_id' => ['required', 'integer'],
            'action' => ['required', 'in:accept,reject'],
        ]);

        $application = JobApplication::where('id', $validated['application_id'])
            ->whereHas('jobPosting.company', fn ($q) => $q->where('user_id', auth()->id()))
            ->with(['user', 'jobPosting'])
            ->first();

        if (! $application) {
            return back()->with('error', 'Application not found.');
        }

        $application->update([
            'status' => $validated['action'] === 'accept' ? 'accepted' : 'rejected',
        ]);

        $accepted = $validated['action'] === 'accept';
        $this->notifications->send(
            $application->user,
            $accepted ? 'Application accepted' : 'Application rejected',
            $accepted
                ? 'Your application for '.$application->jobPosting->title.' was accepted.'
                : 'Your application for '.$application->jobPosting->title.' was rejected.',
            route('user.applications.index'),
            'briefcase'
        );

        return back()->with('success', 'Application '.$validated['action'].'ed.');
    }
}
