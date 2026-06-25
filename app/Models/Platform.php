<?php

namespace App\Models;

use App\Modules\Scraper as ScraperModule;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property string $url
 * @property string $logo
 * @property string $currency
 * @property string $currency_symbol
 * @property string $country
 * @property string $script_file
 * @property int $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Platform newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Platform newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Platform query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Platform whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Platform whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Platform whereCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Platform whereCurrencySymbol($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Platform whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Platform whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Platform whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Platform whereLogo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Platform whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Platform whereScriptFile($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Platform whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Platform whereUrl($value)
 * @mixin \Eloquent
 */
class Platform extends Model
{
    protected $fillable = [
        'name',
        'description',
        'url',
        'logo',
        'currency',
        'currency_symbol',
        'country',
        'script_file',
        'sis_active',
    ];

    public function getCode()
    {
        return $this->scraping()->getCode();
    }

    public function scopeActive()
    {
        return $this->whereIsActive(1);
    }

    public function scraping($data = [])
    {
        return new ScraperModule($data, $this);
    }
}
