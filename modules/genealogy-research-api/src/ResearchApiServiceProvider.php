<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Research\Api;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

final class ResearchApiServiceProvider extends ServiceProvider
{
    public function boot(Router $router): void
    {
        $router->middleware(['api', 'auth:sanctum'])->group(function () use ($router): void {
            $router->apiResource('api/v1/research-projects', ResearchProjectController::class)
                ->parameters(['research-projects' => 'record']);
        });
    }
}
