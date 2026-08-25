<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Media\Api;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Liberu\Foundation\ApiAccess\Http\Middleware\ApiContract;
use Liberu\Genealogy\GenealogyCore\Http\Middleware\EstablishTeamContext;

final class MediaApiServiceProvider extends ServiceProvider
{
    public function boot(Router $router): void
    {
        $router->middleware(['api', 'auth:sanctum', EstablishTeamContext::class, ApiContract::class, 'throttle:60,1'])->group(function () use ($router): void {
            $router->get('api/v1/genealogy/media/library', [MediaAssetController::class, 'library'])->name('genealogy.media.library');
            $router->post('api/v1/genealogy/media/upload', [MediaAssetController::class, 'upload'])->name('genealogy.media.upload');
            $router->post('api/v1/genealogy/media/{record}/links', [MediaAssetController::class, 'link'])->name('genealogy.media.link');
            $router->post('api/v1/genealogy/media/{record}/analyze-faces', [MediaAssetController::class, 'analyzeFaces'])->name('genealogy.media.analyze-faces');
            $router->get('api/v1/genealogy/media/{record}/face-tags', [MediaAssetController::class, 'faceTags'])->name('genealogy.media.face-tags');
            $router->patch('api/v1/genealogy/media/face-tags/{tag}', [MediaAssetController::class, 'reviewFaceTag'])->name('genealogy.media.face-tags.review');
            $router->apiResource('api/v1/genealogy/media', MediaAssetController::class)->parameters(['media' => 'record']);
        });
    }
}
