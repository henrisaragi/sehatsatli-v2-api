<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommunityReport extends Model
{
    use HasFactory;

    protected $table = 'community_reports';

    protected $fillable = [
        'id_laporan',
        'name',
        'report_date',
        'description',
        'reporter_id'
    ];

    public function laporan()
    {
        return $this->belongsTo('App\Models\GeneralReportSource', 'id_laporan', 'id');
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'reporter_id', 'id');
    }
}
