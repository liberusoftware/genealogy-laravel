<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Collaboration\Api;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

final class CollaborationApiServiceProvider extends ServiceProvider
{
    public function boot(Router $router): void
    {
        $router->middleware(['api', 'auth:sanctum'])->group(function () use ($router): void {
            $router->apiResource('api/v1/collaboration-spaces', CollaborationSpaceController::class)
                ->parameters(['collaboration-spaces' => 'record']);
        });
    }
}
