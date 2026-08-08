<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Disease extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $primaryKey = 'id';

    protected $fillable = [
        'creator',
        'updater',
        'status',
        'name',
        'code',
        "short_description",
        'description',
        'symptom',
        'symptom_details',
        'treatment',
        'treatment_details',
        'priority',
        'zoonosis',
    ];

    protected $casts = [
        'symptom_details' => 'array',
        'treatment_details' => 'array',
        'priority' => 'boolean',
        'zoonosis' => 'boolean',
    ];

    public function creator()
    {
        return $this->belongsTo('App\Models\User', 'creator');
    }

    public function updater()
    {
        return $this->belongsTo('App\Models\User', 'updater');
    }
}
