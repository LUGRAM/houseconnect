<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Notifications\DatabaseNotification;

class NotificationPolicy
{
    public function view(User $user, DatabaseNotification $notification): bool
    {
        return $notification->notifiable_id === $user->id;
    }

    public function delete(User $user, DatabaseNotification $notification): bool
    {
        return $notification->notifiable_id === $user->id;
    }

    public function markAsRead(User $user, DatabaseNotification $notification): bool
    {
        return $notification->notifiable_id === $user->id;
    }
}
