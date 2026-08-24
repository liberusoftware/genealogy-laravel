<?php

declare(strict_types=1);

namespace Liberu\Genealogy\GenealogyCore\Livewire;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

final class GenealogyCoreLivewireServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Livewire::component('genealogy-core-livewire-status', Status::class);
    }
}

final class Status
{
    public function render(): string
    {
        return 'Genealogy GenealogyCore Livewire adapter is available.';
    }
}
