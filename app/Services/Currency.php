<?php


namespace App\Services;

use App\Models\ScraperProduct;
use App\Models\CurrencyExchangeRate;

class Currency
{

    public static $currency = 'YER';
    public static $defaultCurrency = 'SAR';

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
    ];

    /**
     * Converts an amount from one currency to another.
     *
     * Returns a Money value object so the result can be chained, e.g.
     * Currency::convert($amount, 'USD')->format(). The object serializes to its
     * raw numeric amount in JSON and casts to a formatted string when used as a
     * string, so existing callers keep working.
     *
     * @param float|int $amount
     * @param string|null $currencyFrom  Defaults to the stored/default currency (SAR).
     * @param string|null $currencyTo  Defaults to the active (user) currency.
     * @param bool $format  When true, returns the formatted string directly
     *                       (kept for backward compatibility; prefer ->format()).
     * @return Money|string
     */
    public static function convert($amount, $currencyFrom = null, $currencyTo = null, $format = false)
    {
        if ($currencyFrom === null) {
            $currencyFrom = self::$defaultCurrency;
        }

        if ($currencyTo === null) {
            $currencyTo = self::$currency;
        }

        if ($currencyFrom === $currencyTo) {
            $converted = (float) $amount;
        } else {
            $fromRate = self::getExchangeRate($currencyFrom);
            $toRate = self::getExchangeRate($currencyTo);

            // Convert to SAR pivot, then from SAR to the target currency.
            $converted = ($amount * $fromRate) / $toRate;
        }

        if ($format) {
            return self::format($converted, $currencyTo);
        }

        return new Money($converted, $currencyTo);
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
        $amount = round($amount, 2);
        return number_format($amount, 0, '.', ',') . ' ' . self::getCurrencySymbol($currency);
    }

    public static function getCurrencySymbol($currency = 'YER', $start = true)
    {
        $symbol = self::$currencySymbols[$currency] ?? $currency;
        if ($start) {
            return $symbol . ' ';
        }
        return ' ' . $symbol;
    }

    public static function setCurrency($currency)
    {
        self::$currency = $currency;
    }
}
