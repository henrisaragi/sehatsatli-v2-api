<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;


class GeneralReportVerification extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, LogsActivity;
    protected $table = 'general_report_doctor_verifications';
    protected $primaryKey = 'id';
    public $incrementing = false;

    protected $fillable = [
        "id",
        'created_at',
        'updated_at',
        // 'doctor_name',
        // 'doctor_occupation',
        'verified_date',
        'verified',
        'verification',
        'temporary_disease_id',
        'sampling',
        'action',
        'doctor_information',
        'involved_doctors'

    ];

    protected $casts = [
        'involved_doctors' => 'array'
    ];

    public function user()
    {
        return $this->belongsTo('APp\Models\User', 'user_id');
    }

    public function disease()
    {
        return $this->belongsTo('App\Models\Disease', 'temporary_disease_id');
    }
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly($this->fillable);
    }
}
