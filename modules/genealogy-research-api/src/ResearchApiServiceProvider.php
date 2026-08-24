<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Research\Api;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Liberu\Genealogy\GenealogyCore\Http\Middleware\EstablishTeamContext;

final class ResearchApiServiceProvider extends ServiceProvider
{
    public function boot(Router $router): void
    {
        $router->middleware(['api', 'auth:sanctum', EstablishTeamContext::class])->group(function () use ($router): void {
            $router->apiResource('api/v1/genealogy/research/{project}/entries', ResearchEntryController::class)
                ->parameters(['entries' => 'entry']);
            $router->apiResource('api/v1/genealogy/research', ResearchProjectController::class)
                ->parameters(['research-projects' => 'record']);
        });
    }
}
