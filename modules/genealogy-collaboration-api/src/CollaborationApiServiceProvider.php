<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Collaboration\Api;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Liberu\Foundation\ApiAccess\Http\Middleware\ApiContract;
use Liberu\Genealogy\GenealogyCore\Http\Middleware\EstablishTeamContext;

final class CollaborationApiServiceProvider extends ServiceProvider
{
    public function boot(Router $router): void
    {
        $router->middleware(['api', 'auth:sanctum', EstablishTeamContext::class, ApiContract::class])->group(function () use ($router): void {
            $router->apiResource('api/v1/genealogy/collaboration', CollaborationSpaceController::class)
                ->parameters(['collaboration-spaces' => 'record']);
        });
    }
}
