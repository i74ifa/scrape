<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    //
    protected $fillable = [
        'address_one',
        'phone',
        'latitude',
        'longitude',
        'user_id',
    ];
}
