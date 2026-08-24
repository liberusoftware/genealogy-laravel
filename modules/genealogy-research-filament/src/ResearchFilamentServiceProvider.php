<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Research\Filament;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Illuminate\Support\ServiceProvider;

final class ResearchFilamentServiceProvider extends ServiceProvider
{
    public function register(): void {}
}

final class ResearchFilamentPlugin implements Plugin
{
    public function getId(): string
    {
        return 'genealogy-research-filament';
    }

    public function register(Panel $panel): void {}

    public function boot(Panel $panel): void {}
}
