<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Reports\Api;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

final class ReportsApiServiceProvider extends ServiceProvider
{
    public function boot(Router $router): void
    {
        $router->middleware('api')->group(function () use ($router): void {
            $router->get('/api/v1/genealogy-reports-api', function (): array {
                return ['module' => 'genealogy-reports', 'surface' => 'api', 'status' => 'available'];
            });
        });
    }
}
