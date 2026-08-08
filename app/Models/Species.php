<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Species extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $table = "species";

    protected $primaryKey = 'id';

    protected $fillable = [
        'id',
        'creator',
        'updater',
        'status',
        'category',
        'code',
        'name',
        'latin_name',
        'type',
        'priority',
        'protected'
    ];

    public function creator()
    {
        return $this->belongsTo('App\Models\User', 'creator');
    }

    public function updater()
    {
        return $this->belongsTo('App\Models\User', 'updater');
    }

    public function source(){
        return $this->hasMany('App\Models\GeneralReportSource','species_id','id');
    }

     protected $casts = [
        'priority' => 'boolean',
        'protected' => 'boolean'
    ];
}
