<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Dna\Api;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Liberu\Genealogy\GenealogyCore\Http\Middleware\EstablishTeamContext;

final class DnaApiServiceProvider extends ServiceProvider
{
    public function boot(Router $router): void
    {
        $router->middleware(['api', 'auth:sanctum', EstablishTeamContext::class, 'throttle:api'])->group(function () use ($router): void {
            $router->post('api/v1/genealogy/dna/kits/{kit}/consent', [DnaKitController::class, 'consent'])->name('genealogy.dna.consent');
            $router->post('api/v1/genealogy/dna/kits/{kit}/revoke', [DnaKitController::class, 'revoke'])->name('genealogy.dna.revoke');
            $router->apiResource('api/v1/genealogy/dna/kits', DnaKitController::class)->parameters(['kits' => 'record']);
            $router->apiResource('api/v1/genealogy/dna/matches', DnaMatchController::class)->parameters(['matches' => 'record']);
            $router->post('api/v1/genealogy/dna/matches/{record}/segments', [DnaMatchController::class, 'segment'])->name('genealogy.dna.segment');
            $router->apiResource('api/v1/genealogy/dna/groups', DnaGroupController::class)->parameters(['groups' => 'record']);
        });
    }
}
