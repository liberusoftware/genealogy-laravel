<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Evidence\Filament;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Illuminate\Support\ServiceProvider;
use Liberu\Genealogy\Evidence\Filament\Resources\EvidenceRecordResource;

final class EvidenceFilamentServiceProvider extends ServiceProvider
{
    public function register(): void {}
}

final class EvidenceFilamentPlugin implements Plugin
{
    public static function make(): self
    {
        return new self();
    }

    public function getId(): string
    {
        return 'genealogy-evidence-filament';
    }

    public function register(Panel $panel): void
    {
        $panel->resources([EvidenceRecordResource::class]);
    }

    public function boot(Panel $panel): void {}
}
