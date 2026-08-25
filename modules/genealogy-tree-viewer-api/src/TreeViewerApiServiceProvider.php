<?php

declare(strict_types=1);

namespace Liberu\Genealogy\TreeViewer\Api;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Liberu\Genealogy\GenealogyCore\Http\Middleware\EstablishTeamContext;

final class TreeViewerApiServiceProvider extends ServiceProvider
{
    public function boot(Router $router): void
    {
        $router->middleware(['api', 'auth:sanctum', EstablishTeamContext::class, 'throttle:60,1'])->group(function () use ($router): void {
            $router->get('api/v1/genealogy/tree-viewer/{record}/graph', [TreeViewApiController::class, 'graph']);
            $router->get('api/v1/genealogy/tree-viewer', [TreeViewApiController::class, 'index']);
            $router->get('api/v1/genealogy/tree-viewer/{record}', [TreeViewApiController::class, 'show']);
            $router->middleware('auth:sanctum')->group(function () use ($router): void {
                $router->post('api/v1/genealogy/tree-viewer', [TreeViewApiController::class, 'store']);
                $router->patch('api/v1/genealogy/tree-viewer/{record}', [TreeViewApiController::class, 'update']);
                $router->delete('api/v1/genealogy/tree-viewer/{record}', [TreeViewApiController::class, 'destroy']);
            });
        });
    }
}
