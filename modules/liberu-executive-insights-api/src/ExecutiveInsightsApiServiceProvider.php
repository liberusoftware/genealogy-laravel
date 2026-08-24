<?php

declare(strict_types=1);

namespace Liberu\Platform\ExecutiveInsights\Api;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

final class ExecutiveInsightsApiServiceProvider extends ServiceProvider
{
    public function boot(Router $router): void
    {
        $router->middleware('api')->group(function () use ($router): void {
            $router->get('/api/v1/liberu-executive-insights-api', function (): array {
                return ['module' => 'liberu-executive-insights', 'surface' => 'api', 'status' => 'available'];
            });
        });
    }
}
