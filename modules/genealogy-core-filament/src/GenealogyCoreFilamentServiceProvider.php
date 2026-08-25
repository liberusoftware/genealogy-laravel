<?php

declare(strict_types=1);

namespace Liberu\Genealogy\GenealogyCore\Filament;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Illuminate\Support\ServiceProvider;
use Liberu\Genealogy\GenealogyCore\Filament\Resources\TreeResource;

final class GenealogyCoreFilamentServiceProvider extends ServiceProvider
{
    public function register(): void {}
}

final class GenealogyCoreFilamentPlugin implements Plugin
{
    public static function make(): self
    {
        return new self();
    }

    public function getId(): string
    {
        return 'genealogy-core-filament';
    }

    public function register(Panel $panel): void
    {
        $panel->resources([TreeResource::class]);
    }

    public function boot(Panel $panel): void {}
}
