<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class GeneralReportLocation extends Model
{
    use HasFactory, LogsActivity;

    protected $primaryKey = 'id';
    public $incrementing = false;

    // protected $table = 'general_report_location';

    protected $fillable = [
        'id',
        'upt_id',
        'upt_type',
        'upt_name',
        'location_name',
        'conservation_type',
        'insitu_conservation',
        'insitu_other',
        'exsitu_conservation',
        'exsitu_other',
        'province_id',
        'district_id',
        'subdistrict_id',
        'village_id',
        'location_description',
        'latitude',
        'longitude',
        'created_at',
        'updated_at',
    ];


    protected $casts = [
        'exsitu_conservation' => 'array',
        'insitu_conservation' => 'array'
    ];

    public function report()
    {
        return $this->belongsTo('App\Models\GeneralReportSource', 'id', 'id');
    }

    public function lab()
    {
        return $this->belongsTo('App\Models\GeneralReportLab', 'id', 'id');
    }

    public function species()
    {
        return $this->belongsTo('App\Models\GeneralReportSpecies', 'id', 'id');
    }

    public function province(){
        return $this->belongsTo('App\Models\Province','province_id');
    }

    public function subdistrict(){
        return $this->belongsTo('App\Models\SubDistrict','subdistrict_id');
    }

    public function district(){
        return $this->belongsTo('App\Models\District','district_id');
    }

    public function village(){
        return $this->belongsTo('App\Models\Village','village_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly($this->fillable);
    }
}
