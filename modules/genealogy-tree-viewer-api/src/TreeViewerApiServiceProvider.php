<?php

declare(strict_types=1);

namespace Liberu\Genealogy\TreeViewer\Api;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

final class TreeViewerApiServiceProvider extends ServiceProvider
{
    public function boot(Router $router): void
    {
        $router->middleware('api')->group(function () use ($router): void {
            $router->get('/api/v1/genealogy-tree-viewer-api', function (): array {
                return ['module' => 'genealogy-tree-viewer', 'surface' => 'api', 'status' => 'available'];
            });
        });
    }
}
