<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Otp extends Model
{
    protected $fillable = [
        'identifier',
        'token',
        'expires_at',
        'valid',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'valid' => 'boolean',
    ];
}
