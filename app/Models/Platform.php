<?php

namespace App\Models;

use App\Modules\Scraper as ScraperModule;
use Illuminate\Database\Eloquent\Model;

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
        return (new ScraperModule(null, [], $this))->getCode();
    }
}
