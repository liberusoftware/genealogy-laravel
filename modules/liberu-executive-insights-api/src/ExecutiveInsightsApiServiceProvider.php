<?php

declare(strict_types=1);

namespace Liberu\Platform\ExecutiveInsights\Api;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

final class ExecutiveInsightsApiServiceProvider extends ServiceProvider
{
    public function boot(Router $router): void
    {
        $router->middleware(['api', 'auth:sanctum'])->group(function () use ($router): void {
            $router->apiResource('api/v1/insight-snapshots', InsightSnapshotController::class)
                ->parameters(['insight-snapshots' => 'record']);
        });
    }
}
