<?php

declare(strict_types=1);

namespace Liberu\Genealogy\People;

use Illuminate\Support\ServiceProvider;

final class PeopleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(Capability::class, fn (): Capability => new Capability(
            'genealogy-people',
            'Genealogy People',
            ['genealogy.people', 'genealogy.people.lifecycle'],
        ));
    }
}
