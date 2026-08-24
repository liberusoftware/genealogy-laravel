<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Reports\Filament;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Illuminate\Support\ServiceProvider;
use Liberu\Genealogy\Reports\Filament\Resources\GenealogyReportResource;

final class ReportsFilamentServiceProvider extends ServiceProvider
{
    public function register(): void {}
}

final class ReportsFilamentPlugin implements Plugin
{
    public static function make(): self
    {
        return new self();
    }

    public function getId(): string
    {
        return 'genealogy-reports-filament';
    }

    public function register(Panel $panel): void
    {
        $panel->resources([GenealogyReportResource::class]);
    }

    public function boot(Panel $panel): void {}
}
