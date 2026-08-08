<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserInbox extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';

    protected $fillable = [
        'creator',
        'updater',
        'status',
        'user_id',
        'received_date',
        'read_date',
        'subject',
        'message',
        'read',
    ];

    public function creator()
    {
        return $this->belongsTo('App\Models\User', 'creator');
    }

    public function updater()
    {
        return $this->belongsTo('App\Models\User', 'updater');
    }

    public function user(){
        return $this->belongsTo('App\Models\User','user_id');
    }
}
