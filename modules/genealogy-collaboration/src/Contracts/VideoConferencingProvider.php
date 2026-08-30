<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Collaboration\Contracts;

interface VideoConferencingProvider
{
    public function key(): string;

    public function isAvailable(): bool;

    /** @param array<string, mixed> $meetingData */
    /** @return array<string, mixed> */
    public function createMeeting(array $meetingData): array;

    /** @param array<string, mixed> $meetingData */
    /** @return array<string, mixed> */
    public function updateMeeting(array $meetingData): array;

    public function deleteMeeting(string $meetingId): bool;

    /** @return array<string, mixed>|null */
    public function meetingDetails(string $meetingId): ?array;

    /** @return list<array<string, mixed>> */
    public function attendees(string $meetingId): array;

    /** @param list<string> $emails */
    public function sendInvitations(string $meetingId, array $emails): bool;
}
