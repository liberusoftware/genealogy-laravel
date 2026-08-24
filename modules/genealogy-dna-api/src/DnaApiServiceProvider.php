<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Dna\Api;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

final class DnaApiServiceProvider extends ServiceProvider
{
    public function boot(Router $router): void
    {
        $router->middleware('api')->group(function () use ($router): void {
            $router->get('/api/v1/genealogy-dna-api', function (): array {
                return ['module' => 'genealogy-dna', 'surface' => 'api', 'status' => 'available'];
            });
        });
    }
}
