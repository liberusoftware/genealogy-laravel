<?php

declare(strict_types=1);

namespace Liberu\Genealogy\GenealogyCore\Api;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

final class GenealogyCoreApiServiceProvider extends ServiceProvider
{
    public function boot(Router $router): void
    {
        $router->middleware('api')->group(function () use ($router): void {
            $router->get('/api/v1/genealogy-core-api', function (): array {
                return ['module' => 'genealogy-core', 'surface' => 'api', 'status' => 'available'];
            });
        });
    }
}
