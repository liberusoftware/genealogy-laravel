<?php

declare(strict_types=1);

namespace Liberu\Genealogy\TreeViewer\Filament;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Illuminate\Support\ServiceProvider;
use Liberu\Genealogy\TreeViewer\Filament\Resources\TreeViewResource;

final class TreeViewerFilamentServiceProvider extends ServiceProvider
{
    public function register(): void {}
}

final class TreeViewerFilamentPlugin implements Plugin
{
    public static function make(): self
    {
        return new self();
    }

    public function getId(): string
    {
        return 'genealogy-tree-viewer-filament';
    }

    public function register(Panel $panel): void
    {
        $panel->resources([TreeViewResource::class]);
    }

    public function boot(Panel $panel): void {}
}
