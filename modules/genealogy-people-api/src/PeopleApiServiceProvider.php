<?php

declare(strict_types=1);

namespace Liberu\Genealogy\People\Api;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

final class PeopleApiServiceProvider extends ServiceProvider
{
    public function boot(Router $router): void
    {
        $router->middleware('api')->group(function () use ($router): void {
            $router->get('/api/v1/genealogy-people-api', function (): array {
                return ['module' => 'genealogy-people', 'surface' => 'api', 'status' => 'available'];
            });
        });
    }
}
