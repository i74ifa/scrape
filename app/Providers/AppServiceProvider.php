<?php

namespace App\Providers;

use App\Exceptions\MessagingException;
use App\Modules\Massaging\Contracts\MessageSender;
use App\Modules\Massaging\Providers\M365Dialog;
use App\Modules\Massaging\Providers\WaGo;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(MessageSender::class, function ($app) {
            $service = config('services.massaging.default');

            return match ($service) {
                'wa-go' => app(WaGo::class),
                'm365-dialog' => app(M365Dialog::class),
                default => app(WaGo::class)
            };
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
