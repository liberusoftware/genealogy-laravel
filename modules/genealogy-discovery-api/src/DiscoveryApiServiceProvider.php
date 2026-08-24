<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Discovery\Api;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

final class DiscoveryApiServiceProvider extends ServiceProvider
{
    public function boot(Router $router): void
    {
        $router->middleware('api')->group(function () use ($router): void {
            $router->get('/api/v1/genealogy-discovery-api', function (): array {
                return ['module' => 'genealogy-discovery', 'surface' => 'api', 'status' => 'available'];
            });
        });
    }
}
