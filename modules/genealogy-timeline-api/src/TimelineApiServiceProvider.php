<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Timeline\Api;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Liberu\Foundation\ApiAccess\Http\Middleware\ApiContract;
use Liberu\Genealogy\GenealogyCore\Http\Middleware\EstablishTeamContext;

final class TimelineApiServiceProvider extends ServiceProvider
{
    public function boot(Router $router): void
    {
        $router->middleware(['api', 'auth:sanctum', EstablishTeamContext::class, ApiContract::class, 'throttle:60,1'])->group(function () use ($router): void {
            $router->get('api/v1/genealogy/timeline/events', [TimelineEventController::class, 'timeline'])->name('genealogy.timeline.events');
            $router->get('api/v1/genealogy/timeline/conflicts', [TimelineEventController::class, 'conflicts'])->name('genealogy.timeline.conflicts');
            $router->apiResource('api/v1/genealogy/timeline', TimelineEventController::class)->parameters(['timeline' => 'record']);
        });
    }
}
