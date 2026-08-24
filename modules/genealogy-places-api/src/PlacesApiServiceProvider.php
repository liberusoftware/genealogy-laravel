<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Places\Api;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

final class PlacesApiServiceProvider extends ServiceProvider
{
    public function boot(Router $router): void
    {
        $router->middleware(['api', 'auth:sanctum'])->group(function () use ($router): void {
            $router->apiResource('api/v1/places', PlaceController::class)
                ->parameters(['places' => 'record']);
        });
    }
}
