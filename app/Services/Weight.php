<?php

namespace App\Services;

use Illuminate\Support\Facades\App;

class Weight
{
    private float $grams;

    // Conversion constants
    private const CONVERSIONS = [
        'mg' => 0.001,
        'milligram' => 0.001,
        'milligrams' => 0.001,
        'g' => 1,
        'gram' => 1,
        'grams' => 1,
        'kg' => 1000,
        'kilo' => 1000,
        'kilogram' => 1000,
        'kilograms' => 1000,
        'oz' => 28.349523125,
        'ounce' => 28.349523125,
        'ounces' => 28.349523125,
        'lb' => 453.59237,
        'lbs' => 453.59237,
        'pound' => 453.59237,
        'pounds' => 453.59237,
        't' => 1000000,
        'ton' => 1000000,
        'tons' => 1000000,
        'tonne' => 1000000,
        'tonnes' => 1000000,
        'mt' => 1000000,
    ];

    private function __construct(float $grams)
    {
        $this->grams = $grams;
    }

    /**
     * Parse weight from string
     * 
     * @param string $input Raw weight string (e.g., "100g", "1.5 kg", "weight: 10 pounds")
     * @param string|null $outputUnit Desired output unit (null returns Weight object)
     * @return mixed Weight object, float value, or formatted string
     */
    public static function parse(string $input, ?string $outputUnit = null)
    {
        // Remove extra whitespace and convert to lowercase for processing
        $normalized = preg_replace('/\s+/', ' ', trim($input));
        $lowerInput = strtolower($normalized);

        // Pattern to match number (integer or decimal) followed by optional unit
        // Handles: "100g", "100 g", "100.5kg", "1,000 grams", "2.5 pounds"
        $pattern = '/(\d+(?:[,\.]\d+)*)\s*([a-z]+)?/i';

        preg_match_all($pattern, $lowerInput, $matches, PREG_SET_ORDER);

        if (empty($matches)) {
            return null;
        }

        $totalGrams = 0;
        $foundWeight = false;

        foreach ($matches as $match) {
            $number = $match[1];
            $unit = $match[2] ?? '';

            // Convert number format (handle both comma and period as decimal separator)
            // Remove thousands separators
            $number = str_replace(',', '.', $number);
            $number = preg_replace('/\.(?=.*\.)/', '', $number); // Remove all but last period
            $value = floatval($number);

            if ($value <= 0) {
                continue;
            }

            // If no unit specified, try to infer or default to grams
            if (empty($unit)) {
                // Check if previous or next word might be a unit
                $unitFound = self::findUnitInContext($lowerInput, $match[0]);
                $unit = $unitFound ?: 'g';
            }

            // Find matching unit
            $conversionFactor = self::findConversionFactor($unit);

            if ($conversionFactor !== null) {
                $totalGrams += $value * $conversionFactor;
                $foundWeight = true;
            }
        }

        if (!$foundWeight || $totalGrams <= 0) {
            return null;
        }

        $weight = new self($totalGrams);

        // If output unit specified, convert and return
        if ($outputUnit !== null) {
            return $weight->to($outputUnit);
        }

        return $weight;
    }

    /**
     * Find conversion factor for a unit
     */
    private static function findConversionFactor(string $unit): ?float
    {
        $unit = strtolower(trim($unit));

        // Direct match
        if (isset(self::CONVERSIONS[$unit])) {
            return self::CONVERSIONS[$unit];
        }

        // Try to find partial match (for typos or variations)
        foreach (self::CONVERSIONS as $key => $factor) {
            if (strpos($key, $unit) === 0 || strpos($unit, $key) === 0) {
                return $factor;
            }
        }

        return null;
    }

    /**
     * Try to find unit in surrounding context
     */
    private static function findUnitInContext(string $input, string $numberPart): ?string
    {
        $position = strpos($input, strtolower($numberPart));
        if ($position === false) {
            return null;
        }

        // Look ahead and behind for unit words
        $contextLength = 20;
        $start = max(0, $position - $contextLength);
        $end = min(strlen($input), $position + strlen($numberPart) + $contextLength);
        $context = substr($input, $start, $end - $start);

        foreach (self::CONVERSIONS as $unit => $factor) {
            if (strpos($context, $unit) !== false) {
                return $unit;
            }
        }

        return null;
    }

    /**
     * Convert to specified unit
     */
    public function to(string $unit, int $decimals = 2, bool $withUnit = true)
    {
        $originalUnit = $unit;
        $unit = strtolower(trim($unit));
        $conversionFactor = self::findConversionFactor($unit);

        if ($conversionFactor === null) {
            return null;
        }

        $value = $this->grams / $conversionFactor;
        $rounded = round($value, $decimals);

        if ($withUnit) {
            return $rounded . $originalUnit;
        }

        return $rounded;
    }

    /**
     * Get weight in grams
     */
    public function toGrams(int $decimals = 2): float
    {
        return round($this->grams, $decimals);
    }

    /**
     * Get weight in kilograms
     */
    public function toKilograms(int $decimals = 3): float
    {
        return round($this->grams / 1000, $decimals);
    }

    /**
     * Get weight in pounds
     */
    public function toPounds(int $decimals = 2): float
    {
        return round($this->grams / 453.59237, $decimals);
    }

    /**
     * Get weight in ounces
     */
    public function toOunces(int $decimals = 2): float
    {
        return round($this->grams / 28.349523125, $decimals);
    }

    public static function format(int|float $weight): string
    {
        $locale = App::getLocale();

        $symbols = (object)[
            'gram' => 'g',
            'kg' => 'kg',
        ];
        if ($locale === 'sa') {
            $symbols = (object)[
                'gram' => 'غ',
                'kg' => 'ك.غ',
            ];
        }

        if ($weight >= 1000) {
            $value = $weight / 1000;
            return rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.') . $symbols->kg;
        }

        return $weight . $symbols->gram;
    }

    /**
     * String representation
     */
    public function __toString(): string
    {
        return $this->toGrams() . 'g';
    }
}
