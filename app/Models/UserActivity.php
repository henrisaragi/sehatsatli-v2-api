<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserActivity extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';

    protected $fillable = [
        'creator',
        'updater',
        'status',
        'user_id',
        'month',
        'year',
        'send_report',
        'open_count',
        'last_seen',
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
        return $this->belongsTo('App\Models\User', 'user_id');
    }

    public function getDate(){
        return "{$this->year}_{$this->month}";
    }
}
