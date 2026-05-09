<?php

namespace App\Providers;

use App\Models\User;
use App\Services\LlamaIndexService;
use Illuminate\Support\Facades\Gate;
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
                extractionAgentId: config('services.llama_index.extraction_agent_id'),
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Solo Super y Admin pueden ver/editar campos de salario en empleados.
        Gate::define('view-salary', fn (User $user): bool => $user->hasAnyRole(['Super', 'Admin']));
    }
}
