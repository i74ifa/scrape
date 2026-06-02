<?php

namespace App\Providers;

use App\Modules\Massaging\Contracts\MessageSender;
use App\Modules\Massaging\Providers\M365Dialog;
use App\Modules\Massaging\Providers\WaGo;
use App\Services\Currency;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use SocialiteProviders\Google\Provider as GoogleProvider;
use SocialiteProviders\Telegram\Provider as TelegramProvider;
use SocialiteProviders\Manager\SocialiteWasCalled;

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

        $this->app->singleton(Currency::class, function ($app) {
            return new Currency();
        });


        Event::listen(function (SocialiteWasCalled $event) {
            $event->extendSocialite('google', GoogleProvider::class);
            $event->extendSocialite('telegram', TelegramProvider::class);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        URL::forceHttps($this->app->environment('production'));
    }
}
