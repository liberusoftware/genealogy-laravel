<?php

declare(strict_types=1);

namespace Liberu\Genealogy\TreeViewer;

use Illuminate\Support\ServiceProvider;

final class TreeViewerServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }

    public function register(): void
    {
        $this->app->singleton(Capability::class, fn (): Capability => new Capability(
            'genealogy-tree-viewer',
            'Genealogy Tree Viewer',
            ['genealogy.tree-viewer', 'genealogy.tree-viewer.lifecycle'],
        ));
    }
}
