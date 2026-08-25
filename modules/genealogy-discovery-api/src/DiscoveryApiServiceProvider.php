<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Discovery\Api;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Liberu\Foundation\ApiAccess\Http\Middleware\ApiContract;
use Liberu\Genealogy\GenealogyCore\Http\Middleware\EstablishTeamContext;

final class DiscoveryApiServiceProvider extends ServiceProvider
{
    public function boot(Router $router): void
    {
        $router->middleware(['api', 'auth:sanctum', EstablishTeamContext::class, ApiContract::class, 'throttle:60,1'])->group(function () use ($router): void {
            $router->post('api/v1/genealogy/discovery/external-search', [DiscoveryMatchController::class, 'externalSearch'])->name('genealogy.discovery.external-search');
            $router->get('api/v1/genealogy/discovery/search', [DiscoveryMatchController::class, 'search'])->name('genealogy.discovery.search');
            $router->get('api/v1/genealogy/discovery/duplicates', [DiscoveryMatchController::class, 'duplicates'])->name('genealogy.discovery.duplicates');
            $router->post('api/v1/genealogy/discovery/duplicates/scan', [DiscoveryMatchController::class, 'scanDuplicates'])->name('genealogy.discovery.duplicates.scan');
            $router->get('api/v1/genealogy/discovery/paths/{from}/{to}', [DiscoveryMatchController::class, 'path'])->name('genealogy.discovery.path');
            $router->post('api/v1/genealogy/discovery/{record}/review', [DiscoveryMatchController::class, 'review'])->name('genealogy.discovery.review');
            $router->apiResource('api/v1/genealogy/discovery', DiscoveryMatchController::class)->parameters(['discovery' => 'record']);
        });
    }
}
