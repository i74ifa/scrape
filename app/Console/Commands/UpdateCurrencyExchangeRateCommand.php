<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class UpdateCurrencyExchangeRateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'currency:exchange-rate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update currency exchange rate';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $url = 'https://api.frankfurter.dev/v1/latest?base=USD';

        $response = Http::get($url);
        $data = $response->json();

        // delete all exchange rates
        \App\Models\CurrencyExchangeRate::truncate();
        foreach ($data['rates'] as $currency => $rate) {
            $exchangeRate = new \App\Models\CurrencyExchangeRate();
            $exchangeRate->base_code = 'USD';
            $exchangeRate->code = $currency;
            $exchangeRate->rate = $rate;
            $exchangeRate->updated_at = now()->format('Y-m-d');
            $exchangeRate->save();
        }

        // not supported currencies
        $currencies = [
            [
                'base_code' => 'USD',
                'code' => 'SAR',
                'exchange_rate' => 3.75,
            ],
            [
                'base_code' => 'USD',
                'code' => 'AED',
                'exchange_rate' => 3.67,
            ],
            [
                'base_code' => 'USD',
                'code' => 'YER',
                'exchange_rate' => 530,
            ]
        ];

        foreach ($currencies as $currency) {
            $exchangeRate = new \App\Models\CurrencyExchangeRate();
            $exchangeRate->base_code = $currency['base_code'];
            $exchangeRate->code = $currency['code'];
            $exchangeRate->rate = $currency['exchange_rate'];
            $exchangeRate->updated_at = now()->format('Y-m-d');
            $exchangeRate->save();
        }

        $this->info('✅ Exchange rate updated successfully');
    }
}
