<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Places\Filament;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Illuminate\Support\ServiceProvider;
use Liberu\Genealogy\Places\Filament\Resources\PlaceResource;

final class PlacesFilamentServiceProvider extends ServiceProvider
{
    public function register(): void {}
}

final class PlacesFilamentPlugin implements Plugin
{
    public static function make(): self
    {
        return new self();
    }

    public function getId(): string
    {
        return 'genealogy-places-filament';
    }

    public function register(Panel $panel): void
    {
        $panel->resources([PlaceResource::class]);
    }

    public function boot(Panel $panel): void {}
}
