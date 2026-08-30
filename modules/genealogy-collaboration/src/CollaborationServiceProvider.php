<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Collaboration;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Liberu\Genealogy\Collaboration\Models\VirtualEvent;
use Liberu\Genealogy\GenealogyCore\Policies\TeamOwnedPolicy;

final class CollaborationServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        Gate::policy(VirtualEvent::class, TeamOwnedPolicy::class);
    }

    public function register(): void
    {
        $this->app->singleton(Capability::class, fn (): Capability => new Capability(
            'genealogy-collaboration',
            'Genealogy Collaboration',
            ['genealogy.collaboration', 'genealogy.collaboration.invitations', 'genealogy.collaboration.events', 'genealogy.collaboration.rsvp', 'genealogy.collaboration.video-conferencing', 'genealogy.collaboration.lifecycle'],
        ));
    }
}
