<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Timeline\Filament;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Illuminate\Support\ServiceProvider;

final class TimelineFilamentServiceProvider extends ServiceProvider
{
    public function register(): void {}
}

final class TimelineFilamentPlugin implements Plugin
{
    public function getId(): string
    {
        return 'genealogy-timeline-filament';
    }

    public function register(Panel $panel): void {}

    public function boot(Panel $panel): void {}
}
