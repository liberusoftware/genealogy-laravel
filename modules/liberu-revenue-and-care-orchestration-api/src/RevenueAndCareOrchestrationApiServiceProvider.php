<?php

declare(strict_types=1);

namespace Liberu\Platform\RevenueAndCareOrchestration\Api;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

final class RevenueAndCareOrchestrationApiServiceProvider extends ServiceProvider
{
    public function boot(Router $router): void
    {
        $router->middleware(['api', 'auth:sanctum'])->group(function () use ($router): void {
            $router->apiResource('api/v1/liberu/revenue-and-care-orchestration', CarePlanController::class)
                ->parameters(['revenue-and-care-orchestration' => 'record']);
        });
    }
}
