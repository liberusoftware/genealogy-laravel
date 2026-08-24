<?php

declare(strict_types=1);

namespace Liberu\Platform\RevenueAndCareOrchestration\Api;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

final class RevenueAndCareOrchestrationApiServiceProvider extends ServiceProvider
{
    public function boot(Router $router): void
    {
        $router->middleware('api')->group(function () use ($router): void {
            $router->get('/api/v1/liberu-revenue-and-care-orchestration-api', function (): array {
                return ['module' => 'liberu-revenue-and-care-orchestration', 'surface' => 'api', 'status' => 'available'];
            });
        });
    }
}
