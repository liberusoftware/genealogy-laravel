<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Evidence\Actions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Liberu\Genealogy\GenealogyCore\TeamContext;

final class UpdateEvidenceEntity
{
    public function __construct(private readonly ?CreateEvidenceEntity $validator = null) {}

    public function execute(Model $entity, array $attributes): Model
    {
        if ((string) $entity->team_id !== app(TeamContext::class)->require()) {
            throw new InvalidArgumentException('The evidence entity must belong to the active team.');
        }

        $values = Arr::only($attributes, $entity->getFillable());
        ($this->validator ?? new CreateEvidenceEntity())->validate(array_merge($entity->toArray(), $values), $entity::class);

        DB::transaction(function () use ($entity, $values): void {
            $entity->fill($values)->save();
        });

        return $entity->refresh();
    }
}
