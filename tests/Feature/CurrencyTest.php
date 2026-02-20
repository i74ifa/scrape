<?php

namespace Tests\Feature;

use App\Services\Currency;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use function PHPUnit\Framework\assertEquals;

class CurrencyTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_can_i_convert_currency(): void
    {
        // YER to SAR: 140 YER = 1 SAR
        $this->assertEquals(1, Currency::convert(140, 'YER', 'SAR'));

        // SAR to YER: 1 SAR = 140 YER
        $this->assertEquals(140, Currency::convert(1, 'SAR', 'YER'));

        // USD to SAR: 1 USD = 3.75 SAR
        $this->assertEquals(3.75, Currency::convert(1, 'USD', 'SAR'));

        // SAR to USD: 3.75 SAR = 1 USD
        $this->assertEquals(1, Currency::convert(3.75, 'SAR', 'USD'));

        // USD to YER: 1 USD = 3.75 * 140 = 525 YER
        $this->assertEquals(525, Currency::convert(1, 'USD', 'YER'));

        $this->assertEquals(100, Currency::convert(100, 'SAR', 'SAR'));
    }
}
