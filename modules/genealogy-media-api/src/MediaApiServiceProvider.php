<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Media\Api;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Liberu\Genealogy\GenealogyCore\Http\Middleware\EstablishTeamContext;

final class MediaApiServiceProvider extends ServiceProvider
{
    public function boot(Router $router): void
    {
        $router->middleware(['api', 'auth:sanctum', EstablishTeamContext::class, 'throttle:api'])->group(function () use ($router): void {
            $router->get('api/v1/genealogy/media/library', [MediaAssetController::class, 'library'])->name('genealogy.media.library');
            $router->post('api/v1/genealogy/media/{record}/links', [MediaAssetController::class, 'link'])->name('genealogy.media.link');
            $router->apiResource('api/v1/genealogy/media', MediaAssetController::class)->parameters(['media' => 'record']);
        });
    }
}
