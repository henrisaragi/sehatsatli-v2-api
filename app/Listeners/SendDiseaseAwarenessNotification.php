<?php

namespace App\Listeners;

use App\Events\PriorityDiseaseReported;
use App\Notifications\PrioritySpeciesReportedNotification;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Notification;

class SendDiseaseAwarenessNotification
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
     * @param  \App\Events\PriorityDiseaseReported  $event
     * @return void
     */
    public function handle(PriorityDiseaseReported $event)
    {
        // dari event cari $users dengan upt id yg sama, dan semua user di upt pusat

        $users = User::where('upt_id', '==', $event->upt_id)
            ->orWhere('upt_type', '==', "PUSAT")
            ->get();

        // untuk notification sendnya belum enriq gandi karena di bagian notification belum ada yang buat disease

        Notification::send($users, new PrioritySpeciesReportedNotification());
    }
}
