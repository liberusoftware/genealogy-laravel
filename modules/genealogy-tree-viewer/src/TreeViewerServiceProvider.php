<?php

declare(strict_types=1);

namespace Liberu\Genealogy\TreeViewer;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Liberu\Genealogy\GenealogyCore\Policies\TeamOwnedPolicy;
use Liberu\Genealogy\People\Events\PersonMerged;
use Liberu\Genealogy\TreeViewer\Listeners\ReconcilePersonMerge;
use Liberu\Genealogy\TreeViewer\Models\TreeView;

final class TreeViewerServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        Gate::policy(TreeView::class, TeamOwnedPolicy::class);
        Event::listen(PersonMerged::class, ReconcilePersonMerge::class);
    }

    public function register(): void
    {
        $this->app->singleton(Capability::class, fn (): Capability => new Capability(
            'genealogy-tree-viewer',
            'Genealogy Tree Viewer',
            ['genealogy.tree-viewer', 'genealogy.tree-viewer.pedigree', 'genealogy.tree-viewer.descendants', 'genealogy.tree-viewer.fan-chart', 'genealogy.tree-viewer.navigation', 'genealogy.tree-viewer.filters', 'genealogy.tree-viewer.large-tree-rendering', 'genealogy.tree-viewer.lifecycle'],
        ));
    }
}
