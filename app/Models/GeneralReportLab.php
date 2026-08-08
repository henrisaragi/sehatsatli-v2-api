<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class GeneralReportLab extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'general_report_labs';

    protected $primaryKey = 'id';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'final_disease_id',
        'final_diagnosis',
        'follow_up',
        'created_at',
        'updated_at',
    ];

    public function species()
    {
        return $this->belongsTo('App\Models\GeneralReportSpecies', 'id', 'id');
    }

    public function location()
    {
        return $this->belongsTo('App\Models\GeneralReportLocation', 'id', 'id');
    }

    public function diagnosis()
    {
        return $this->belongsTo('App\Models\GeneralReportDiagnosis', 'id', 'id');
    }

    public function doctor()
    {
        return $this->belongsTo('App\Models\GeneralReportVerification', 'id', 'id');
    }

    public function disease()
    {
        return $this->belongsTo('App\Models\Disease', 'final_disease_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly($this->fillable);
    }
}
