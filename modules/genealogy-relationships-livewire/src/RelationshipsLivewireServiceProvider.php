<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Relationships\Livewire;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

final class RelationshipsLivewireServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'genealogy-relationships-livewire');
        Livewire::component('genealogy-relationships-list', RelationshipList::class);
    }
}

final class Status
{
    public function render(): string
    {
        return 'Genealogy Relationships Livewire adapter is available.';
    }
}
