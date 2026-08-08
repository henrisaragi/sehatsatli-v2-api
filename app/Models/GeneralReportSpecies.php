<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class GeneralReportSpecies extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'general_report_species';

    protected $primaryKey = 'id';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'created_at',
        'updated_at',
        'category',
        'protected',
        'protected_species',
        'species_name',
        'species_latin_name',
        'species_family',
        // 'species_gender',
        'species_age',
        'population',
    ];

    protected $casts = [
        'protected' => 'boolean'
    ];

    public function location()
    {
        return $this->belongsTo('App\Models\GeneralReportLocation', 'id', 'id');
    }

    public function reporter()
    {
        return $this->belongsTo('App\Models\GeneralReportReporter', 'id', 'id');
    }

    public function source(){
        return $this->hasMany('App\Models\GeneralReportSource','id','species_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly($this->fillable);
    }
}
