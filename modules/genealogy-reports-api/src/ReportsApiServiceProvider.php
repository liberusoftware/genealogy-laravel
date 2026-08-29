<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Reports\Api;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Liberu\Foundation\ApiAccess\Http\Middleware\ApiContract;
use Liberu\Genealogy\GenealogyCore\Http\Middleware\EstablishTeamContext;

final class ReportsApiServiceProvider extends ServiceProvider
{
    public function boot(Router $router): void
    {
        $router->middleware(['api', 'auth:sanctum', EstablishTeamContext::class, ApiContract::class, 'throttle:60,1'])->group(function () use ($router): void {
            $router->post('api/v1/genealogy/reports/{record}/generate', [GenealogyReportController::class, 'generate'])
                ->name('genealogy.reports.generate');
            $router->apiResource('api/v1/genealogy/reports', GenealogyReportController::class)
                ->parameters(['reports' => 'record']);
        });
    }
}
