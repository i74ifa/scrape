<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CurrencyExchangeRate extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'base_code',
        'code',
        'rate',
        'updated_at',
    ];
}
