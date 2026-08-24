<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Media\Api;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

final class MediaApiServiceProvider extends ServiceProvider
{
    public function boot(Router $router): void
    {
        $router->middleware(['api', 'auth:sanctum'])->group(function () use ($router): void {
            $router->apiResource('api/v1/media-assets', MediaAssetController::class)
                ->parameters(['media-assets' => 'record']);
        });
    }
}
