<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Places\Api;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

final class PlacesApiServiceProvider extends ServiceProvider
{
    public function boot(Router $router): void
    {
        $router->middleware('api')->group(function () use ($router): void {
            $router->get('/api/v1/genealogy-places-api', function (): array {
                return ['module' => 'genealogy-places', 'surface' => 'api', 'status' => 'available'];
            });
        });
    }
}
