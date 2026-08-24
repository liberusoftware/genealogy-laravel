<?php

declare(strict_types=1);

namespace Liberu\Platform\RevenueAndCareOrchestration\Filament;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Illuminate\Support\ServiceProvider;
use Liberu\Platform\RevenueAndCareOrchestration\Filament\Resources\CarePlanResource;

final class RevenueAndCareOrchestrationFilamentServiceProvider extends ServiceProvider
{
    public function register(): void {}
}

final class RevenueAndCareOrchestrationFilamentPlugin implements Plugin
{
    public static function make(): self
    {
        return new self();
    }

    public function getId(): string
    {
        return 'liberu-revenue-and-care-orchestration-filament';
    }

    public function register(Panel $panel): void
    {
        $panel->resources([CarePlanResource::class]);
    }

    public function boot(Panel $panel): void {}
}
