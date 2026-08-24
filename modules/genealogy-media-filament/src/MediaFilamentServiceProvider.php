<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Media\Filament;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Illuminate\Support\ServiceProvider;

final class MediaFilamentServiceProvider extends ServiceProvider
{
    public function register(): void {}
}

final class MediaFilamentPlugin implements Plugin
{
    public function getId(): string
    {
        return 'genealogy-media-filament';
    }

    public function register(Panel $panel): void {}

    public function boot(Panel $panel): void {}
}
