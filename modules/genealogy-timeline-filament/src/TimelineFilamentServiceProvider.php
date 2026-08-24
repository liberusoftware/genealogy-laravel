<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Timeline\Filament;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Illuminate\Support\ServiceProvider;
use Liberu\Genealogy\Timeline\Filament\Resources\TimelineEventResource;

final class TimelineFilamentServiceProvider extends ServiceProvider
{
    public function register(): void {}
}

final class TimelineFilamentPlugin implements Plugin
{
    public static function make(): self
    {
        return new self();
    }

    public function getId(): string
    {
        return 'genealogy-timeline-filament';
    }

    public function register(Panel $panel): void
    {
        $panel->resources([TimelineEventResource::class]);
    }

    public function boot(Panel $panel): void {}
}
