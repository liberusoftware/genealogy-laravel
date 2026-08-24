<?php

declare(strict_types=1);

namespace Liberu\Genealogy\People\Filament;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Illuminate\Support\ServiceProvider;

final class PeopleFilamentServiceProvider extends ServiceProvider
{
    public function register(): void {}
}

final class PeopleFilamentPlugin implements Plugin
{
    public function getId(): string
    {
        return 'genealogy-people-filament';
    }

    public function register(Panel $panel): void {}

    public function boot(Panel $panel): void {}
}
