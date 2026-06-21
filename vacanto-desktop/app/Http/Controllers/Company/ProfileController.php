<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Services\DocumentUploadService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(): View
    {
        $user = auth()->user();
        $company = $user->company ?? Company::create([
            'user_id' => $user->id,
            'company_name' => $user->name.' Company',
        ]);

        return view('company.profile', compact('user', 'company'));
    }

    public function update(Request $request, DocumentUploadService $uploader): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'company_name' => ['required', 'string', 'min:2', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'industry' => ['nullable', 'string', 'max:255'],
            'website' => ['nullable', 'url', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:500'],
            'business_registration_number' => ['nullable', 'string', 'max:100'],
            'tax_id_vat' => ['nullable', 'string', 'max:100'],
            'government_id_ref' => ['nullable', 'string', 'max:100'],
            'government_id_file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,gif', 'max:5120'],
        ]);

        $user = auth()->user();
        $user->update(['name' => $validated['name']]);

        $company = $user->company ?? new Company(['user_id' => $user->id]);
        $govIdPath = $company->government_id_path;

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

        $company->fill([
            'company_name' => $validated['company_name'],
            'description' => $validated['description'] ?? '',
            'industry' => $validated['industry'] ?? '',
            'website' => $validated['website'] ?? '',
            'phone' => $validated['phone'] ?? '',
            'address' => $validated['address'] ?? '',
            'business_registration_number' => $validated['business_registration_number'] ?? null,
            'tax_id_vat' => $validated['tax_id_vat'] ?? null,
            'government_id_ref' => $validated['government_id_ref'] ?? null,
            'government_id_path' => $govIdPath,
        ]);
        $company->user_id = $user->id;
        $company->save();

        return redirect()->route('company.profile.edit')->with('success', 'Profile updated.');
    }
}
