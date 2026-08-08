<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use Laravel\Passport\HasApiTokens;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class User extends Authenticatable implements HasMedia
{
    protected $table = "users";
    // protected $primaryKey = 'id'; // or null
    // public $incrementing = false;

    use HasApiTokens, HasFactory, Notifiable,  LogsActivity, InteractsWithMedia;

    protected $fillable = [
        'username',
        'username1',
        'name',
        'email',
        'phone',
        'phones',
        'gender',
        'occupation',
        'user_level',
        'upt_id',
        'upt_type',
        'trained',
        'is_doctor',
        'show_in_contact',
        'training_mode',
        'web_admin',
        'all_notification',
        'drh_specialist',
        'devices',
        'heads_upt'
        //'is_active'
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'reset_password',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'phones' => 'array',
        'reset_password' => 'boolean',
        'show_in_contact' => 'boolean',
        'trained' => 'boolean',
        'training_mode' => 'boolean',
        'all_notification' => 'boolean',
        'is_doctor' => 'boolean',
        'heads_upt' => 'boolean',
        //'is_active' => 'boolean',
        // 'devices' => 'array' //object
    ];

    public function upt()
    {
        return $this->belongsTo('App\Models\UPT', 'upt_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly($this->fillable);
    }

    public function routeNotificationForFcm()
    {
        return $this->devices;
    }
}
