<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\FreelancerProfile;
use App\Models\User;
use App\Models\UserProfile;
use App\Services\DocumentUploadService;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use InvalidArgumentException;

class RegisterController extends Controller
{
    public function __construct(
        private readonly DocumentUploadService $uploads,
        private readonly NotificationService $notifications,
    ) {}

    public function showRegistrationForm(): View
    {
        return view('auth.register', [
            'postRole' => old('role', 'user'),
        ]);
    }

    public function register(Request $request): RedirectResponse
    {
        $role = UserRole::tryFrom($request->input('role', 'user')) ?? UserRole::User;

        if (! in_array($role, [UserRole::User, UserRole::Freelancer, UserRole::Company], true)) {
            $role = UserRole::User;
        }

        $rules = [
            'name' => ['required', 'string', 'min:2', 'max:100'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'role' => ['required', 'in:user,freelancer,company'],
        ];

        if ($role === UserRole::Company) {
            $rules['company_name'] = ['required', 'string', 'min:2', 'max:200'];
            $rules['company_industry'] = ['required', 'string', 'min:2', 'max:100'];
            $rules['company_description'] = ['nullable', 'string'];
            $rules['company_website'] = ['nullable', 'url', 'max:255'];
            $rules['company_phone'] = ['nullable', 'string', 'max:50'];
            $rules['company_business_registration'] = ['nullable', 'string', 'max:100'];
            $rules['company_tax_id'] = ['nullable', 'string', 'max:100'];
            $rules['company_government_id_file'] = ['required', 'file', 'max:5120', 'mimes:pdf,jpg,jpeg,png,gif'];
            $rules['company_business_reg_file'] = ['required', 'file', 'max:5120', 'mimes:pdf,jpg,jpeg,png,gif'];
        }

        if ($role === UserRole::Freelancer) {
            $rules['freelancer_type'] = ['required', 'in:general,electrician,plumber'];
            $rules['freelancer_bio'] = ['required', 'string', 'min:20'];
            $rules['freelancer_skills'] = ['required', 'string', 'min:2'];
            $rules['freelancer_hourly_rate'] = ['nullable', 'numeric', 'min:0'];
            $rules['freelancer_qualifications'] = ['nullable', 'string'];
            $rules['freelancer_government_id_file'] = ['nullable', 'file', 'max:5120', 'mimes:pdf,jpg,jpeg,png,gif'];
            $rules['freelancer_certification_file'] = ['nullable', 'file', 'max:5120', 'mimes:pdf,jpg,jpeg,png,gif'];
        }

        $validated = $request->validate($rules, [
            'company_government_id_file.required' => 'Please upload a government ID document.',
            'company_business_reg_file.required' => 'Please upload your business registration document.',
            'company_government_id_file.mimes' => 'Government ID must be a PDF or image (JPEG, PNG, GIF).',
            'company_business_reg_file.mimes' => 'Business registration document must be a PDF or image (JPEG, PNG, GIF).',
        ]);

        try {
            $companyGovIdPath = null;
            $companyBusinessRegPath = null;
            $freelancerGovIdPath = null;
            $freelancerCertPath = null;

            if ($role === UserRole::Company) {
                $companyGovIdPath = $this->uploads->store(
                    $request->file('company_government_id_file'),
                    config('vacanto.upload_paths.government_ids'),
                    'company_gov'
                );
                $companyBusinessRegPath = $this->uploads->store(
                    $request->file('company_business_reg_file'),
                    config('vacanto.upload_paths.government_ids'),
                    'company_reg'
                );
            }

            if ($role === UserRole::Freelancer) {
                $freelancerGovIdPath = $this->uploads->store(
                    $request->file('freelancer_government_id_file'),
                    config('vacanto.upload_paths.government_ids'),
                    'freelancer_gov'
                );
                $freelancerCertPath = $this->uploads->store(
                    $request->file('freelancer_certification_file'),
                    config('vacanto.upload_paths.certifications'),
                    'freelancer_cert'
                );
            }
        } catch (InvalidArgumentException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        $status = in_array($role, [UserRole::Company, UserRole::Freelancer], true)
            ? UserStatus::Pending
            : UserStatus::Active;

        $user = DB::transaction(function () use ($validated, $role, $status, $request, $companyGovIdPath, $companyBusinessRegPath, $freelancerGovIdPath, $freelancerCertPath) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => $validated['password'],
                'role' => $role,
                'status' => $status,
            ]);

            if ($role === UserRole::Company) {
                Company::create([
                    'user_id' => $user->id,
                    'company_name' => $validated['company_name'],
                    'description' => $request->input('company_description'),
                    'industry' => $validated['company_industry'],
                    'website' => $request->input('company_website'),
                    'phone' => $request->input('company_phone'),
                    'business_registration_number' => $request->input('company_business_registration'),
                    'tax_id_vat' => $request->input('company_tax_id'),
                    'business_registration_document_path' => $companyBusinessRegPath,
                    'government_id_path' => $companyGovIdPath,
                ]);
            } elseif ($role === UserRole::Freelancer) {
                FreelancerProfile::create([
                    'user_id' => $user->id,
                    'freelancer_type' => $validated['freelancer_type'],
                    'bio' => $validated['freelancer_bio'],
                    'skills' => $validated['freelancer_skills'],
                    'hourly_rate' => $request->input('freelancer_hourly_rate'),
                    'government_id_path' => $freelancerGovIdPath,
                    'qualifications' => $request->input('freelancer_qualifications'),
                    'certification_path' => $freelancerCertPath,
                ]);
            } else {
                UserProfile::create(['user_id' => $user->id]);
            }

            return $user;
        });

        if ($status === UserStatus::Active) {
            Auth::login($user);
        }

        if ($status === UserStatus::Pending) {
            $this->notifications->notifyAdmins(
                'New registration pending review',
                $user->name.' registered as a '.ucfirst($role->value).' and is awaiting approval.',
                route('admin.users.review', $user),
                'bell'
            );

            return redirect()->route('login', ['pending' => 1]);
        }

        return redirect()->route('user.dashboard');
    }
}
