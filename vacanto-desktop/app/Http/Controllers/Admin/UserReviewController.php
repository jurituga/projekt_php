<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\View\View;

class UserReviewController extends Controller
{
    public function show(User $user): View
    {
        abort_if($user->role === UserRole::Admin, 404);

        $user->load(['company', 'freelancerProfile']);

        return view('admin.users.review', compact('user'));
    }
}
