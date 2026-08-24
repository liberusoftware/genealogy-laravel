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
        $router->middleware(['api', 'auth:sanctum', EstablishTeamContext::class])->group(function () use ($router): void {
            $router->get('api/v1/genealogy/tree-viewer/{record}/graph', [TreeViewController::class, 'graph']);
            $router->apiResource('api/v1/genealogy/tree-viewer', TreeViewController::class)
                ->parameters(['tree-viewer' => 'record']);
        });
    }
}
