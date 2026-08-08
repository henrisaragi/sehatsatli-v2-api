<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UptSpecies extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';

    protected $fillable = [
        'creator',
        'updater',
        'status',
        'upt_id',
        'name',
        'species_id',
        'population',
    ];

    public function creator()
    {
        return $this->belongsTo('App\Models\User', 'creator');
    }

    public function updater()
    {
        return $this->belongsTo('App\Models\User', 'updater');
    }

    public function upt(){
        return $this->belongsTo('App\Models\UPT','upt_id');
    }

    public function species(){
        return $this->belongsTo('App\Models\species_id');
    }
}
