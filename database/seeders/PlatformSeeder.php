<?php

namespace Database\Seeders;

use App\Modules\LogoDev;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PlatformSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            [
                'id' => 1,
                'name' => 'Amazon',
                'url' => 'https://www.amazon.sa',
                'script_file' => 'amazon',
                'currency' => 'SAR',
                'currency_symbol' => 'ر.س',
                'country' => 'SA',
            ],
            [
                'id' => 2,
                'name' => 'Ali Express',
                'url' => 'https://ar.aliexpress.com?_randl_shipto=SA&gatewayAdaptuae',
                'script_file' => 'ali_express',
                'currency' => 'SAR',
                'currency_symbol' => 'ر.س',
                'country' => 'SA',
            ],
            [
                'id' => 3,
                'name' => 'Trendyol',
                'url' => 'https://www.trendyol.com/ar',
                'script_file' => 'trendyol',
                'currency' => 'SAR',
                'currency_symbol' => 'ر.س',
                'country' => 'SA',
            ],
            [
                'id' => 4,
                'name' => 'Ebay',
                'url' => 'https://www.ebay.com',
                'script_file' => 'ebay',
                'currency' => 'USD',
                'currency_symbol' => '$',
                'country' => 'US',
            ],
            [
                'id' => 5,
                'name' => 'Shein',
                'url' => 'https://ar.shein.com',
                'script_file' => 'shein',
                'currency' => 'SAR',
                'currency_symbol' => 'ر.س',
                'country' => 'SA',
            ],
            [
                'id' => 6,
                'name' => 'Zara',
                'url' => 'https://www.zara.com/sa/ar',
                'script_file' => 'zara',
                'currency' => 'SAR',
                'currency_symbol' => 'ر.س',
                'country' => 'SA',
            ],
            [
                'id' => 7,
                'name' => 'Sephora',
                'url' => 'https://www.sephora.me/sa-ar',
                'script_file' => 'sephora',
                'currency' => 'SAR',
                'currency_symbol' => 'ر.س',
                'country' => 'SA',
            ],
            [
                'id' => 8,
                'name' => 'Gissah SA',
                'url' => 'https://sa.gissah.com/ar',
                'script_file' => 'gissah',
                'currency' => 'SAR',
                'currency_symbol' => 'ر.س',
                'country' => 'SA',
                'logo' => 'https://gissah.com/web/image/website/2/logo/Kuwait?unique=31ed69a',
            ],
            [
                'id' => 9,
                'name' => 'Puma SA',
                'url' => 'https://sa.puma.com',
                'script_file' => 'puma',
                'currency' => 'SAR',
                'currency_symbol' => 'ر.س',
                'country' => 'SA',
            ],
            [
                'id' => 10,
                'name' => 'Nike SA',
                'url' => 'https://www.nike.sa/ar/home',
                'script_file' => 'nike',
                'currency' => 'SAR',
                'currency_symbol' => 'ر.س',
                'country' => 'SA',
            ],
            [
                'id' => 11,
                'name' => 'Adidas SA',
                'url' => 'https://www.adidas.sa/ar',
                'script_file' => 'adidas',
                'currency' => 'SAR',
                'currency_symbol' => 'ر.س',
                'country' => 'SA',
            ],
            [
                'id' => 12,
                'name' => 'Fashion Nova',
                'url' => 'https://www.fashionnova.com',
                'script_file' => 'fashion-nova',
                'currency' => 'USD',
                'currency_symbol' => '$',
                'country' => 'US',
            ],
            [
                'id' => 13,
                'name' => 'Light In The Box',
                'url' => 'https://www.lightinthebox.com',
                'script_file' => 'light-in-the-box',
                'currency' => 'USD',
                'currency_symbol' => '$',
                'country' => 'US',
            ],
            [
                'id' => 14,
                'name' => 'Flo',
                'url' => 'https://www.flo.com.tr',
                'script_file' => 'flo',
                'currency' => 'TRY',
                'currency_symbol' => '₺',
                'country' => 'TR',
            ],
            [
                'id' => 15,
                'name' => 'Noon SA',
                'url' => 'https://www.noon.com/saudi-ar',
                'script_file' => 'noon',
                'currency' => 'SAR',
                'currency_symbol' => 'ر.س',
                'country' => 'SA',
            ]
        ];

        foreach ($data as $scraper) {

            // check if exists by name
            if (DB::table('platforms')->where('id', $scraper['id'])->exists()) {
                continue;
            }

            if (!isset($scraper['logo'])) {
                $imageData = LogoDev::make($scraper['name'])->getLogoFileContent();
                $scraper['logo'] = $this->uploadBase64ToS3($imageData);
            } else {
                $scraper['logo'] = $this->uploadBase64ToS3(LogoDev::getFileContents($scraper['logo']));
            }


            DB::table('platforms')->insert($scraper);
        }
    }

    function uploadBase64ToS3(string $imageData, string $path = 'uploads/logo'): string
    {
        // Get MIME type
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->buffer($imageData);

        // Determine extension
        $extensions = [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/gif'  => 'gif',
            'image/webp' => 'webp',
            'image/bmp'  => 'bmp',
        ];
        $extension = $extensions[$mimeType] ?? 'png';

        $fileName = $path . '/' . Str::uuid() . '.' . $extension;

        /** @var \Illuminate\Contracts\Filesystem\Cloud $disk */
        $disk = Storage::put($fileName, $imageData, 'public');
        if (!$disk) {
            throw new \Exception('Failed to upload logo');
        }

        return $fileName;
    }
}
