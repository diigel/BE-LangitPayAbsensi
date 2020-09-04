<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Absensi extends Model
{

    protected $table = "lp_user";

    const STATUS_INACTIVE   = "0";
    const STATUS_ACTIVE     = "1";

    // public function getImageAttribute()
    // {
    //     return "/Applications/XAMPP/xamppfiles/htdocs/LangitpayAbsensi/public" . $this->attributes["image"];
    // }

    public function user()
    {
        return $this->belongsTo("App\\Models\\User", "name");
    }
}
