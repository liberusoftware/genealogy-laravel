<?php

declare(strict_types=1);

namespace Liberu\Genealogy\TreeViewer\Filament;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Illuminate\Support\ServiceProvider;

final class TreeViewerFilamentServiceProvider extends ServiceProvider
{
    public function register(): void {}
}

final class TreeViewerFilamentPlugin implements Plugin
{
    public function getId(): string
    {
        return 'genealogy-tree-viewer-filament';
    }

    public function register(Panel $panel): void {}

    public function boot(Panel $panel): void {}
}
