<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $table = "product";

    const STATUS_INACTIVE   = "0";
    const STATUS_ACTIVE     = "1";
}
