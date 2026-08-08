<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Option extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';

    protected $fillable = [
        'creator',
        'updater',
        'name',
        'slug',
        'category',
        'field',
        'type',
        'value'
    ];

    protected $casts = [
        'value' => 'array'
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

