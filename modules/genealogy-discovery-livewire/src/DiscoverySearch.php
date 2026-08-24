<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Discovery\Livewire;

use Liberu\Genealogy\Discovery\Queries\DiscoverySearch as DiscoverySearchQuery;
use Livewire\Component;

final class DiscoverySearch extends Component
{
    public string $term = '';

    public bool $publicOnly = false;

    /** @var array{people: list<array<string, mixed>>, places: list<array<string, mixed>>, sources: list<array<string, mixed>>} */
    public array $results = ['people' => [], 'places' => [], 'sources' => []];

    public function search(DiscoverySearchQuery $search): void
    {
        $values = $this->validate(['term' => ['required', 'string', 'min:2', 'max:200'], 'publicOnly' => ['boolean']]);
        $this->results = $search->execute($values['term'], ['public_only' => $values['publicOnly'], 'limit' => 25]);
    }

    public function clear(): void
    {
        $this->reset(['term', 'results']);
        $this->results = ['people' => [], 'places' => [], 'sources' => []];
        $this->resetValidation();
    }

    public function render(): mixed
    {
        return view('genealogy-discovery-livewire::search');
    }
}
