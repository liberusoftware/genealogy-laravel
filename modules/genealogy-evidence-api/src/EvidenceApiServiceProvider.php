<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Evidence\Api;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Liberu\Foundation\ApiAccess\Http\Middleware\ApiContract;
use Liberu\Genealogy\GenealogyCore\Http\Middleware\EstablishTeamContext;

final class EvidenceApiServiceProvider extends ServiceProvider
{
    public function boot(Router $router): void
    {
        $router->middleware(['api', 'auth:sanctum', EstablishTeamContext::class, ApiContract::class, 'throttle:60,1'])->group(function () use ($router): void {
            $router->get('api/v1/genealogy/evidence/{entity}', [EvidenceEntityController::class, 'index']);
            $router->post('api/v1/genealogy/evidence/{entity}', [EvidenceEntityController::class, 'store']);
            $router->get('api/v1/genealogy/evidence/{entity}/{record}', [EvidenceEntityController::class, 'show']);
            $router->match(['put', 'patch'], 'api/v1/genealogy/evidence/{entity}/{record}', [EvidenceEntityController::class, 'update']);
            $router->delete('api/v1/genealogy/evidence/{entity}/{record}', [EvidenceEntityController::class, 'destroy']);
            $router->post('api/v1/genealogy/evidence/{record}/review', [EvidenceRecordController::class, 'review'])
                ->name('genealogy.evidence.review');
            $router->post('api/v1/genealogy/evidence/{record}/archive', [EvidenceRecordController::class, 'archive'])
                ->name('genealogy.evidence.archive');
            $router->apiResource('api/v1/genealogy/evidence', EvidenceRecordController::class)
                ->parameters(['evidence' => 'record']);
        });
    }
}
