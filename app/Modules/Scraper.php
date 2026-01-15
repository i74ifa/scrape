<?php

namespace App\Modules;

use JsonSerializable;
use MatthiasMullie\Minify;
use voku\helper\HtmlDomParser;
use App\Exceptions\Scraper\CurrencyNotSameInScraperException;
use App\Models\Platform;

final class Scraper implements JsonSerializable
{
    protected mixed $data = [];
    public ?string $product_id = null;
    public ?string $name = null;
    public ?string $description = null;
    public ?string $price = null;
    public ?string $original_price = null;
    public ?string $stock = null;
    public ?string $image = null;
    public ?string $review = null;
    public ?string $category = null;
    public ?string $customer_rating = null;
    public ?string $brand = null;
    public ?string $average_rating = null;
    public ?string $total_reviews = null;
    public ?string $sold_by = null;
    public ?string $shipping_price = null;
    public ?string $currency = null;
    public ?array $images = [];
    public ?string $selectedVariant = null;
    public array $variants = [];
    public ?string $condition = null;
    public ?string $auction_end_time = null;
    public ?string $buy_it_now_price = null;
    public ?string $product_url = null;
    public ?string $weight = null;

    public function __construct($url, ?array $data = null, protected Platform $platform)
    {
        foreach ($data as $key => $value) {
            $key = $value['name'];
            if (is_null($value['data'])) {
                continue;
            }
            $this->{$key} = $value['data'];
        }

        $this->original_price = self::toFloat($this->price, $this->platform->currency);

        if ($this->shipping_price) {
            $this->shipping_price = self::toFloat($this->shipping_price, $this->platform->currency);
        }

        if ($this->currency === null) {
            $this->currency = $this->platform->currency;
        }

        $this->price =  $this->original_price;
    }


    public function getCode(): ?string
    {
        return $this->getOnPageStartedFile();
    }

    protected function setConstVariables(string $code): string
    {
        return $code;
    }

    private function getOnPageStartedFile(): ?string
    {
        $nameFile = $this->platform->script_file;

        $html = view('scrapers-scripts.partials.add-to-cart-button')->render();
        $html = view('scrapers-scripts.platforms.' . $nameFile, [
            'html' => $html,
            'className' => $nameFile
        ])->render();
        $htmlParser = new HtmlDomParser($html);
        $javascriptCode = $htmlParser->text();

        if (env('APP_ENV') == 'local') {
            return $javascriptCode;
        }

        return (new MiniFy\JS($javascriptCode))->minify();
    }

    /**
     * Converts a price string to a float value.
     * If the price string is null, returns 0.0.
     * Removes currency symbols, letters, and spaces from the price string.
     * Keeps digits, dot, and comma.
     * If the price string contains comma as thousands separator (e.g., "1,299.99"),
     * removes the comma.
     * If the price string contains comma as decimal separator (e.g., "1299,99"),
     * replaces the comma with a dot.
     * Returns the converted float value.
     *
     * @param string|null $price The price string to convert.
     * @return float The converted float value.
     */
    public static function toFloat(?string $price, ?string $scraperCurrency): float
    {
        if ($price === null) {
            return 0.0;
        }

        $currencyUpper = strtoupper($scraperCurrency);

        // Detect currency from string
        $detectedCurrency = null;

        $currencyMap = [
            '$'  => 'USD',
            '€'  => 'EUR',
            '£'  => 'GBP',
            '₺'  => 'TRY',
            '¥'  => 'JPY',
            '₹'  => 'INR',
            'CHF' => 'CHF',
            'TL' => 'TRY',
            'SA' => 'SAR',
        ];

        foreach ($currencyMap as $symbol => $code) {
            if (str_contains($price, $symbol)) {
                $detectedCurrency = $code;
                break;
            }
        }

        // Throw exception if currency mismatch
        if ($detectedCurrency && $detectedCurrency !== $currencyUpper) {
            throw new CurrencyNotSameInScraperException(
                "Detected currency {$detectedCurrency} does not match {$currencyUpper}",
                $detectedCurrency,
                ''
            );
        }

        // ---- existing logic ----

        $price = preg_replace('/[a-zA-Z]/', '', $price);
        $price = str_replace(array_keys($currencyMap), '', $price);
        $price = str_replace(' ', '', $price);

        $clean = preg_replace('/[^0-9\.,-]/', '', $price);

        $commaDecimalCurrencies = ['EUR', 'TRY', 'BRL', 'ARS', 'DKK', 'NOK', 'SEK', 'PLN', 'CZK', 'HUF'];
        $noDecimalCurrencies = ['JPY', 'KRW', 'IDR', 'VND', 'CLP', 'ISK'];

        $lastDot   = strrpos($clean, '.');
        $lastComma = strrpos($clean, ',');

        if (in_array($currencyUpper, $noDecimalCurrencies)) {
            $clean = str_replace(['.', ','], '', $clean);
        } elseif ($lastDot !== false && $lastComma !== false) {
            if ($lastDot > $lastComma) {
                $clean = str_replace(',', '', $clean);
            } else {
                $clean = str_replace('.', '', $clean);
                $clean = str_replace(',', '.', $clean);
            }
        } elseif ($lastDot !== false || $lastComma !== false) {
            $separator = $lastDot !== false ? '.' : ',';
            $separatorPos = $lastDot !== false ? $lastDot : $lastComma;
            $digitsAfter = strlen($clean) - $separatorPos - 1;

            if (in_array($currencyUpper, $commaDecimalCurrencies)) {
                $clean = $separator === '.'
                    ? str_replace('.', '', $clean)
                    : str_replace(',', '.', $clean);
            } else {
                if ($digitsAfter === 3) {
                    $clean = str_replace($separator, '', $clean);
                } elseif ($separator === ',') {
                    $clean = str_replace(',', '.', $clean);
                }
            }
        }

        return (float) $clean;
    }

    public function jsonSerialize(): mixed
    {
        return [
            'product_id' => $this->product_id,
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'original_price' => $this->original_price,
            'stock' => $this->stock,
            'image' => $this->image,
            'images' => $this->images,
            'customer_rating' => $this->customer_rating,
            'category' => $this->category,
            'brand' => $this->brand,
            'average_rating' => $this->average_rating,
            'total_reviews' => $this->total_reviews,
            'sold_by' => $this->sold_by,
            'shipping_price' => $this->shipping_price,
            'currency' => $this->currency
        ];
    }
}
