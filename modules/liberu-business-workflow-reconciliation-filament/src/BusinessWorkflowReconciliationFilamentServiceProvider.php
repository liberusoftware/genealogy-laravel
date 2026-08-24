<?php

declare(strict_types=1);

namespace Liberu\Platform\BusinessWorkflowReconciliation\Filament;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Illuminate\Support\ServiceProvider;
use Liberu\Platform\BusinessWorkflowReconciliation\Filament\Resources\ReconciliationCaseResource;

final class BusinessWorkflowReconciliationFilamentServiceProvider extends ServiceProvider
{
    public function register(): void {}
}

final class BusinessWorkflowReconciliationFilamentPlugin implements Plugin
{
    public static function make(): self
    {
        return new self();
    }

    public function getId(): string
    {
        return 'liberu-business-workflow-reconciliation-filament';
    }

    public function register(Panel $panel): void
    {
        $panel->resources([ReconciliationCaseResource::class]);
    }

    public function boot(Panel $panel): void {}
}
