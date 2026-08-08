<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class GeneralReportDiagnosis extends Model
{
    use HasFactory, LogsActivity;

    protected $primaryKey = 'id';
    public $incrementing = false;

    protected $fillable = [
        "id",
        'report_date',
        'dead',
        'dead_sign',
        'dead_sign_other',
        'live',
        'live_sign',
        'live_sign_other',
        'chronological',
        'sampling', // general report sample
        'follow_up',
        'diagnosis',
        'temporary_diagnosis_id',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'dead_sign' => 'array',
        'live_sign' => 'array'
    ];

    public function species()
    {
        return $this->belongsTo('App\Models\GeneralReportSpecies', 'id', 'id');
    }

    public function report()
    {
        return $this->belongsTo('App\Models\GeneralReportReporter', 'id', 'id');
    }

    public function disease()
    {
        return $this->belongsTo('App\Models\Disease', 'temporary_diagnosis_id');
    }
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly($this->fillable);
    }
}
