<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Media\Livewire;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

final class MediaLivewireServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Livewire::component('genealogy-media-livewire-status', Status::class);
    }
}

final class Status
{
    public function render(): string
    {
        return 'Genealogy Media Livewire adapter is available.';
    }
}
