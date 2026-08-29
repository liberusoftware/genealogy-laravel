<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Relationships\Filament;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Illuminate\Support\ServiceProvider;
use Liberu\Genealogy\Relationships\Filament\Pages\RelationshipCalculator;
use Liberu\Genealogy\Relationships\Filament\Resources\RelationshipResource;

final class RelationshipsFilamentServiceProvider extends ServiceProvider
{
    public function register(): void {}
}

final class RelationshipsFilamentPlugin implements Plugin
{
    public static function make(): self
    {
        return new self();
    }

    public function getId(): string
    {
        return 'genealogy-relationships-filament';
    }

    public function register(Panel $panel): void
    {
        $panel->resources([RelationshipResource::class]);
        $panel->pages([RelationshipCalculator::class]);
    }

    public function boot(Panel $panel): void {}
}
