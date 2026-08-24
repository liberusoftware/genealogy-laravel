<?php

declare(strict_types=1);

namespace Liberu\Genealogy\TreeViewer\Livewire;

use Illuminate\Validation\ValidationException;
use Liberu\Genealogy\People\Models\Person;
use Liberu\Genealogy\TreeViewer\Queries\TreeGraph;
use Livewire\Component;

final class TreeGraphView extends Component
{
    public string $personId = '';

    public int $generations = 3;

    public function render(TreeGraph $graph): mixed
    {
        $data = null;

        if ($this->personId !== '') {
            $this->validate(['personId' => ['uuid']]);
            $person = Person::query()->find($this->personId);

            if (! $person) {
                throw ValidationException::withMessages(['personId' => 'The selected person was not found.']);
            }

            $data = $graph->for($person, $this->generations);
        }

        return view('genealogy-tree-viewer-livewire::graph', ['data' => $data]);
    }
}
