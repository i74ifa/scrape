<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property string $account_name
 * @property string $number
 * @property string|null $iban
 * @property string|null $logo
 * @property int $is_active
 * @property string|null $instructions
 * @property string|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BankAccount newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BankAccount newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BankAccount query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BankAccount whereAccountName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BankAccount whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BankAccount whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BankAccount whereIban($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BankAccount whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BankAccount whereInstructions($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BankAccount whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BankAccount whereLogo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BankAccount whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BankAccount whereNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BankAccount whereUpdatedAt($value)
 * @mixin \Eloquent
 */
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
