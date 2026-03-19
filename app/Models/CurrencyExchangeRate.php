<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $base_code
 * @property string $code
 * @property string $rate
 * @property string|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CurrencyExchangeRate newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CurrencyExchangeRate newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CurrencyExchangeRate query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CurrencyExchangeRate whereBaseCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CurrencyExchangeRate whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CurrencyExchangeRate whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CurrencyExchangeRate whereRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CurrencyExchangeRate whereUpdatedAt($value)
 * @mixin \Eloquent
 */
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
