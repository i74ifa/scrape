<?php

namespace App;

class RegexCode
{
    public const IRAQ_NUMBERS = '/^(\+964\s*0?|\+9640?|0?)?\s*7\s*(0|1|2|3|4|5|7|8|9)(?:\s*\d){8}$/';

    public const YEMEN_NUMBERS = '/^(\+967\s*0?|\+9670?|0?)?\s*7\s*(0|1|2|3|4|5|7|8|9)(?:\s*\d){7}$/';

    public static function getCountryRegexUsingCode(string $code): ?string
    {
        switch ($code) {
            case '964':
                return self::IRAQ_NUMBERS;
            case '967':
                return self::YEMEN_NUMBERS;
            default:
                return null;
        }
    }

    public static function getPhoneCountryCode(?string $countryCode = null): string
    {
        if (!$countryCode || strlen($countryCode) < 2) {
            $countryCode = '967';
        }

        return str_replace('+', '', $countryCode);
    }
}
