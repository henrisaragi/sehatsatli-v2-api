<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Upt extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $primaryKey = 'id';

    protected $table = 'upts';

    protected $fillable = [
        'id',
        'creator',
        'updater',
        'status',
        'name',
        'code',
        'type',
        'upt_type',
        'unit_id', // parent id, misal Lembaga Khusus dibawah BKSDA
        'address',
        'status',
        'latitude',
        'longitude',
        'province_id',
        'district_id',
        'subdistrict_id',
        'village_id',
        'conservation_type',
        'insitu_conservation',
        'insitu_other',
        'exsitu_conservation',
        'exsitu_other',
        'upt_heads'
    ];

    protected $casts = [
        'insitu_conservation' => 'array',
        'exsitu_conservation' => 'array',
        'upt_heads' => 'array'
    ];

    public function creator()
    {
        return $this->belongsTo('App\Models\User', 'creator');
    }

    public function updater()
    {
        return $this->belongsTo('App\Models\User', 'updater');
    }

    public function user()
    {
        return $this->hasMany('App\Models\User', 'upt_id', 'id');
    }

    public function generalReportSource()
    {
        return $this->hasMany('App\Models\GeneralReportSource', 'upt_id', 'id');
    }

    public function species()
    {
        return $this->hasMany('App\Models\UptSpecies', 'upt_id', 'id');
    }
}
