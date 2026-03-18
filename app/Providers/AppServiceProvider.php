<?php

namespace App\Providers;

use App\Services\LlamaIndexService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(LlamaIndexService::class, function (): LlamaIndexService {
            return new LlamaIndexService(
                baseUrl: config('services.llama_index.base_url'),
                apiKey: config('services.llama_index.api_key'),
                configurationId: config('services.llama_index.configuration_id'),
            );
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
