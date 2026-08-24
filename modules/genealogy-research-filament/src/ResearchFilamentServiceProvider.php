<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Research\Filament;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Illuminate\Support\ServiceProvider;
use Liberu\Genealogy\Research\Filament\Resources\ResearchProjectResource;

final class ResearchFilamentServiceProvider extends ServiceProvider
{
    public function register(): void {}
}

final class ResearchFilamentPlugin implements Plugin
{
    public static function make(): self
    {
        return new self();
    }

    public function getId(): string
    {
        return 'genealogy-research-filament';
    }

    public function register(Panel $panel): void
    {
        $panel->resources([ResearchProjectResource::class]);
    }

    public function boot(Panel $panel): void {}
}
