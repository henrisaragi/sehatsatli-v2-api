<?php

namespace App\Events;

use App\Models\GeneralReportSource;
use App\Models\Upt;
use App\Models\GeneralReportReporter;
use App\Models\GeneralReportDiagnosis;
use App\Models\Disease;
use App\Models\User;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PriorityDiseaseReported
{
    use Dispatchable, InteractsWithSockets, SerializesModels;


    public $report_id;
    public $report_code;
    public $user_id;
    public $user_name;
    public $disease_id;
    public $disease_name;
    public $upt_id;
    public $upt_name;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(GeneralReportSource $source)
    {
        if (!$source) {
            return;
        }
        // Dapatkan semua variabel di atas menggunakan $source
        $upt = Upt::find($source->upt_id);
        $user = User::find($source->user_id);
        $diagnosis = GeneralReportDiagnosis::find($source->id);
        $disease = Disease::find($diagnosis->temporary_disease_id);

        // Report
        $this->report_id = $source->id;
        $this->report_code = $source->report_code;

        // User
        $this->user_id = $user->id;
        $this->user_name = $user->name;

        // Disease
        $this->disease_id = $disease->id;
        $this->disease_name = $disease->name;

        // Upt
        $this->upt_id = $upt->id;
        $this->upt_name = $upt->name;
    }
}
