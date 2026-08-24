<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Media\Filament;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Illuminate\Support\ServiceProvider;
use Liberu\Genealogy\Media\Filament\Resources\MediaAssetResource;

final class MediaFilamentServiceProvider extends ServiceProvider
{
    public function register(): void {}
}

final class MediaFilamentPlugin implements Plugin
{
    public static function make(): self
    {
        return new self();
    }

    public function getId(): string
    {
        return 'genealogy-media-filament';
    }

    public function register(Panel $panel): void
    {
        $panel->resources([MediaAssetResource::class]);
    }

    public function boot(Panel $panel): void {}
}
