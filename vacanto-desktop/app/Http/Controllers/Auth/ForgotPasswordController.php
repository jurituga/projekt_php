<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\PasswordResetToken;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class ForgotPasswordController extends Controller
{
    public function showLinkRequestForm(): View
    {
        return view('auth.forgot-password');
    }

    public function sendResetLink(Request $request): View|RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::where('email', $validated['email'])->first();
        $resetLink = null;

        if ($user) {
            PasswordResetToken::where('user_id', $user->id)
                ->where('used', false)
                ->update(['used' => true]);

            $token = bin2hex(random_bytes(32));

            PasswordResetToken::create([
                'user_id' => $user->id,
                'token' => $token,
                'expires_at' => now()->addHour(),
            ]);

            $resetLink = route('password.reset', ['token' => $token]);

            @Mail::raw(
                "Hi {$user->name},\n\nClick the link below to reset your password:\n{$resetLink}\n\nThis link expires in 1 hour.\n\nIf you did not request this, ignore this email.",
                function ($message) use ($user) {
                    $message->to($user->email)
                        ->subject('Vacanto — Password Reset');
                }
            );
        }

        return view('auth.forgot-password', [
            'success' => true,
            'resetLink' => $resetLink,
        ]);
    }
}
