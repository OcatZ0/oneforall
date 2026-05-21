<?php

namespace App\Providers;

use App\Services\Implementations\AgentService;
use App\Services\Implementations\OpenSearchService;
use App\Services\Implementations\WazuhService;
use App\Services\Interfaces\IAgentService;
use App\Services\Interfaces\IOpenSearchService;
use App\Services\Interfaces\IWazuhService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(IWazuhService::class, WazuhService::class);
        $this->app->bind(IOpenSearchService::class, OpenSearchService::class);
        $this->app->bind(IAgentService::class, AgentService::class);
    }

    public function boot(): void
    {
        //
    }
}
