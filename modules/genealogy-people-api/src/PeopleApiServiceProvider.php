<?php

declare(strict_types=1);

namespace Liberu\Genealogy\People\Api;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Liberu\Genealogy\GenealogyCore\Http\Middleware\EstablishTeamContext;

final class PeopleApiServiceProvider extends ServiceProvider
{
    public function boot(Router $router): void
    {
        $router->middleware(['api', 'auth:sanctum', EstablishTeamContext::class])->group(function () use ($router): void {
            $router->apiResource('api/v1/genealogy/people', PersonController::class)
                ->only(['index', 'store', 'show', 'update', 'destroy']);
        });
    }
}
