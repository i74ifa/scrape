<?php

namespace App\Modules;

use Illuminate\Support\Facades\Http;

class GoogleMap
{
    private static function getCityName($apiResponse)
    {
        if (empty($apiResponse['results'])) {
            return "Unknown";
        }

        foreach ($apiResponse['results'] as $result) {
            foreach ($result['address_components'] as $component) {
                if (in_array('administrative_area_level_1', $component['types']) || in_array('locality', $component['types'])) {

                    $originalName = $component['long_name'];

                    $name = str_replace(['محافظة', 'Governorate', 'City', 'District'], '', $originalName);

                    $name = preg_replace('/[^A-Za-z0-9\s]/', '', $name);

                    return trim($name);
                }
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
            'language' => 'en',
            'result_type' => 'administrative_area_level_1|locality'
        ]);

        if ($response->successful()) {
            return self::getCityName($response->json());
        }

        return "";
    }
}
