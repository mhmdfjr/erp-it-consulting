<?php
// app/Http/Controllers/NotificationController.php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function markAllRead(): RedirectResponse
    {
        Auth::user()->unreadNotifications->markAsRead();

        return back();
    }

    public function open(DatabaseNotification $notification): RedirectResponse
    {
        abort_unless($notification->notifiable_id === Auth::id(), 403);

        $notification->markAsRead();

        return redirect($notification->data['url'] ?? route('dashboard'));
    }
}
