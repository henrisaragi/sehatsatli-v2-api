<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class GeneralReportReporter extends Model
{
    use HasFactory, LogsActivity;

    protected $primaryKey = 'id';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'created_at',
        'updated_at',
        'user_id',
        'name',
        'gender',
        'occupation',
        'phone',
        'address',
        'case_found',
        'additional_reporters',
        'acknowledged_by'
        //'report_date',
    ];

    protected $casts = [
        'additional_reporters' => 'array',
        'acknowledged_by' => 'array'
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

    public function lab()
    {
        return $this->belongsTo('App\Models\GeneralReportLab', 'id', 'id');
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id');
    }
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly($this->fillable);
    }
}
