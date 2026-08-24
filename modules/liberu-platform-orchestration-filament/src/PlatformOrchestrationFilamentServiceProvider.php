<?php

declare(strict_types=1);

namespace Liberu\Platform\PlatformOrchestration\Filament;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Illuminate\Support\ServiceProvider;

final class PlatformOrchestrationFilamentServiceProvider extends ServiceProvider
{
    public function register(): void {}
}

final class PlatformOrchestrationFilamentPlugin implements Plugin
{
    public function getId(): string
    {
        return 'liberu-platform-orchestration-filament';
    }

    public function register(Panel $panel): void {}

    public function boot(Panel $panel): void {}
}
