<?php

declare(strict_types=1);

namespace Liberu\Platform\BusinessWorkflowReconciliation\Api;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

final class BusinessWorkflowReconciliationApiServiceProvider extends ServiceProvider
{
    public function boot(Router $router): void
    {
        $router->middleware('api')->group(function () use ($router): void {
            $router->get('/api/v1/liberu-business-workflow-reconciliation-api', function (): array {
                return ['module' => 'liberu-business-workflow-reconciliation', 'surface' => 'api', 'status' => 'available'];
            });
        });
    }
}
