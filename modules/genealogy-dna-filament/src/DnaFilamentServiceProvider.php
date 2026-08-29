<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Dna\Filament;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Illuminate\Support\ServiceProvider;
use Liberu\Genealogy\Dna\Filament\Resources\DnaConsentResource;
use Liberu\Genealogy\Dna\Filament\Resources\DnaGroupResource;
use Liberu\Genealogy\Dna\Filament\Resources\DnaKitResource;
use Liberu\Genealogy\Dna\Filament\Resources\DnaMatchResource;
use Liberu\Genealogy\Dna\Filament\Resources\DnaNoteResource;
use Liberu\Genealogy\Dna\Filament\Resources\DnaProviderResource;
use Liberu\Genealogy\Dna\Filament\Resources\DnaRelationshipResource;
use Liberu\Genealogy\Dna\Filament\Resources\DnaSegmentResource;

final class DnaFilamentServiceProvider extends ServiceProvider
{
    public function register(): void {}
}

final class DnaFilamentPlugin implements Plugin
{
    public static function make(): self
    {
        return new self();
    }

    public function getId(): string
    {
        return 'genealogy-dna-filament';
    }

    public function register(Panel $panel): void
    {
        $panel->resources([
            DnaKitResource::class,
            DnaMatchResource::class,
            DnaGroupResource::class,
            DnaRelationshipResource::class,
            DnaNoteResource::class,
            DnaSegmentResource::class,
            DnaConsentResource::class,
            DnaProviderResource::class,
        ]);
    }

    public function boot(Panel $panel): void {}
}
