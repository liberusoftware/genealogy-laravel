<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Timeline\Actions;

use Illuminate\Support\Arr;
use InvalidArgumentException;
use Liberu\Genealogy\GenealogyCore\Contracts\PersonReferenceResolver;
use Liberu\Genealogy\GenealogyCore\TeamContext;
use Liberu\Genealogy\Timeline\Events\TimelineEventUpdated;
use Liberu\Genealogy\Timeline\Models\TimelineEvent;

final class UpdateTimelineEvent
{
    public function __construct(private readonly ?PersonReferenceResolver $personReference = null) {}

    public function execute(TimelineEvent $event, array $attributes): TimelineEvent
    {
        if ((string) $event->team_id !== app(TeamContext::class)->require()) {
            throw new InvalidArgumentException('The timeline event must belong to the active team.');
        }
        $values = Arr::only($attributes, $event->getFillable());
        (new CreateTimelineEvent($this->personReference))->validate(array_merge($event->toArray(), $values));
        if (array_key_exists('name', $values)) {
            $values['name'] = trim((string) $values['name']);
        }
        $event->getConnection()->transaction(function () use ($event, $values): void {
            $event->update($values);
        });

        $event = $event->refresh();
        if (app()->bound('events')) {
            event(new TimelineEventUpdated($event));
        }

        return $event;
    }
}
