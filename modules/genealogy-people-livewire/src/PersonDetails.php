<?php

declare(strict_types=1);

namespace Liberu\Genealogy\People\Livewire;

use Illuminate\Contracts\View\View;
use Liberu\Genealogy\People\Actions\CreateMergeCandidate;
use Liberu\Genealogy\People\Actions\CreatePersonAssociation;
use Liberu\Genealogy\People\Actions\CreatePersonIdentity;
use Liberu\Genealogy\People\Actions\CreatePersonLifeEvent;
use Liberu\Genealogy\People\Actions\CreatePersonName;
use Liberu\Genealogy\People\Actions\RemovePersonAttribute;
use Liberu\Genealogy\People\Actions\SetPersonLifeStatus;
use Liberu\Genealogy\People\Actions\UpdatePersonAttributes;
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

    public string $attributesJson = '{}';

    public string $lifeStatus = 'living';

    public string $deathDate = '';

    public string $associationPersonId = '';

    public string $associationExternalId = '';

    public string $associationRelationship = '';

    public string $associationDescription = '';

    public function mount(): void
    {
        abort_unless(auth()->check(), 403);
    }

    public function addAssociation(CreatePersonAssociation $create): void
    {
        $this->validate([
            'personId' => ['required', 'uuid'],
            'associationPersonId' => ['nullable', 'uuid', 'different:personId'],
            'associationExternalId' => ['nullable', 'string', 'max:255'],
            'associationRelationship' => ['required', 'string', 'max:255'],
            'associationDescription' => ['nullable', 'string'],
        ]);
        if ($this->associationPersonId === '' && $this->associationExternalId === '') {
            $this->addError('associationPersonId', 'Provide a person ID or external reference.');

            return;
        }
        $create->execute([
            'person_id' => $this->personId,
            'associated_person_id' => $this->associationPersonId ?: null,
            'associated_external_id' => $this->associationExternalId ?: null,
            'relationship' => $this->associationRelationship,
            'description' => $this->associationDescription ?: null,
        ]);
        $this->reset('associationPersonId', 'associationExternalId', 'associationRelationship', 'associationDescription');
        $this->dispatch('person-association-created');
    }

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

    public function updateAttributes(UpdatePersonAttributes $update): void
    {
        $this->validate(['personId' => ['required', 'uuid'], 'attributesJson' => ['required', 'json']]);
        $attributes = json_decode($this->attributesJson, true, 512, JSON_THROW_ON_ERROR);
        if (! is_array($attributes) || ($attributes !== [] && array_is_list($attributes))) {
            $this->addError('attributesJson', 'Attributes must be a JSON object.');

            return;
        }

        $update->execute(Person::query()->findOrFail($this->personId), $attributes, true);
        $this->dispatch('person-attributes-updated');
    }

    public function removeAttribute(string $attribute, RemovePersonAttribute $remove): void
    {
        $this->validate(['personId' => ['required', 'uuid']]);
        if (trim($attribute) === '' || mb_strlen($attribute) > 100) {
            $this->addError('attributesJson', 'An attribute name between 1 and 100 characters is required.');

            return;
        }
        $remove->execute(Person::query()->findOrFail($this->personId), $attribute);
        $this->dispatch('person-attributes-updated');
    }

    public function setLifeStatus(SetPersonLifeStatus $setStatus): void
    {
        $this->validate([
            'personId' => ['required', 'uuid'],
            'lifeStatus' => ['required', 'in:living,deceased'],
            'deathDate' => ['nullable', 'date'],
        ]);
        $setStatus->execute(Person::query()->findOrFail($this->personId), $this->lifeStatus, $this->deathDate ?: null);
        $this->dispatch('person-life-status-updated');
    }

    public function render(): View
    {
        $person = Person::query()->with(['names', 'identities', 'lifeEvents', 'mergeCandidates', 'associations.associatedPerson'])->findOrFail($this->personId);
        $this->attributesJson = json_encode($person->attributes ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) ?: '{}';
        $this->lifeStatus = $person->isLiving() ? 'living' : 'deceased';
        $this->deathDate = $person->death_date?->toDateString() ?? '';

        return view('genealogy-people-livewire::person-details', compact('person'));
    }
}
