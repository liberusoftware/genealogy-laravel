<?php

declare(strict_types=1);

namespace Liberu\Genealogy\GenealogyCore;

use Illuminate\Support\ServiceProvider;

final class GenealogyCoreServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(Capability::class, fn (): Capability => new Capability(
            'genealogy-core',
            'Genealogy Genealogy Core',
            ['genealogy.genealogy-core', 'genealogy.genealogy-core.lifecycle'],
        ));
    }
}
