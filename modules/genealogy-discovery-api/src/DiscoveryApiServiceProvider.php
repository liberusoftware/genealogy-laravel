<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Discovery\Api;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

final class DiscoveryApiServiceProvider extends ServiceProvider
{
    public function boot(Router $router): void
    {
        $router->middleware(['api', 'auth:sanctum'])->group(function () use ($router): void {
            $router->apiResource('api/v1/discovery-matches', DiscoveryMatchController::class)
                ->parameters(['discovery-matches' => 'record']);
        });
    }
}
