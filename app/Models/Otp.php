<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $identifier
 * @property string $token
 * @property \Illuminate\Support\Carbon|null $expires_at
 * @property bool $valid
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Otp newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Otp newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Otp query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Otp whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Otp whereExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Otp whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Otp whereIdentifier($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Otp whereToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Otp whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Otp whereValid($value)
 * @mixin \Eloquent
 */
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
