<?php

namespace App\Http\Controllers\Freelancer;

use App\Http\Controllers\Controller;
use App\Models\FreelancerProfile;
use App\Services\DocumentUploadService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(): View
    {
        $user = auth()->user();
        $profile = $user->freelancerProfile ?? FreelancerProfile::create(['user_id' => $user->id]);

        return view('freelancer.profile', compact('user', 'profile'));
    }

    public function update(Request $request, DocumentUploadService $uploader): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'freelancer_type' => ['required', 'in:general,electrician,plumber'],
            'bio' => ['nullable', 'string', 'max:5000'],
            'skills' => ['nullable', 'string', 'max:500'],
            'hourly_rate' => ['nullable', 'numeric', 'min:0'],
            'government_id_ref' => ['nullable', 'string', 'max:100'],
            'qualifications' => ['nullable', 'string', 'max:2000'],
            'government_id_file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,gif', 'max:5120'],
            'certification_file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,gif', 'max:5120'],
        ]);

        $user = auth()->user();
        $user->update(['name' => $validated['name']]);

        $profile = $user->freelancerProfile ?? new FreelancerProfile(['user_id' => $user->id]);
        $govIdPath = $profile->government_id_path;
        $certPath = $profile->certification_path;

        if ($request->hasFile('government_id_file')) {
            try {
                $govIdPath = $uploader->store(
                    $request->file('government_id_file'),
                    config('vacanto.upload_paths.government_ids'),
                    'gov_'.$user->id,
                );
            } catch (\InvalidArgumentException $e) {
                return back()->withInput()->withErrors(['government_id_file' => $e->getMessage()]);
            }
        }

        if ($request->hasFile('certification_file')) {
            try {
                $certPath = $uploader->store(
                    $request->file('certification_file'),
                    config('vacanto.upload_paths.certifications'),
                    'cert_'.$user->id,
                );
            } catch (\InvalidArgumentException $e) {
                return back()->withInput()->withErrors(['certification_file' => $e->getMessage()]);
            }
        }

        $profile->fill([
            'freelancer_type' => $validated['freelancer_type'],
            'bio' => $validated['bio'] ?? '',
            'skills' => $validated['skills'] ?? '',
            'hourly_rate' => $validated['hourly_rate'] ?? null,
            'government_id_ref' => $validated['government_id_ref'] ?? null,
            'government_id_path' => $govIdPath,
            'qualifications' => $validated['qualifications'] ?? null,
            'certification_path' => $certPath,
        ]);
        $profile->user_id = $user->id;
        $profile->save();

        return redirect()->route('freelancer.profile.edit')->with('success', 'Profile updated.');
    }
}
