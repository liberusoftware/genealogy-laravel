<?php

declare(strict_types=1);

namespace Liberu\Genealogy\ImportExport\Api;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Liberu\Genealogy\GenealogyCore\Http\Middleware\EstablishTeamContext;

final class ImportExportApiServiceProvider extends ServiceProvider
{
    public function boot(Router $router): void
    {
        $router->middleware(['api', 'auth:sanctum', EstablishTeamContext::class])->group(function () use ($router): void {
            $router->post('api/v1/genealogy/import-export/preview', [DataTransferController::class, 'preview'])->middleware('throttle:api');
            $router->post('api/v1/genealogy/import-export/import', [DataTransferController::class, 'import'])->middleware('throttle:api');
            $router->get('api/v1/genealogy/import-export/export', [DataTransferController::class, 'export']);
            $router->apiResource('api/v1/genealogy/import-export', DataTransferController::class)
                ->parameters(['import-export' => 'record']);
        });
    }
}
