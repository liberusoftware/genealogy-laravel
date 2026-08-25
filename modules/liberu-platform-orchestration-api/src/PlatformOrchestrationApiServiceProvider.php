<?php

declare(strict_types=1);

namespace Liberu\Platform\PlatformOrchestration\Api;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

final class PlatformOrchestrationApiServiceProvider extends ServiceProvider
{
    public function boot(Router $router): void
    {
        $router->middleware(['api', 'auth:sanctum'])->group(function () use ($router): void {
            $router->apiResource('api/v1/liberu/platform-orchestration', PlatformWorkflowController::class)
                ->parameters(['platform-orchestration' => 'record']);
        });
    }
}
