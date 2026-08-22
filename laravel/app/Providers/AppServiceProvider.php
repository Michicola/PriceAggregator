<?php

namespace App\Providers;

use App\Services\Parsers\PriceParserManager;
use App\Services\Parsers\TOVStrategy;
use App\Services\Parsers\WBStrategy;
use App\Services\Parsers\OzonStrategy;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
           $this->app->singleton(PriceParserManager::class, function ($app) {
            $manager = new PriceParserManager();
            $manager->registerStrategy('wildberries', new WBStrategy());
            $manager->registerStrategy('21vek', new TOVStrategy());
            $manager->registerStrategy('ozon', new OzonStrategy());
    
            return $manager;
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
