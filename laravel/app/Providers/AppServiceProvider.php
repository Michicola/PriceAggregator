<?php

namespace App\Providers;

use App\Services\Parsers\PriceParserManager;
use App\Services\Parsers\TOVStrategy;
use App\Services\Parsers\WBStrategy;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
            $this->app->singleton(PriceParserManager::class, function ($app) {
            $manager = new PriceParserManager();
            $manager->registerStrategy('wildberries', new WBStrategy());
            $manager->registerStrategy('21vek', new TOVStrategy());
           
            return $manager;
        });
    }

    public function boot(): void
    {
        //
    }
}
