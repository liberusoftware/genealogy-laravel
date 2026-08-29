<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Relationships\Api;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Liberu\Foundation\ApiAccess\Http\Middleware\ApiContract;
use Liberu\Genealogy\GenealogyCore\Http\Middleware\EstablishTeamContext;

final class RelationshipsApiServiceProvider extends ServiceProvider
{
    public function boot(Router $router): void
    {
        $router->middleware(['api', 'auth:sanctum', EstablishTeamContext::class, ApiContract::class, 'throttle:60,1'])->group(function () use ($router): void {
            $router->post('api/v1/genealogy/relationships/validate', [RelationshipController::class, 'validateGraph'])
                ->name('genealogy.relationships.validate');
            $router->apiResource('api/v1/genealogy/relationships', RelationshipController::class)
                ->parameters(['relationships' => 'record']);
        });
    }
}
