<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Relationships;

use Illuminate\Support\ServiceProvider;

final class RelationshipsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(Capability::class, fn (): Capability => new Capability(
            'genealogy-relationships',
            'Genealogy Relationships',
            ['genealogy.relationships', 'genealogy.relationships.lifecycle'],
        ));
    }
}
