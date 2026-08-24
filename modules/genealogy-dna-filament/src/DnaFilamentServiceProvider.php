<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Dna\Filament;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Illuminate\Support\ServiceProvider;
use Liberu\Genealogy\Dna\Filament\Resources\DnaKitResource;

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
        $panel->resources([DnaKitResource::class]);
    }

    public function boot(Panel $panel): void {}
}
