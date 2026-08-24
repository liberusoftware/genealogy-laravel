<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Relationships\Filament;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Illuminate\Support\ServiceProvider;

final class RelationshipsFilamentServiceProvider extends ServiceProvider
{
    public function register(): void {}
}

final class RelationshipsFilamentPlugin implements Plugin
{
    public function getId(): string
    {
        return 'genealogy-relationships-filament';
    }

    public function register(Panel $panel): void {}

    public function boot(Panel $panel): void {}
}
