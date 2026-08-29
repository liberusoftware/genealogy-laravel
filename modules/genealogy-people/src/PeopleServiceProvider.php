<?php

declare(strict_types=1);

namespace Liberu\Genealogy\People;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Liberu\Genealogy\GenealogyCore\Contracts\PersonReferenceResolver;
use Liberu\Genealogy\GenealogyCore\Policies\TeamOwnedPolicy;
use Liberu\Genealogy\People\Models\MergeCandidate;
use Liberu\Genealogy\People\Models\Person;
use Liberu\Genealogy\People\Models\PersonAssociation;
use Liberu\Genealogy\People\Models\PersonIdentity;
use Liberu\Genealogy\People\Models\PersonLifeEvent;
use Liberu\Genealogy\People\Models\PersonName;
use Liberu\Genealogy\People\Support\PersonReference;

final class PeopleServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        foreach ([Person::class, PersonName::class, PersonIdentity::class, PersonLifeEvent::class, PersonAssociation::class, MergeCandidate::class] as $model) {
            Gate::policy($model, TeamOwnedPolicy::class);
        }
    }

    public function register(): void
    {
        $this->app->singleton(PersonReferenceResolver::class, PersonReference::class);
        $this->app->singleton(Capability::class, fn (): Capability => new Capability(
            'genealogy-people',
            'Genealogy People',
            ['genealogy.people', 'genealogy.people.names', 'genealogy.people.identities', 'genealogy.people.attributes', 'genealogy.people.life-events', 'genealogy.people.living-status', 'genealogy.people.merge-candidates', 'genealogy.people.lifecycle'],
        ));
    }
}
