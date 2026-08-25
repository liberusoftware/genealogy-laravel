<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Discovery\Api;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Liberu\Genealogy\GenealogyCore\Http\Middleware\EstablishTeamContext;

final class DiscoveryApiServiceProvider extends ServiceProvider
{
    public function boot(Router $router): void
    {
        $router->middleware(['api', 'auth:sanctum', EstablishTeamContext::class, 'throttle:api'])->group(function () use ($router): void {
            $router->get('api/v1/genealogy/discovery/search', [DiscoveryMatchController::class, 'search'])->name('genealogy.discovery.search');
            $router->get('api/v1/genealogy/discovery/duplicates', [DiscoveryMatchController::class, 'duplicates'])->name('genealogy.discovery.duplicates');
            $router->get('api/v1/genealogy/discovery/paths/{from}/{to}', [DiscoveryMatchController::class, 'path'])->name('genealogy.discovery.path');
            $router->apiResource('api/v1/genealogy/discovery', DiscoveryMatchController::class)->parameters(['discovery' => 'record']);
        });
    }
}
