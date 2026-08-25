<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Relationships;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Liberu\Genealogy\GenealogyCore\Contracts\ParentGraphReader;
use Liberu\Genealogy\GenealogyCore\Policies\TeamOwnedPolicy;
use Liberu\Genealogy\Relationships\Models\Relationship;
use Liberu\Genealogy\Relationships\Queries\ParentGraph;

final class RelationshipsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        Gate::policy(Relationship::class, TeamOwnedPolicy::class);
    }

    public function register(): void
    {
        $this->app->singleton(ParentGraphReader::class, ParentGraph::class);
        $this->app->singleton(Capability::class, fn (): Capability => new Capability(
            'genealogy-relationships',
            'Genealogy Relationships',
            ['genealogy.relationships', 'genealogy.relationships.parent', 'genealogy.relationships.partner', 'genealogy.relationships.household', 'genealogy.relationships.adoption', 'genealogy.relationships.guardianship', 'genealogy.relationships.uncertain', 'genealogy.relationships.graph-validation', 'genealogy.relationships.lifecycle'],
        ));
    }
}
