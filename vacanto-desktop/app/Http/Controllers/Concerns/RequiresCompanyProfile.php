<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Company;
use Illuminate\Http\RedirectResponse;

trait RequiresCompanyProfile
{
    protected function requireCompany(): Company|RedirectResponse
    {
        $company = auth()->user()->company;

        if (! $company) {
            return redirect()->route('company.profile.edit')
                ->with('error', 'Please complete your company profile first.');
        }

        return $company;
    }
}
