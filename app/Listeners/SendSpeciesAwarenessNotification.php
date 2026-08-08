<?php

namespace App\Listeners;

use App\Events\ProtectedSpeciesReported;
use App\Models\User;
use App\Notifications\PrioritySpeciesReportedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Notification;

class SendSpeciesAwarenessNotification
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  \App\Events\ProtectedSpeciesReported  $event
     * @return void
     */
    public function handle(ProtectedSpeciesReported $event)
    {
        //
        $users = User::where('upt_id', '==', $event->upt_id)
            ->orWhere('upt_type', '==', "PUSAT")
            ->get();

        Notification::send($users, new PrioritySpeciesReportedNotification());
    }
}
