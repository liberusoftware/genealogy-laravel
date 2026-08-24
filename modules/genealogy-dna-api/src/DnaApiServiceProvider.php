<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Dna\Api;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

final class DnaApiServiceProvider extends ServiceProvider
{
    public function boot(Router $router): void
    {
        $router->middleware(['api', 'auth:sanctum'])->group(function () use ($router): void {
            $router->apiResource('api/v1/dna-kits', DnaKitController::class)
                ->parameters(['dna-kits' => 'record']);
        });
    }
}
