<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function __construct(private NotificationService $notifications) {}
    public function index(): View
    {
        $users = User::where('role', '!=', UserRole::Admin)
            ->orderByDesc('created_at')
            ->get();

        $pendingCount = $users->where('status', UserStatus::Pending)->count();

        return view('admin.users', compact('users', 'pendingCount'));
    }

    public function action(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'action' => ['required', 'in:block,activate,delete'],
        ]);

        $user = User::findOrFail($validated['user_id']);

        if ($user->role === UserRole::Admin) {
            return redirect()->route('admin.users.index');
        }

        match ($validated['action']) {
            'block' => $user->update(['status' => UserStatus::Blocked]),
            'activate' => $user->update(['status' => UserStatus::Active]),
            'delete' => $user->delete(),
        };

        if ($validated['action'] === 'activate') {
            $this->notifications->send(
                $user,
                'Account activated',
                'Your Vacanto account has been approved. You can now sign in and use the platform.',
                route('login'),
                'bell'
            );
        } elseif ($validated['action'] === 'block') {
            $this->notifications->send(
                $user,
                'Account blocked',
                'Your Vacanto account has been blocked. Contact support if you believe this is a mistake.',
                route('login'),
                'bell'
            );
        }

        return redirect()->route('admin.users.index')->with('success', 'User updated.');
    }
}
