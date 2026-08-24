<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Reports\Api;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

final class ReportsApiServiceProvider extends ServiceProvider
{
    public function boot(Router $router): void
    {
        $router->middleware(['api', 'auth:sanctum'])->group(function () use ($router): void {
            $router->apiResource('api/v1/reports', GenealogyReportController::class)
                ->parameters(['reports' => 'record']);
        });
    }
}
