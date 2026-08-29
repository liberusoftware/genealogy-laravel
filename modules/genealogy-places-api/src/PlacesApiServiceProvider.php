<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Places\Api;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Liberu\Foundation\ApiAccess\Http\Middleware\ApiContract;
use Liberu\Genealogy\GenealogyCore\Http\Middleware\EstablishTeamContext;

final class PlacesApiServiceProvider extends ServiceProvider
{
    public function boot(Router $router): void
    {
        $router->middleware(['api', 'auth:sanctum', EstablishTeamContext::class, ApiContract::class, 'throttle:60,1'])->group(function () use ($router): void {
            $router->get('api/v1/genealogy/places/hierarchy', [PlaceController::class, 'hierarchy'])->name('genealogy.places.hierarchy');
            $router->get('api/v1/genealogy/places/{record}/names', [PlaceController::class, 'names']);
            $router->post('api/v1/genealogy/places/{record}/names', [PlaceController::class, 'storeName']);
            $router->patch('api/v1/genealogy/places/{record}/names/{name}', [PlaceController::class, 'updateName']);
            $router->delete('api/v1/genealogy/places/{record}/names/{name}', [PlaceController::class, 'destroyName']);
            $router->apiResource('api/v1/genealogy/places', PlaceController::class)
                ->parameters(['places' => 'record']);
        });
    }
}
