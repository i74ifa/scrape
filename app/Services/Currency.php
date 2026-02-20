<?php


namespace App\Services;

use NumberFormatter;
use App\Models\ScraperProduct;
use App\Models\CurrencyExchangeRate;

class Currency
{

    private static $currencySymbols = [
        'USD' => '$',
        'EUR' => '€',
        'GBP' => '£',
        'JPY' => '¥',
        'CNY' => '¥',
        'CAD' => '$',
        'AUD' => '$',
        'CHF' => 'Fr',
        'SEK' => 'kr',
        'NZD' => '$',
        'HKD' => 'HK$',
        'SGD' => 'S$',
        'NOK' => 'kr',
        'MXN' => '$',
        'BRL' => 'R$',
        'ZAR' => 'R',
        'TRY' => '₺',
        'INR' => '₹',
        'RUB' => '₽',
        'AED' => 'د.إ',
        'SAR' => 'ر.س',
        'QAR' => 'ر.ق',
        'KWD' => 'د.ك',
        'BHD' => 'د.ب',
        'OMR' => 'ر.ع.',
        'YER' => 'ر.ي',
        'default' => 'SAR',
    ];

    /**
     * Converts the price of a scraper product to the specified currency.
     *
     * Only supports converting to 'IQD' or 'USD'.
     *
     * @param ScraperProduct $product
     * @param string $currency
     * @return float
     * @throws \Exception
     */
    public static function convert($amount, $currencyFrom, $currencyTo = 'YER')
    {
        if ($currencyFrom === $currencyTo) {
            return $amount;
        }

        $fromRate = self::getExchangeRate($currencyFrom);
        $toRate = self::getExchangeRate($currencyTo);

        // Convert to SAR pivot
        $amountInSar = $amount * $fromRate;

        // Convert from SAR to target
        return $amountInSar / $toRate;
    }

    public static function getExchangeRate($currency = 'YER')
    {
        if ($currency === 'SAR') {
            return 1.0;
        }

        if ($currency === 'YER') {
            return 1.0 / 140.0;
        }

        if ($currency === 'USD') {
            return 3.75;
        }

        $exchangeRate = CurrencyExchangeRate::where('code', $currency)->first();

        // If not found, we assume it might be SAR based on the old "default" logic or just return 1
        if (!$exchangeRate) {
            return 1.0;
        }

        // rate in DB is units per USD (e.g. 1.41 for AUD)
        // 1 unit = (1 / rate) USD
        // 1 USD = 3.75 SAR
        // 1 unit = (3.75 / rate) SAR
        return 3.75 / (float)$exchangeRate->rate;
    }

    public static function format($amount, $currency = 'YER')
    {
        // format pattern
        return number_format($amount, 2, '.', ',') . ' ' . self::getCurrencySymbol($currency);
    }

    public static function getCurrencySymbol($currency = 'YER', $start = true)
    {
        $symbol = self::$currencySymbols[$currency] ?? $currency;
        if ($start) {
            return $symbol . ' ';
        }
        return ' ' . $symbol;
    }
}
