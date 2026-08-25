<?php

declare(strict_types=1);

namespace Liberu\Platform\PlatformOrchestration\Filament;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Illuminate\Support\ServiceProvider;
use Liberu\Platform\PlatformOrchestration\Filament\Resources\PlatformWorkflowResource;

final class PlatformOrchestrationFilamentServiceProvider extends ServiceProvider
{
    public function register(): void {}
}

final class PlatformOrchestrationFilamentPlugin implements Plugin
{
    public static function make(): self
    {
        return new self();
    }

    public function getId(): string
    {
        return 'liberu-platform-orchestration-filament';
    }

    public function register(Panel $panel): void
    {
        $panel->resources([PlatformWorkflowResource::class]);
    }

    public function boot(Panel $panel): void {}
}
