<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Timeline\Api;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

final class TimelineApiServiceProvider extends ServiceProvider
{
    public function boot(Router $router): void
    {
        $router->middleware(['api', 'auth:sanctum'])->group(function () use ($router): void {
            $router->apiResource('api/v1/timeline-events', TimelineEventController::class)
                ->parameters(['timeline-events' => 'record']);
        });
    }
}
