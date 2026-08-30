<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Collaboration\Services;

use Illuminate\Support\Collection;
use Liberu\Genealogy\Collaboration\Contracts\VideoConferencingProvider;
use Liberu\Genealogy\Collaboration\Models\VirtualEvent;

final class VideoConferencingService
{
    /** @param iterable<VideoConferencingProvider> $providers */
    public function createMeeting(VirtualEvent $event, iterable $providers): ?array
    {
        $provider = $this->provider($event->platform, $providers);
        if ($provider === null) {
            return null;
        }

        $result = $provider->createMeeting([
            'title' => $event->title, 'description' => $event->description, 'start_time' => $event->start_time,
            'end_time' => $event->end_time, 'timezone' => $event->timezone, 'host_email' => $event->host_email,
            'max_attendees' => $event->max_attendees, 'require_password' => true,
        ]);
        $event->update([
            'meeting_id' => $result['meeting_id'] ?? null, 'meeting_password' => $result['password'] ?? null,
            'meeting_url' => $result['meeting_url'] ?? null, 'join_url' => $result['join_url'] ?? null,
            'platform_data' => $result['platform_data'] ?? [],
        ]);

        return $result;
    }

    /** @param iterable<VideoConferencingProvider> $providers */
    public function updateMeeting(VirtualEvent $event, iterable $providers): ?array
    {
        $provider = $this->provider($event->platform, $providers);
        if ($provider === null || $event->meeting_id === null) {
            return null;
        }

        $result = $provider->updateMeeting(['meeting_id' => $event->meeting_id, 'title' => $event->title, 'description' => $event->description, 'start_time' => $event->start_time, 'end_time' => $event->end_time, 'timezone' => $event->timezone, 'max_attendees' => $event->max_attendees]);
        $event->update(['meeting_url' => $result['meeting_url'] ?? $event->meeting_url, 'join_url' => $result['join_url'] ?? $event->join_url, 'platform_data' => array_merge($event->platform_data ?? [], $result['platform_data'] ?? [])]);

        return $result;
    }

    /** @param iterable<VideoConferencingProvider> $providers */
    public function deleteMeeting(VirtualEvent $event, iterable $providers): bool
    {
        $provider = $this->provider($event->platform, $providers);
        if ($provider === null || $event->meeting_id === null || ! $provider->deleteMeeting($event->meeting_id)) {
            return false;
        }
        $event->update(['meeting_id' => null, 'meeting_password' => null, 'meeting_url' => null, 'join_url' => null, 'platform_data' => null]);

        return true;
    }

    /** @param iterable<VideoConferencingProvider> $providers */
    public function availablePlatforms(iterable $providers): Collection
    {
        return collect($providers)->filter(fn (VideoConferencingProvider $provider): bool => $provider->isAvailable())->mapWithKeys(fn (VideoConferencingProvider $provider): array => [$provider->key() => true]);
    }

    /** @param iterable<VideoConferencingProvider> $providers */
    private function provider(string $platform, iterable $providers): ?VideoConferencingProvider
    {
        foreach ($providers as $provider) {
            if ($provider->key() === $platform && $provider->isAvailable()) {
                return $provider;
            }
        }

        return null;
    }
}
