<?php

declare(strict_types=1);

namespace Liberu\Genealogy\People\Api;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Liberu\Foundation\ApiAccess\Http\Middleware\ApiContract;
use Liberu\Genealogy\GenealogyCore\Http\Middleware\EstablishTeamContext;

final class PeopleApiServiceProvider extends ServiceProvider
{
    public function boot(Router $router): void
    {
        $router->middleware(['api', 'auth:sanctum', EstablishTeamContext::class, ApiContract::class, 'throttle:60,1'])->group(function () use ($router): void {
            $router->post('api/v1/genealogy/people/{person}/merge-candidates/{candidate}/review', [PersonController::class, 'reviewMergeCandidate'])
                ->name('genealogy.people.merge-candidates.review');
            $router->match(['put', 'patch'], 'api/v1/genealogy/people/{person}/attributes', [PersonController::class, 'updateAttributes'])
                ->name('genealogy.people.attributes.update');
            $router->delete('api/v1/genealogy/people/{person}/attributes/{attribute}', [PersonController::class, 'removeAttribute'])
                ->name('genealogy.people.attributes.delete');
            $router->patch('api/v1/genealogy/people/{person}/life-status', [PersonController::class, 'setLifeStatus'])
                ->name('genealogy.people.life-status.update');
            $router->get('api/v1/genealogy/people/{person}/{supporting}', [PersonSupportingRecordController::class, 'index']);
            $router->post('api/v1/genealogy/people/{person}/{supporting}', [PersonSupportingRecordController::class, 'store']);
            $router->match(['put', 'patch'], 'api/v1/genealogy/people/{person}/{supporting}/{record}', [PersonSupportingRecordController::class, 'update']);
            $router->delete('api/v1/genealogy/people/{person}/{supporting}/{record}', [PersonSupportingRecordController::class, 'destroy']);
            $router->apiResource('api/v1/genealogy/people', PersonController::class)
                ->only(['index', 'store', 'show', 'update', 'destroy']);
        });
    }
}
