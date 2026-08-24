<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Research\Api;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

final class ResearchApiServiceProvider extends ServiceProvider
{
    public function boot(Router $router): void
    {
        $router->middleware('api')->group(function () use ($router): void {
            $router->get('/api/v1/genealogy-research-api', function (): array {
                return ['module' => 'genealogy-research', 'surface' => 'api', 'status' => 'available'];
            });
        });
    }
}
