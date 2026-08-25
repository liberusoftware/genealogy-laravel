<?php

declare(strict_types=1);

namespace Liberu\Genealogy\People\Livewire;

use Illuminate\Contracts\View\View;
use Liberu\Genealogy\People\Actions\CreateMergeCandidate;
use Liberu\Genealogy\People\Actions\CreatePersonIdentity;
use Liberu\Genealogy\People\Actions\CreatePersonLifeEvent;
use Liberu\Genealogy\People\Actions\CreatePersonName;
use Liberu\Genealogy\People\Models\Person;
use Livewire\Component;

final class PersonDetails extends Component
{
    public string $personId = '';

    public string $givenName = '';

    public string $familyName = '';

    public string $identityType = '';

    public string $identityValue = '';

    public string $lifeEventType = '';

    public string $lifeEventDate = '';

    public string $lifeEventDescription = '';

    public string $candidatePersonId = '';

    public function addName(CreatePersonName $create): void
    {
        $this->validate(['personId' => ['required', 'uuid'], 'givenName' => ['nullable', 'string', 'max:255'], 'familyName' => ['nullable', 'string', 'max:255']]);
        $create->execute(['person_id' => $this->personId, 'given_name' => $this->givenName, 'family_name' => $this->familyName]);
        $this->reset('givenName', 'familyName');
        $this->dispatch('person-supporting-record-created', type: 'name');
    }

    public function addIdentity(CreatePersonIdentity $create): void
    {
        $this->validate(['personId' => ['required', 'uuid'], 'identityType' => ['required', 'string', 'max:100'], 'identityValue' => ['required', 'string', 'max:500']]);
        $create->execute(['person_id' => $this->personId, 'type' => $this->identityType, 'value' => $this->identityValue]);
        $this->reset('identityType', 'identityValue');
        $this->dispatch('person-supporting-record-created', type: 'identity');
    }

    public function addLifeEvent(CreatePersonLifeEvent $create): void
    {
        $this->validate(['personId' => ['required', 'uuid'], 'lifeEventType' => ['required', 'string', 'max:100'], 'lifeEventDate' => ['nullable', 'date'], 'lifeEventDescription' => ['nullable', 'string']]);
        $create->execute(['person_id' => $this->personId, 'type' => $this->lifeEventType, 'date' => $this->lifeEventDate ?: null, 'description' => $this->lifeEventDescription ?: null]);
        $this->reset('lifeEventType', 'lifeEventDate', 'lifeEventDescription');
        $this->dispatch('person-supporting-record-created', type: 'life-event');
    }

    public function proposeMerge(CreateMergeCandidate $create): void
    {
        $this->validate(['personId' => ['required', 'uuid'], 'candidatePersonId' => ['required', 'uuid']]);
        $create->execute(['person_id' => $this->personId, 'candidate_person_id' => $this->candidatePersonId]);
        $this->reset('candidatePersonId');
        $this->dispatch('person-merge-candidate-created');
    }

    public function render(): View
    {
        $person = Person::query()->with(['names', 'identities', 'lifeEvents', 'mergeCandidates'])->findOrFail($this->personId);

        return view('genealogy-people-livewire::person-details', compact('person'));
    }
}
