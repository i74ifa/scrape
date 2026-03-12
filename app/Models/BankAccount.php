<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BankAccount extends Model
{
    protected $fillable = [
        'name',
        'account_name',
        'number',
        'iban',
        'logo',
        'is_active',
        'instructions'
    ];
}
