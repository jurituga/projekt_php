<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function showLoginForm(Request $request): View
    {
        return view('auth.login', [
            'pending' => $request->query('pending') === '1',
        ]);
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $remember = $request->boolean('remember');
        $isAdminLogin = $request->boolean('admin_login');

        if (! Auth::attempt($credentials, $remember)) {
            return back()
                ->withInput($request->only('email'))
                ->with('error', 'Invalid email or password.');
        }

        $user = Auth::user();

        if ($user->status === UserStatus::Blocked) {
            Auth::logout();
            return back()
                ->withInput($request->only('email'))
                ->with('error', 'Your account has been blocked. Contact support.');
        }

        if ($user->status === UserStatus::Pending && ! $user->isAdmin()) {
            Auth::logout();
            return back()
                ->withInput($request->only('email'))
                ->with('error', 'Your account is pending approval.');
        }

        if ($isAdminLogin && ! $user->isAdmin()) {
            Auth::logout();
            return back()
                ->withInput($request->only('email'))
                ->with('error', 'Admin access only. Use regular login.');
        }

        $request->session()->regenerate();

        return redirect()->intended($this->dashboardRouteFor($user->role));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    private function dashboardRouteFor(UserRole $role): string
    {
        return match ($role) {
            UserRole::Admin => route('admin.dashboard'),
            UserRole::Company => route('company.dashboard'),
            UserRole::Freelancer => route('freelancer.dashboard'),
            UserRole::User => route('user.dashboard'),
        };
    }
}
