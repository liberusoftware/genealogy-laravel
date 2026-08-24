<?php

declare(strict_types=1);

namespace Liberu\Platform\PlatformOrchestration\Api;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

final class PlatformOrchestrationApiServiceProvider extends ServiceProvider
{
    public function boot(Router $router): void
    {
        $router->middleware('api')->group(function () use ($router): void {
            $router->get('/api/v1/liberu-platform-orchestration-api', function (): array {
                return ['module' => 'liberu-platform-orchestration', 'surface' => 'api', 'status' => 'available'];
            });
        });
    }
}
