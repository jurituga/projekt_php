<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\UserProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(): View
    {
        $user = auth()->user();
        $profile = $user->userProfile ?? UserProfile::create(['user_id' => $user->id]);

        return view('user.profile', compact('user', 'profile'));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:500'],
            'headline' => ['nullable', 'string', 'max:255'],
        ]);

        $user = auth()->user();
        $user->update(['name' => $validated['name']]);

        $profile = $user->userProfile ?? new UserProfile(['user_id' => $user->id]);
        $profile->fill([
            'phone' => $validated['phone'] ?? '',
            'address' => $validated['address'] ?? '',
            'headline' => $validated['headline'] ?? '',
        ]);
        $profile->user_id = $user->id;
        $profile->save();

        return redirect()->route('user.profile.edit')->with('success', 'Profile updated.');
    }
}
