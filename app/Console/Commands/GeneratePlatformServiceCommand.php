<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class GeneratePlatformServiceCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'platform:generate {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'generate file and selectors manually';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $file = file_get_contents(base_path('resources/views/scrapers-scripts/platforms/scraper.stub'));
        $name = $this->argument('name');

        $domain = $this->ask('What is the name domain?');
        $baseDomain = $this->initialDomain($domain);
        $nameSelector = $this->ask('What is the name selector?');
        $priceSelector = $this->ask('What is the price selector?');
        $imageSelector = $this->ask('What is the image selector?');
        $imagesSelector = $this->ask('What is the images selector?');


        $file = self::setVariable('domain', $baseDomain, $file);
        $file = self::setVariable('nameSelector', $nameSelector, $file);
        $file = self::setVariable('priceSelector', $priceSelector, $file);
        $file = self::setVariable('imageSelector', $imageSelector, $file);
        $file = self::setVariable('imagesSelector', $imagesSelector, $file);
        $fileName = Str::kebab($name);
        file_put_contents(base_path('resources/views/scrapers-scripts/platforms/' . $fileName . '.blade.php'), $file);

        // get latest scrapers id
        $id = \App\Models\Platform::max('id') + 1;

        $this->info('add this to PlatformSeeder');
        $this->comment("[
            'id' => {$id},
            'name' => '{$name}',
            'url' => '{$domain}',
            'script_file' => '$fileName',
            'currency' => 'SAR',
            'country' => 'SA',
            'currency_symbol' => 'ر.س',
        ]");
    }

    public static function setVariable($name, $value, $file)
    {

        return str_replace(sprintf('{%s}', $name), $value, $file);
    }

    public function initialDomain($name)
    {
        return str_replace(['http://', 'https://', 'www.'], '', $name);
    }
}
