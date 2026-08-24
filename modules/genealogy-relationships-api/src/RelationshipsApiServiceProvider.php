<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Relationships\Api;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

final class RelationshipsApiServiceProvider extends ServiceProvider
{
    public function boot(Router $router): void
    {
        $router->middleware('api')->group(function () use ($router): void {
            $router->get('/api/v1/genealogy-relationships-api', function (): array {
                return ['module' => 'genealogy-relationships', 'surface' => 'api', 'status' => 'available'];
            });
        });
    }
}
