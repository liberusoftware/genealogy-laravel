<?php

declare(strict_types=1);

namespace Liberu\Platform\BusinessWorkflowReconciliation\Filament;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Illuminate\Support\ServiceProvider;

final class BusinessWorkflowReconciliationFilamentServiceProvider extends ServiceProvider
{
    public function register(): void {}
}

final class BusinessWorkflowReconciliationFilamentPlugin implements Plugin
{
    public function getId(): string
    {
        return 'liberu-business-workflow-reconciliation-filament';
    }

    public function register(Panel $panel): void {}

    public function boot(Panel $panel): void {}
}
