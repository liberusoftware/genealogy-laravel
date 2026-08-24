<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Relationships\Actions;

use Illuminate\Support\Arr;
use InvalidArgumentException;
use Liberu\Genealogy\People\Models\Person;
use Liberu\Genealogy\Relationships\Models\Relationship;
use Liberu\Genealogy\Relationships\Queries\GraphValidator;

final class UpdateRelationship
{
    public function __construct(private readonly GraphValidator $validator) {}

    public function execute(Relationship $relationship, array $attributes): Relationship
    {
        $values = Arr::only($attributes, ['person_id', 'related_person_id', 'type', 'confidence', 'metadata']);
        $personId = (string) ($values['person_id'] ?? $relationship->person_id);
        $relatedPersonId = (string) ($values['related_person_id'] ?? $relationship->related_person_id);
        $type = (string) ($values['type'] ?? $relationship->type);
        $confidence = (int) ($values['confidence'] ?? $relationship->confidence ?? 100);

        if ($personId === '' || $relatedPersonId === '' || $personId === $relatedPersonId) {
            throw new InvalidArgumentException('A relationship must connect two different people.');
        }

        if ($confidence < 0 || $confidence > 100) {
            throw new InvalidArgumentException('Relationship confidence must be between 0 and 100.');
        }

        $people = Person::query()->whereKey([$personId, $relatedPersonId])->count();
        if ($people !== 2) {
            throw new InvalidArgumentException('Both people must belong to the active team.');
        }

        $this->validator->assertValid($personId, $relatedPersonId, $type, (string) $relationship->getKey());

        $relationship->update($values + [
            'person_id' => $personId,
            'related_person_id' => $relatedPersonId,
            'type' => $type,
            'confidence' => $confidence,
        ]);

        return $relationship->refresh();
    }
}
