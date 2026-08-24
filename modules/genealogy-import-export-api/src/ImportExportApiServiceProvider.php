<?php

declare(strict_types=1);

namespace Liberu\Genealogy\ImportExport\Api;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

final class ImportExportApiServiceProvider extends ServiceProvider
{
    public function boot(Router $router): void
    {
        $router->middleware('api')->group(function () use ($router): void {
            $router->get('/api/v1/genealogy-import-export-api', function (): array {
                return ['module' => 'genealogy-import-export', 'surface' => 'api', 'status' => 'available'];
            });
        });
    }
}
