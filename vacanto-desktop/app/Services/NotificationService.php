<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\User;
use App\Notifications\AppNotification;
use Illuminate\Support\Collection;

class NotificationService
{
    public function send(User $user, string $title, string $message, ?string $url = null, string $icon = 'bell'): void
    {
        $user->notify(new AppNotification($title, $message, $url, $icon));
    }

    public function notifyAdmins(string $title, string $message, ?string $url = null, string $icon = 'bell'): void
    {
        $this->notifyUsers(
            User::where('role', UserRole::Admin)->get(),
            $title,
            $message,
            $url,
            $icon
        );
    }

    public function notifyUsers(Collection $users, string $title, string $message, ?string $url = null, string $icon = 'bell'): void
    {
        foreach ($users as $user) {
            $this->send($user, $title, $message, $url, $icon);
        }
    }

    public function unreadCount(int $userId): int
    {
        return User::find($userId)?->unreadNotifications()->count() ?? 0;
    }
}
