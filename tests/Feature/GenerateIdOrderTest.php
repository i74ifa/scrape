<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Order;

class GenerateIdOrderTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_example(): void
    {
        $codes = [];
        $duplicates = 0;

        $prefix = [
            '001',
            '002',
            '003'
        ];

        foreach ($prefix as $p) {
            for ($i = 0; $i < 10000; $i++) {
                $code = Order::generateIdentifier($p);

                if (isset($codes[$code])) {
                    $duplicates++;
                    echo "Duplicate found: $code\n";
                } else {
                    $codes[$code] = true;
                }
            }
        }

        echo "Total duplicates: $duplicates\n";
        echo "Total unique codes: " . count($codes) . "\n";
    }
}
