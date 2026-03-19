<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property string $code
 * @property numeric $delivery_cost
 * @property int $is_supported
 * @method static \Illuminate\Database\Eloquent\Builder<static>|State newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|State newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|State query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|State whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|State whereDeliveryCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|State whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|State whereIsSupported($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|State whereName($value)
 * @mixin \Eloquent
 */
class State extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'name',
        'code',
        'delivery_cost',
    ];
}
