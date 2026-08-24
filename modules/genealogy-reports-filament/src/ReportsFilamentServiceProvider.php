<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Reports\Filament;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Illuminate\Support\ServiceProvider;

final class ReportsFilamentServiceProvider extends ServiceProvider
{
    public function register(): void {}
}

final class ReportsFilamentPlugin implements Plugin
{
    public function getId(): string
    {
        return 'genealogy-reports-filament';
    }

    public function register(Panel $panel): void {}

    public function boot(Panel $panel): void {}
}
