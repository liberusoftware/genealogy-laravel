<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Places\Livewire;

use Liberu\Genealogy\Places\Actions\CreatePlace;
use Livewire\Component;

final class PlaceEditor extends Component
{
    public string $name = '';

    public string $parentId = '';

    public string $jurisdiction = '';

    public string $latitude = '';

    public string $longitude = '';

    public bool $isCurrent = true;

    public function save(CreatePlace $create): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'parentId' => ['nullable', 'uuid'],
            'jurisdiction' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'isCurrent' => ['boolean'],
        ]);
        $create->execute([
            'name' => $this->name,
            'parent_id' => $this->parentId ?: null,
            'jurisdiction' => $this->jurisdiction ?: null,
            'latitude' => $this->latitude !== '' ? $this->latitude : null,
            'longitude' => $this->longitude !== '' ? $this->longitude : null,
            'is_current' => $this->isCurrent,
        ]);
        $this->reset('name', 'parentId', 'jurisdiction', 'latitude', 'longitude');
        $this->dispatch('place-created');
    }

    public function render(): mixed
    {
        return view('genealogy-places-livewire::editor');
    }
}
