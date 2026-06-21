<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\PasswordResetToken;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ResetPasswordController extends Controller
{
    public function showResetForm(Request $request, string $token): View
    {
        $reset = $this->findValidToken($token);

        if (! $reset) {
            return view('auth.reset-password', [
                'error' => $this->tokenErrorMessage($token),
                'validToken' => false,
                'success' => false,
            ]);
        }

        return view('auth.reset-password', [
            'token' => $token,
            'userName' => $reset->user->name,
            'validToken' => true,
            'success' => false,
        ]);
    }

    public function reset(Request $request): View|RedirectResponse
    {
        $request->validate([
            'token' => ['required', 'string'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $reset = $this->findValidToken($request->input('token'));

        if (! $reset) {
            return view('auth.reset-password', [
                'error' => $this->tokenErrorMessage($request->input('token')),
                'validToken' => false,
                'success' => false,
            ]);
        }

        User::whereKey($reset->user_id)->update([
            'password' => $request->input('password'),
        ]);

        $reset->update(['used' => true]);

        return view('auth.reset-password', [
            'success' => true,
            'validToken' => false,
        ]);
    }

    private function findValidToken(string $token): ?PasswordResetToken
    {
        if ($token === '') {
            return null;
        }

        $reset = PasswordResetToken::with('user')
            ->where('token', $token)
            ->first();

        if (! $reset || ! $reset->isValid()) {
            return null;
        }

        return $reset;
    }

    private function tokenErrorMessage(string $token): string
    {
        if ($token === '') {
            return 'Invalid or missing reset token.';
        }

        $reset = PasswordResetToken::where('token', $token)->first();

        if (! $reset) {
            return 'Invalid reset link. Please request a new one.';
        }

        if ($reset->used) {
            return 'This reset link has already been used.';
        }

        return 'This reset link has expired. Please request a new one.';
    }
}
