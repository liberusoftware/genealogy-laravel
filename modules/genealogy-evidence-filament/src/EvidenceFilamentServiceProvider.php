<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Evidence\Filament;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Illuminate\Support\ServiceProvider;
use Liberu\Genealogy\Evidence\Filament\Resources\AssertionResource;
use Liberu\Genealogy\Evidence\Filament\Resources\CitationResource;
use Liberu\Genealogy\Evidence\Filament\Resources\EvidenceRecordResource;
use Liberu\Genealogy\Evidence\Filament\Resources\ExtractResource;
use Liberu\Genealogy\Evidence\Filament\Resources\ProofConclusionResource;
use Liberu\Genealogy\Evidence\Filament\Resources\RepositoryResource;
use Liberu\Genealogy\Evidence\Filament\Resources\SourceResource;

final class EvidenceFilamentServiceProvider extends ServiceProvider
{
    public function register(): void {}
}

final class EvidenceFilamentPlugin implements Plugin
{
    public static function make(): self
    {
        return new self();
    }

    public function getId(): string
    {
        return 'genealogy-evidence-filament';
    }

    public function register(Panel $panel): void
    {
        $panel->resources([
            EvidenceRecordResource::class,
            SourceResource::class,
            RepositoryResource::class,
            CitationResource::class,
            ExtractResource::class,
            AssertionResource::class,
            ProofConclusionResource::class,
        ]);
    }

    public function boot(Panel $panel): void {}
}
