<?php

declare(strict_types=1);

namespace Liberu\Genealogy\GenealogyCore\Api;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Liberu\Foundation\ApiAccess\Http\Middleware\ApiContract;
use Liberu\Genealogy\GenealogyCore\Http\Middleware\EstablishTeamContext;

final class GenealogyCoreApiServiceProvider extends ServiceProvider
{
    public function boot(Router $router): void
    {
        $router->middleware(['api', EstablishTeamContext::class, ApiContract::class, 'throttle:60,1'])
            ->prefix('api/v1/genealogy/genealogy-core')
            ->group(__DIR__.'/../routes/api.php');
    }
}
