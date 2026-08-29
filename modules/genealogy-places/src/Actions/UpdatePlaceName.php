<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Places\Actions;

use Illuminate\Support\Arr;
use InvalidArgumentException;
use Liberu\Genealogy\GenealogyCore\TeamContext;
use Liberu\Genealogy\Places\Events\PlaceNameUpdated;
use Liberu\Genealogy\Places\Models\PlaceName;

final class UpdatePlaceName
{
    /** @param array<string, mixed> $attributes */
    public function execute(PlaceName $placeName, array $attributes): PlaceName
    {
        if ((string) $placeName->team_id !== app(TeamContext::class)->require()) {
            throw new InvalidArgumentException('The place name must belong to the active team.');
        }
        $values = Arr::only($attributes, ['name', 'type', 'locale', 'valid_from', 'valid_to', 'metadata']);
        if (array_key_exists('name', $values)) {
            $values['name'] = trim((string) $values['name']);
        }
        (new CreatePlaceName())->validate(array_merge($placeName->toArray(), $values));

        $placeName->getConnection()->transaction(function () use ($placeName, $values): void {
            $placeName->update($values);
        });

        $placeName = $placeName->refresh();
        if (app()->bound('events')) {
            event(new PlaceNameUpdated($placeName));
        }

        return $placeName;
    }
}
