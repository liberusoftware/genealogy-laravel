<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Collaboration\Actions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\UniqueConstraintViolationException;
use Liberu\Genealogy\Collaboration\Models\VirtualEvent;
use Liberu\Genealogy\Collaboration\Models\VirtualEventAttendee;

final class RsvpToVirtualEvent
{
    public function execute(VirtualEvent $event, Model $user, string $status, ?string $notes = null): VirtualEventAttendee
    {
        if (! in_array($status, ['accepted', 'declined', 'maybe'], true)) {
            throw new \InvalidArgumentException('RSVP status must be accepted, declined, or maybe.');
        }
        if ($event->status !== 'published' || $event->start_time <= now()) {
            throw new \DomainException('This event is not accepting RSVPs.');
        }
        $attendee = $event->attendees()->where('user_id', $user->getKey())->first();
        if ($attendee === null && $status === 'accepted' && $event->isAtCapacity()) {
            throw new \DomainException('This event is at capacity.');
        }

        if ($attendee === null) {
            try {
                return VirtualEventAttendee::query()->create(['virtual_event_id' => $event->getKey(), 'user_id' => $user->getKey(), 'rsvp_status' => $status, 'rsvp_date' => now(), 'rsvp_notes' => $notes]);
            } catch (UniqueConstraintViolationException) {
                $attendee = $event->attendees()->where('user_id', $user->getKey())->firstOrFail();
            }
        }
        $attendee->update(['rsvp_status' => $status, 'rsvp_date' => now(), 'rsvp_notes' => $notes]);

        return $attendee->fresh();
    }
}
