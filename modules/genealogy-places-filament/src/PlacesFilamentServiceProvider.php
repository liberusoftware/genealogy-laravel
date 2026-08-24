<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Places\Filament;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Illuminate\Support\ServiceProvider;

final class PlacesFilamentServiceProvider extends ServiceProvider
{
    public function register(): void {}
}

final class PlacesFilamentPlugin implements Plugin
{
    public function getId(): string
    {
        return 'genealogy-places-filament';
    }

    public function register(Panel $panel): void {}

    public function boot(Panel $panel): void {}
}
