<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Places\Actions;

use Illuminate\Support\Arr;
use InvalidArgumentException;
use Liberu\Genealogy\GenealogyCore\TeamContext;
use Liberu\Genealogy\Places\Events\PlaceCreated;
use Liberu\Genealogy\Places\Models\Place;

final class CreatePlace
{
    public function execute(array $attributes): Place
    {
        $values = Arr::only($attributes, ['name', 'parent_id', 'historical_names', 'latitude', 'longitude', 'jurisdiction', 'is_current', 'status', 'metadata']);
        $this->validate($values);
        $values['name'] = trim((string) $values['name']);

        $model = Place::query()->getModel();
        $schema = $model->getConnection()->getSchemaBuilder();
        $values = Arr::only($values, $schema->getColumnListing('genealogy_places'));
        if ($schema->hasColumn('genealogy_places', 'team_id')) {
            $values['team_id'] = app(TeamContext::class)->require();
        }

        $place = $model->getConnection()->transaction(function () use ($values): Place {
            $place = Place::query()->create($values);

            return $place;
        });

        if (app()->bound('events')) {
            event(new PlaceCreated($place));
        }

        return $place;
    }

    /** @param array<string, mixed> $values */
    public function validate(array $values): void
    {
        if (trim((string) ($values['name'] ?? '')) === '') {
            throw new InvalidArgumentException('A place name is required.');
        }

        if (isset($values['status']) && ! in_array($values['status'], Place::STATUSES, true)) {
            throw new InvalidArgumentException('The place status is not supported.');
        }

        if (($values['parent_id'] ?? null) !== null && ! Place::query()->whereKey($values['parent_id'])->exists()) {
            throw new InvalidArgumentException('The parent place must belong to the active team.');
        }
        if (($values['latitude'] ?? null) !== null && ((float) $values['latitude'] < -90 || (float) $values['latitude'] > 90)) {
            throw new InvalidArgumentException('Latitude must be between -90 and 90.');
        }
        if (($values['longitude'] ?? null) !== null && ((float) $values['longitude'] < -180 || (float) $values['longitude'] > 180)) {
            throw new InvalidArgumentException('Longitude must be between -180 and 180.');
        }

    }
}
