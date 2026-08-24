<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Media\Api;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

final class MediaApiServiceProvider extends ServiceProvider
{
    public function boot(Router $router): void
    {
        $router->middleware('api')->group(function () use ($router): void {
            $router->get('/api/v1/genealogy-media-api', function (): array {
                return ['module' => 'genealogy-media', 'surface' => 'api', 'status' => 'available'];
            });
        });
    }
}
