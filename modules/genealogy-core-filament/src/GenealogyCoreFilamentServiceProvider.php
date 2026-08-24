<?php

declare(strict_types=1);

namespace Liberu\Genealogy\GenealogyCore\Filament;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Illuminate\Support\ServiceProvider;

final class GenealogyCoreFilamentServiceProvider extends ServiceProvider
{
    public function register(): void {}
}

final class GenealogyCoreFilamentPlugin implements Plugin
{
    public function getId(): string
    {
        return 'genealogy-core-filament';
    }

    public function register(Panel $panel): void {}

    public function boot(Panel $panel): void {}
}
