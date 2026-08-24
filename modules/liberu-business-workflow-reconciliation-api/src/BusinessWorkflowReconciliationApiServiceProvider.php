<?php

declare(strict_types=1);

namespace Liberu\Platform\BusinessWorkflowReconciliation\Api;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

final class BusinessWorkflowReconciliationApiServiceProvider extends ServiceProvider
{
    public function boot(Router $router): void
    {
        $router->middleware(['api', 'auth:sanctum'])->group(function () use ($router): void {
            $router->apiResource('api/v1/reconciliation-cases', ReconciliationCaseController::class)
                ->parameters(['reconciliation-cases' => 'record']);
        });
    }
}
