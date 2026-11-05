<?php

declare(strict_types=1);

namespace Liberu\Genealogy\TreeViewer\Livewire;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Liberu\Genealogy\People\Actions\CreatePerson;
use Liberu\Genealogy\People\Models\Person;
use Liberu\Genealogy\Relationships\Actions\RecordRelationship;
use Liberu\Genealogy\TreeViewer\Queries\TreeGraph;
use Livewire\Attributes\Locked;
use Livewire\Component;

final class TreeGraphView extends Component
{
    public string $personId = '';

    public string $search = '';

    #[Locked]
    public string $startingPersonId = '';

    public string $relativeType = 'child';

    public string $givenName = '';

    public string $familyName = '';

    public string $birthDate = '';

    public string $deathDate = '';

    public string $notice = '';

    public int $generations = 3;

    public string $view = 'chart';

    public bool $includeLiving = true;

    public bool $includeSiblings = false;

    public int $maxNodes = 2000;

    public array $data = [];

    public function mount(TreeGraph $graph): void
    {
        if ($this->personId !== '') {
            $this->loadGraph($graph);
        }
    }

    /** @return array<string, array<int, mixed>> */
    protected function rules(): array
    {
        return [
            'personId' => ['required', 'uuid'],
            'generations' => ['integer', 'between:0,12'],
            'view' => ['required', 'in:pedigree,descendants,fan,chart'],
            'includeLiving' => ['boolean'],
            'includeSiblings' => ['boolean'],
            'maxNodes' => ['integer', 'between:100,5000'],
        ];
    }

    public function loadGraph(TreeGraph $graph): void
    {
        $this->validate();
        $person = Person::query()->find($this->personId);

        if (! $person) {
            throw ValidationException::withMessages(['personId' => 'The selected person was not found.']);
        }

        $this->resetValidation();
        if ($this->startingPersonId === '') {
            $this->startingPersonId = $this->personId;
        }

        $includeLiving = auth()->check() && $this->includeLiving;
        $this->data = $graph->for($person, $this->generations, $includeLiving, $this->view, $this->includeSiblings, $this->maxNodes);
    }

    public function navigateTo(string $personId, TreeGraph $graph): void
    {
        $this->notice = '';
        $this->personId = $personId;
        $this->loadGraph($graph);
    }

    public function returnToStart(TreeGraph $graph): void
    {
        if ($this->startingPersonId !== '') {
            $this->navigateTo($this->startingPersonId, $graph);
        }
    }

    public function addRelative(CreatePerson $create, RecordRelationship $record, TreeGraph $graph): void
    {
        abort_unless(auth()->check(), 403);
        $this->validate();
        $this->validate([
            'personId' => ['required', 'uuid'],
            'relativeType' => ['required', 'in:parent,child,partner'],
            'givenName' => ['required', 'string', 'max:255', 'not_regex:/^\\s*$/u'],
            'familyName' => ['nullable', 'string', 'max:255'],
            'birthDate' => ['nullable', 'date', 'before_or_equal:today'],
            'deathDate' => ['nullable', 'date', 'before_or_equal:today', ...($this->birthDate !== '' ? ['after_or_equal:birthDate'] : [])],
        ]);
        $root = Person::query()->findOrFail($this->personId);
        DB::transaction(function () use ($root, $create, $record): void {
            $relative = $create->execute([
                'given_name' => $this->givenName,
                'family_name' => $this->familyName,
                'birth_date' => $this->birthDate ?: null,
                'death_date' => $this->deathDate ?: null,
                'is_public' => false,
            ]);
            $record->execute([
                'person_id' => $this->relativeType === 'parent' ? $relative->id : $root->id,
                'related_person_id' => $this->relativeType === 'parent' ? $root->id : $relative->id,
                'type' => $this->relativeType === 'partner' ? 'partner' : 'parent',
            ]);
        });
        $this->reset('givenName', 'familyName', 'birthDate', 'deathDate');
        $this->loadGraph($graph);
        $this->notice = 'Relative added. Choose All family and include living people to see every relative.';
    }

    public function setView(string $view, TreeGraph $graph): void
    {
        $this->view = $view;
        $this->validateOnly('view');

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
        $search = trim($this->search);
        $people = auth()->check() && mb_strlen($search) >= 2
            ? Person::query()
                ->where(function ($query) use ($search): void {
                    foreach (preg_split('/\\s+/u', mb_substr($search, 0, 100)) as $word) {
                        $query->where(function ($names) use ($word): void {
                            $names->where('given_name', 'like', '%'.$word.'%')
                                ->orWhere('family_name', 'like', '%'.$word.'%')
                                ->orWhere('display_name', 'like', '%'.$word.'%');
                        });
                    }
                })
                ->orderBy('family_name')->orderBy('given_name')->limit(10)->get()
            : collect();

        return view('genealogy-tree-viewer-livewire::graph', ['data' => $this->data, 'people' => $people]);
    }
}
