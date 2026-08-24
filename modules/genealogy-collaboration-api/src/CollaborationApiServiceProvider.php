<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Collaboration\Api;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

final class CollaborationApiServiceProvider extends ServiceProvider
{
    public function boot(Router $router): void
    {
        $router->middleware('api')->group(function () use ($router): void {
            $router->get('/api/v1/genealogy-collaboration-api', function (): array {
                return ['module' => 'genealogy-collaboration', 'surface' => 'api', 'status' => 'available'];
            });
        });
    }
}
