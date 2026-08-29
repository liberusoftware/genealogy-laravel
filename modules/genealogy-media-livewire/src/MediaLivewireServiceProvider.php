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
        Livewire::component('genealogy-media-upload', MediaAssetUpload::class);
        Livewire::component('genealogy-media-face-review', MediaFaceReview::class);
        Livewire::component('module-genealogy-media::media-asset-list', MediaAssetList::class);
        Livewire::component('module-genealogy-media::media-library-browser', MediaLibraryBrowser::class);
        Livewire::component('module-genealogy-media::media-asset-upload', MediaAssetUpload::class);
        Livewire::component('module-genealogy-media::media-face-review', MediaFaceReview::class);
    }
}

final class Status
{
    public function render(): string
    {
        return 'Genealogy Media Livewire adapter is available.';
    }
}
