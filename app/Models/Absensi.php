<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Absensi extends Model
{

    protected $table = "lp_absensi";

    const STATUS_INACTIVE   = "0";
    const STATUS_ACTIVE     = "1";

    public function getImageAttribute()
    {
        return "http://192.168.100.29/LangitpayAbsensi/storage/Image/" . $this->attributes["image"];
    }

    public function user()
    {
        return $this->belongsTo("App\\Models\\User", "user_id");
    }
}
