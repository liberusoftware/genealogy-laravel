<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Media\Livewire;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

final class MediaLivewireServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'genealogy-media-livewire');
        Livewire::component('genealogy-media-list', MediaAssetList::class);
        Livewire::component('genealogy-media-library', MediaLibraryBrowser::class);
    }
}

final class Status
{
    public function render(): string
    {
        return 'Genealogy Media Livewire adapter is available.';
    }
}
