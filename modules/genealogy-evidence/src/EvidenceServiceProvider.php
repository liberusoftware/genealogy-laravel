<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Evidence;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Liberu\Genealogy\Evidence\Models\Assertion;
use Liberu\Genealogy\Evidence\Models\Citation;
use Liberu\Genealogy\Evidence\Models\Extract;
use Liberu\Genealogy\Evidence\Models\ProofConclusion;
use Liberu\Genealogy\Evidence\Models\Repository;
use Liberu\Genealogy\Evidence\Models\Source;
use Liberu\Genealogy\GenealogyCore\Policies\TeamOwnedPolicy;

final class EvidenceServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        foreach ([Source::class, Repository::class, Citation::class, Extract::class, Assertion::class, ProofConclusion::class] as $model) {
            Gate::policy($model, TeamOwnedPolicy::class);
        }
    }

    public function register(): void
    {
        $this->app->singleton(Capability::class, fn (): Capability => new Capability(
            'genealogy-evidence',
            'Genealogy Evidence',
            ['genealogy.evidence', 'genealogy.evidence.sources', 'genealogy.evidence.repositories', 'genealogy.evidence.citations', 'genealogy.evidence.extracts', 'genealogy.evidence.assertions', 'genealogy.evidence.confidence', 'genealogy.evidence.proof-conclusions', 'genealogy.evidence.lifecycle'],
        ));
    }
}
