<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Discovery\Filament;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Illuminate\Support\ServiceProvider;

final class DiscoveryFilamentServiceProvider extends ServiceProvider
{
    public function register(): void {}
}

final class DiscoveryFilamentPlugin implements Plugin
{
    public function getId(): string
    {
        return 'genealogy-discovery-filament';
    }

    public function register(Panel $panel): void {}

    public function boot(Panel $panel): void {}
}
