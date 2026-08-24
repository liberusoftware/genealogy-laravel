<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Collaboration\Filament;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Illuminate\Support\ServiceProvider;

final class CollaborationFilamentServiceProvider extends ServiceProvider
{
    public function register(): void {}
}

final class CollaborationFilamentPlugin implements Plugin
{
    public function getId(): string
    {
        return 'genealogy-collaboration-filament';
    }

    public function register(Panel $panel): void {}

    public function boot(Panel $panel): void {}
}
