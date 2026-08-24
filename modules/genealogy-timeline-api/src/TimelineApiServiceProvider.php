<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Timeline\Api;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

final class TimelineApiServiceProvider extends ServiceProvider
{
    public function boot(Router $router): void
    {
        $router->middleware('api')->group(function () use ($router): void {
            $router->get('/api/v1/genealogy-timeline-api', function (): array {
                return ['module' => 'genealogy-timeline', 'surface' => 'api', 'status' => 'available'];
            });
        });
    }
}
