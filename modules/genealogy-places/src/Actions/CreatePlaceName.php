<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Places\Actions;

use Illuminate\Support\Arr;
use InvalidArgumentException;
use Liberu\Genealogy\GenealogyCore\TeamContext;
use Liberu\Genealogy\Places\Events\PlaceNameCreated;
use Liberu\Genealogy\Places\Models\Place;
use Liberu\Genealogy\Places\Models\PlaceName;

final class CreatePlaceName
{
    public function execute(array $attributes): PlaceName
    {
        $values = Arr::only($attributes, ['place_id', 'name', 'type', 'locale', 'valid_from', 'valid_to', 'metadata']);
        $this->validate($values);
        $values['name'] = trim((string) $values['name']);

        $model = PlaceName::query()->getModel();
        $schema = $model->getConnection()->getSchemaBuilder();
        $values = Arr::only($values, $schema->getColumnListing('genealogy_place_names'));
        if ($schema->hasColumn('genealogy_place_names', 'team_id')) {
            $values['team_id'] = app(TeamContext::class)->require();
        }

        $placeName = $model->getConnection()->transaction(function () use ($values): PlaceName {
            $placeName = PlaceName::query()->create($values);

            return $placeName;
        });

        if (app()->bound('events')) {
            event(new PlaceNameCreated($placeName));
        }

        return $placeName;
    }

    public function validate(array $values): void
    {
        if (trim((string) ($values['name'] ?? '')) === '') {
            throw new InvalidArgumentException('A place name is required.');
        }

        if (! Place::query()->whereKey($values['place_id'] ?? null)->exists()) {
            throw new InvalidArgumentException('The named place must belong to the active team.');
        }

        if (isset($values['valid_from'], $values['valid_to']) && $values['valid_to'] < $values['valid_from']) {
            throw new InvalidArgumentException('A place name end date cannot precede its start date.');
        }
    }
}
