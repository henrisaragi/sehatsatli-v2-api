<?php

namespace App\Events;

use App\Models\GeneralReportSource;
use App\Models\Upt;
use App\Models\Species;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ProtectedSpeciesReported
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $report_id;
    public $report_code;
    public $report_date;
    public $user_id;
    public $user_name;
    public $species_id;
    public $species_name;
    public $upt_id;
    public $upt_name;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(GeneralReportSource $source)
    {
        // Dapatkan semua variabel di atas menggunakan $source
        if (!$source) {
            return;
        }
        $upt = Upt::find($source->upt_id);
        $user = User::find($source->user_id);
        $species = Species::find($source->species_id);

        // Report
        $this->report_id = $source->id;
        $this->report_code = $source->report_code;
        $this->report_date = $source->report_date;

        // User
        $this->user_id = $user->id;
        $this->user_name = $user->name;

        // Species
        $this->species_id = $species->id;
        $this->species_name = $species->name;

        // UPT
        $this->upt_id = $upt->id;
        $this->upt_name = $upt->name;
    }
}
