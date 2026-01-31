<?php


namespace App\Services;

use NumberFormatter;
use App\Models\ScraperProduct;
use App\Models\CurrencyExchangeRate;

class Currency
{

    private static $currencySymbols = [
        'YER' => 'ر.ي',
        'USD' => '$',
        'EUR' => '€',
        'GBP' => '£',
        'TRY' => 'TL'
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
    public static function convert($amount, $productCurrency, $currency = 'YER')
    {
        if (!in_array($currency, ['YER', 'SAR'])) {
            throw new \Exception('Currency not supported');
        }

        if ($productCurrency === 'SAR') {
            return $amount;
        }

        $rateYer = 530;
        $rate = self::getExchangeRate($productCurrency);

        if ($currency === $productCurrency) {
            $amount = $amount / $rateYer;
        }

        $amount = $amount * $rate;
        if ($currency === 'SAR') {
            return $amount;
        }

        return $amount * $rateYer;
    }

    public static function getExchangeRate($currency = 'YER')
    {
        $rate = 1;

        if ($currency == 'YER') {
            $rate = 530;
        } else {
            $exchangeRate = CurrencyExchangeRate::where('code', $currency)->first();
            $rate = $exchangeRate->rate ?? 1;
            $rate = 1 / $rate;
        }

        return $rate;
    }

    public static function format($amount, $currency = 'YER')
    {
        $currencySymbol = self::$currencySymbols[$currency] ?? $currency;

        // format pattern
        return number_format($amount, 0, '.', ',') . ' ' . $currencySymbol;
    }
}
