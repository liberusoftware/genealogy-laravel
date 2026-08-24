<?php

declare(strict_types=1);

namespace Liberu\Genealogy\ImportExport\Api;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

final class ImportExportApiServiceProvider extends ServiceProvider
{
    public function boot(Router $router): void
    {
        $router->middleware(['api', 'auth:sanctum'])->group(function () use ($router): void {
            $router->apiResource('api/v1/data-transfers', DataTransferController::class)
                ->parameters(['data-transfers' => 'record']);
        });
    }
}
