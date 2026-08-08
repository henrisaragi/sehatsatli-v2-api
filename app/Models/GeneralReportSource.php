<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class GeneralReportSource extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, LogsActivity;

    protected $primaryKey = 'id';
    // public $incrementing = false;

    protected $fillable = [
        'id',
        'created_at',
        'updated_at',
        'creator',
        'updater',
        'training',
        'status',
        'user_id',
        'upt_id',
        'report_code',
        'report_date',
        'location',
        'species_id',
        'protected',
        'species_name',
        'dead',
        'live',
        'description',
        'client_id'
    ];

    public function creator()
    {
        return $this->belongsTo('App\Models\User', 'creator');
    }

    public function updater()
    {
        return $this->belongsTo('App\Models\User', 'updater');
    }

    public function species()
    {
        return $this->belongsTo('App\Models\GeneralReportSpecies', 'id', 'id');
    }

    public function specieses()
    {
        return $this->belongsTo('App\Models\Species', 'species_id');
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id');
    }

    public function upt()
    {
        return $this->belongsTo('App\Models\UPT', 'upt_id');
    }

    public function reporter()
    {
        return $this->belongsTo('App\Models\GeneralReportReporter', 'id', 'id');
    }

    public function acknowled()
    {
        return $this->belongsTo('App\Models\GeneralReportAcknowledgement', 'id', 'id');
    }

    public function lab()
    {
        return $this->belongsTo('App\Models\GeneralReportLab', 'id', 'id');
    }

    public function location()
    {
        return $this->belongsTo('App\Models\GeneralReportLocation', 'id', 'id');
    }

    public function locations()
    {
        return $this->belongsTo('App\Models\GeneralReportLocation', 'id', 'id');
    }

    public function diagnoses()
    {
        return $this->belongsTo('App\Models\GeneralReportDiagnosis', 'id', 'id');
    }

    public function verification()
    {
        return $this->belongsTo('App\Models\GeneralReportVerification', 'id', 'id');
    }

    public function investigation()
    {
        return $this->belongsTo('App\Models\GeneralReportInvestigation', 'id', 'id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly($this->fillable);
    }
}
