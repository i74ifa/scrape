<?php

namespace App\Exceptions\Scraper;

use Exception;

class CurrencyNotSameInScraperException extends Exception
{
    /**
     * @var string|null
     */
    protected $detectedCurrency;

    /**
     * @var string|null
     */
    protected $scraperCurrency;

    public function __construct(string $message = "", ?string $detectedCurrency = null, ?string $scraperCurrency = null)
    {
        parent::__construct($message);
        $this->detectedCurrency = $detectedCurrency;
        $this->scraperCurrency = $scraperCurrency;
    }

    public function getDetectedCurrency(): ?string
    {
        return $this->detectedCurrency;
    }
}
