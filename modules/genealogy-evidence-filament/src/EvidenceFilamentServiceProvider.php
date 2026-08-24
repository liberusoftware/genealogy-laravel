<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Evidence\Filament;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Illuminate\Support\ServiceProvider;

final class EvidenceFilamentServiceProvider extends ServiceProvider
{
    public function register(): void {}
}

final class EvidenceFilamentPlugin implements Plugin
{
    public function getId(): string
    {
        return 'genealogy-evidence-filament';
    }

    public function register(Panel $panel): void {}

    public function boot(Panel $panel): void {}
}
