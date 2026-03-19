<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Spatie\Geocoder\Geocoder;
use Tests\TestCase;

class GeoToCityTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_example(): void
    {
        $city = $this->getCityFromCoords(13.966970, 44.169159);
        dd($city);
    }

    public static function getCityFromCoords($lat, $lng)
    {
        $client = new \GuzzleHttp\Client();

        $geocoder = new Geocoder($client);

        $result = $geocoder->getAddressForCoordinates($lat, $lng);

        $city = collect($result['address_components'])
            ->filter(fn($component) => in_array('locality', $component['types']))
            ->first()['long_name'] ?? 'Unknown City';

        return $city;
    }
}
