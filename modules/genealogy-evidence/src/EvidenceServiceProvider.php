<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Evidence;

use Illuminate\Support\ServiceProvider;

final class EvidenceServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(Capability::class, fn (): Capability => new Capability(
            'genealogy-evidence',
            'Genealogy Evidence',
            ['genealogy.evidence', 'genealogy.evidence.lifecycle'],
        ));
    }
}
