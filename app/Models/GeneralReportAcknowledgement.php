<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class GeneralReportAcknowledgement extends Model
{
    use HasFactory, LogsActivity;

    protected $primaryKey = 'id';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'upt_head_date',
        'upt_head_name',
        'upt_head_occupation',
        'upt_head_sign',
        'created_at',
        'updated_at',
    ];
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly($this->fillable);
    }
}
