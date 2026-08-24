<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Evidence\Api;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

final class EvidenceApiServiceProvider extends ServiceProvider
{
    public function boot(Router $router): void
    {
        $router->middleware('api')->group(function () use ($router): void {
            $router->get('/api/v1/genealogy-evidence-api', function (): array {
                return ['module' => 'genealogy-evidence', 'surface' => 'api', 'status' => 'available'];
            });
        });
    }
}
