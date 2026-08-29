<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Relationships\Livewire;

use Liberu\Genealogy\Relationships\Actions\CreateRelationship;
use Liberu\Genealogy\Relationships\Models\Relationship;
use Livewire\Component;

final class RelationshipEditor extends Component
{
    public string $personId = '';

    public string $relatedPersonId = '';

    public string $type = 'parent';

    public int $confidence = 100;

    public function save(CreateRelationship $create): void
    {
        $this->validate([
            'personId' => ['required', 'uuid'],
            'relatedPersonId' => ['required', 'uuid', 'different:personId'],
            'type' => ['required', 'in:'.implode(',', Relationship::TYPES)],
            'confidence' => ['required', 'integer', 'between:0,100'],
        ]);
        $create->execute([
            'person_id' => $this->personId,
            'related_person_id' => $this->relatedPersonId,
            'type' => $this->type,
            'confidence' => $this->confidence,
        ]);
        $this->reset(['personId', 'relatedPersonId']);
        $this->dispatch('relationship-created');
    }

    public function render(): mixed
    {
        return view('genealogy-relationships-livewire::editor');
    }
}
