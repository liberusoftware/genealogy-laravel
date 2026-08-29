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

    public string $view = 'chart';

    public bool $includeLiving = true;

    public bool $includeSiblings = false;

    public int $maxNodes = 2000;

    public array $data = [];

    public function loadGraph(TreeGraph $graph): void
    {
        $this->validate([
            'personId' => ['required', 'uuid'],
            'generations' => ['integer', 'between:0,12'],
            'view' => ['in:pedigree,descendants,fan,chart'],
            'includeLiving' => ['boolean'],
            'includeSiblings' => ['boolean'],
            'maxNodes' => ['integer', 'between:100,5000'],
        ]);
        $person = Person::query()->find($this->personId);

        if (! $person) {
            throw ValidationException::withMessages(['personId' => 'The selected person was not found.']);
        }

        $this->data = $graph->for($person, $this->generations, $this->includeLiving, $this->view, $this->includeSiblings, $this->maxNodes);
    }

    public function navigateTo(string $personId, TreeGraph $graph): void
    {
        $this->personId = $personId;
        $this->loadGraph($graph);
    }

    public function setView(string $view, TreeGraph $graph): void
    {
        $this->view = $view;

        if ($this->personId !== '') {
            $this->loadGraph($graph);
        }
    }

    public function setGenerations(int $generations, TreeGraph $graph): void
    {
        $this->generations = max(0, min(12, $generations));

        if ($this->personId !== '') {
            $this->loadGraph($graph);
        }
    }

    public function render(): mixed
    {
        return view('genealogy-tree-viewer-livewire::graph', ['data' => $this->data]);
    }
}
