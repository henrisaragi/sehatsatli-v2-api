<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Province extends Model
{
    use HasFactory;
    protected $table = "provinces";

    public function province(){
        return $this->hasOne('App\Models\GeneralReportSource','province_id','id');
    }
}
