<?php


namespace App\Modules;

use Illuminate\Support\Facades\Http;

class GoogleMap
{
    private static function getStateName($apiResponse)
    {
        if (!isset($apiResponse['results'][0])) {
            return "Unknown";
        }

        $addressComponents = $apiResponse['results'][0]['address_components'];

        foreach ($addressComponents as $component) {
            if (in_array('locality', $component['types'])) {
                return $component['long_name'];
            }
        }

        foreach ($addressComponents as $component) {
            if (in_array('administrative_area_level_1', $component['types'])) {
                return $component['long_name'];
            }
        }

        return null;
    }

    public static function getLocationInfo($lat, $lng)
    {
        $key = config('services.google_map.key');

        $response = Http::get("https://maps.googleapis.com/maps/api/geocode/json", [
            'latlng' => "$lat,$lng",
            'key' => $key,
            'language' => 'en'
        ]);

        if ($response->successful()) {
            $cityName = self::getStateName($response->json());
            return $cityName;
        }

        return "";
    }
}
