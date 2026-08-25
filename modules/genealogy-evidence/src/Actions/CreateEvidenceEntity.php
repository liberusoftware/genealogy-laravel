<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Evidence\Actions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Liberu\Genealogy\Evidence\Models\Assertion;
use Liberu\Genealogy\Evidence\Models\Citation;
use Liberu\Genealogy\Evidence\Models\Extract;
use Liberu\Genealogy\Evidence\Models\ProofConclusion;
use Liberu\Genealogy\Evidence\Models\Repository;
use Liberu\Genealogy\Evidence\Models\Source;
use Liberu\Genealogy\People\Support\PersonReference;

final class CreateEvidenceEntity
{
    /** @param class-string<Model> $modelClass */
    public function execute(string $modelClass, array $attributes): Model
    {
        /** @var Model $model */
        $model = new $modelClass();
        $values = Arr::only($attributes, $model->getFillable());
        $this->validate($values, $modelClass);

        return DB::transaction(fn (): Model => $modelClass::query()->create($values));
    }

    /** @param class-string<Model> $modelClass */
    public function validate(array $values, string $modelClass): void
    {
        $required = match ($modelClass) {
            Source::class,
            Repository::class => ['name'],
            Citation::class => ['source_id'],
            Extract::class => ['citation_id', 'content'],
            Assertion::class => ['statement'],
            ProofConclusion::class => ['assertion_id', 'conclusion'],
            default => [],
        };

        foreach ($required as $field) {
            if (blank($values[$field] ?? null)) {
                throw new InvalidArgumentException("The evidence {$field} is required.");
            }
        }

        if (isset($values['confidence']) && ((int) $values['confidence'] < 0 || (int) $values['confidence'] > 100)) {
            throw new InvalidArgumentException('Evidence confidence must be between 0 and 100.');
        }

        $references = match ($modelClass) {
            Repository::class => ['source_id' => Source::class],
            Citation::class => ['source_id' => Source::class, 'repository_id' => Repository::class],
            Extract::class => ['citation_id' => Citation::class],
            Assertion::class => [
                'subject_person_id' => PersonReference::class,
                'citation_id' => Citation::class,
                'extract_id' => Extract::class,
            ],
            ProofConclusion::class => ['assertion_id' => Assertion::class],
            default => [],
        };

        foreach ($references as $field => $referenceClass) {
            if (($values[$field] ?? null) !== null && ! $this->referenceExists($referenceClass, $values[$field])) {
                throw new InvalidArgumentException("The evidence {$field} must belong to the active team.");
            }
        }
    }

    private function referenceExists(string $referenceClass, mixed $value): bool
    {
        if ($referenceClass === PersonReference::class) {
            app(PersonReference::class)->require($value);

            return true;
        }

        return $referenceClass::query()->whereKey($value)->exists();
    }
}
