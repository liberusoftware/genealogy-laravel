<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Liberu\Foundation\Identity\Socialstream\Models\ConnectedAccount;
use Liberu\Foundation\Organizations\Models\Team;
use Liberu\Genealogy\Collaboration\Actions\RsvpToVirtualEvent;
use Liberu\Genealogy\Collaboration\Contracts\VideoConferencingProvider;
use Liberu\Genealogy\Collaboration\Models\VirtualEvent;
use Liberu\Genealogy\Collaboration\Services\VideoConferencingService;
use Liberu\Genealogy\Discovery\Contracts\ExternalRecordProvider;
use Liberu\Genealogy\Discovery\Models\SmartMatch;
use Liberu\Genealogy\Discovery\Models\SocialFamilyConnection;
use Liberu\Genealogy\Discovery\Services\SmartMatchingService;
use Liberu\Genealogy\Discovery\Services\SocialFamilyDiscovery;
use Liberu\Genealogy\GenealogyCore\TeamContext;
use Liberu\Genealogy\People\Models\Person;
use Liberu\Genealogy\Research\Actions\CreateChecklist;
use Liberu\Genealogy\Research\Models\ChecklistTemplate;
use Liberu\Genealogy\Research\Models\ChecklistTemplateItem;
use Liberu\Genealogy\Timeline\Models\HistoricalEvent;
use Liberu\Genealogy\Timeline\Services\HistoricalEventService;

uses(RefreshDatabase::class);

it('creates a team-scoped research checklist from a template and tracks completion', function (): void {
    $user = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $user->id]);
    app(TeamContext::class)->set($team->getKey());

    $template = ChecklistTemplate::query()->create([
        'created_by' => $user->getKey(),
        'name' => 'Birth record research',
        'category' => 'records',
        'is_public' => true,
    ]);
    ChecklistTemplateItem::query()->create([
        'checklist_template_id' => $template->getKey(),
        'title' => 'Search the civil register',
        'sort_order' => 1,
        'is_required' => true,
    ]);

    $checklist = app(CreateChecklist::class)->execute($user, 'My birth record search', $template);
    $item = $checklist->items()->firstOrFail();
    $item->markAsCompleted(30);

    expect($checklist->fresh()->status)->toBe('completed')
        ->and($checklist->fresh()->completion_percentage)->toBe(100.0)
        ->and($item->fresh()->actual_time)->toBe(30);
});

it('supports published team virtual event RSVPs with capacity enforcement', function (): void {
    $user = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $user->id]);
    app(TeamContext::class)->set($team->getKey());

    $event = VirtualEvent::query()->create([
        'created_by' => $user->getKey(),
        'title' => 'Family history gathering',
        'status' => 'published',
        'start_time' => now()->addWeek(),
        'end_time' => now()->addWeek()->addHours(2),
        'max_attendees' => 1,
    ]);

    $attendee = app(RsvpToVirtualEvent::class)->execute($event, $user, 'accepted', 'Looking forward to it');

    expect($attendee->rsvp_status)->toBe('accepted')
        ->and($event->fresh()->isAtCapacity())->toBeTrue();
});

it('fetches historical context by period, country, and a persons life dates', function (): void {
    $user = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $user->id]);
    app(TeamContext::class)->set($team->getKey());

    $early = HistoricalEvent::query()->create([
        'title' => 'Early event',
        'event_date' => '1900-06-01',
        'year' => 1900,
        'country' => 'GB',
    ]);
    HistoricalEvent::query()->create([
        'title' => 'Other country',
        'event_date' => '1905-06-01',
        'year' => 1905,
        'country' => 'US',
    ]);
    $person = Person::query()->create([
        'given_name' => 'Ada',
        'family_name' => 'Example',
        'birth_date' => '1899-01-01',
        'death_date' => '1901-12-31',
    ]);

    $service = app(HistoricalEventService::class);

    expect($service->fetchForPeriod('1899-01-01', '1901-12-31', 'GB')->modelKeys())
        ->toContain($early->getKey())
        ->and($service->fetchForPerson($person, 1)->modelKeys())->toContain($early->getKey());
});

it('provides opt-in social family discovery with privacy and connection lifecycle', function (): void {
    $user = User::factory()->create();
    $account = ConnectedAccount::factory()->create(['user_id' => $user->getKey(), 'name' => 'Family Researcher']);
    $service = app(SocialFamilyDiscovery::class);

    expect($service->enable($account))->toBeTrue()
        ->and($account->fresh()->enable_family_matching)->toBeTrue()
        ->and($account->fresh()->cached_profile_data['name'])->toBe('Family Researcher')
        ->and($service->needsSync($account->fresh()))->toBeFalse();

    $privacy = $service->updatePrivacy($user->getKey(), ['allow_family_discovery' => false]);
    $connection = SocialFamilyConnection::query()->create([
        'user_id' => $user->getKey(),
        'connected_account_id' => $account->getKey(),
        'matched_social_id' => 'match-1',
        'confidence_score' => 82,
    ]);
    $connection->accept();

    expect($privacy->allow_family_discovery)->toBeFalse()
        ->and($connection->fresh()->isPending())->toBeFalse()
        ->and($connection->fresh()->status)->toBe('accepted');
});

it('scores configured smart-matching providers and persists their candidates', function (): void {
    $user = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $user->id]);
    app(TeamContext::class)->set($team->getKey());
    $person = Person::query()->create(['given_name' => 'Ada', 'family_name' => 'Lovelace', 'birth_date' => '1815-12-10']);
    $provider = new class() implements ExternalRecordProvider
    {
        public function key(): string
        {
            return 'archive';
        }

        public function isAvailable(): bool
        {
            return true;
        }

        public function search(array $person): array
        {
            return [['id' => 'record-1', 'first_name' => 'Ada', 'last_name' => 'Lovelace', 'birth_date' => '1815-12-10']];
        }
    };

    $service = app(SmartMatchingService::class);
    $matches = $service->findMatches($person, [$provider]);
    $persisted = $service->persist($user, $person, $matches);

    expect($matches[0]['confidence_score'])->toBe(100)
        ->and($persisted)->toHaveCount(1)
        ->and(SmartMatch::query()->first()->match_source)->toBe('archive');
});

it('orchestrates meeting creation through the configured event platform provider', function (): void {
    $user = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $user->id]);
    app(TeamContext::class)->set($team->getKey());
    $event = VirtualEvent::query()->create([
        'created_by' => $user->getKey(),
        'title' => 'Online family gathering',
        'platform' => 'archive-meet',
        'start_time' => now()->addWeek(),
        'end_time' => now()->addWeek()->addHours(2),
    ]);
    $provider = new class() implements VideoConferencingProvider
    {
        public function key(): string
        {
            return 'archive-meet';
        }

        public function isAvailable(): bool
        {
            return true;
        }

        public function createMeeting(array $meetingData): array
        {
            return ['meeting_id' => 'meeting-1', 'join_url' => 'https://example.test/meeting-1'];
        }

        public function updateMeeting(array $meetingData): array
        {
            return [];
        }

        public function deleteMeeting(string $meetingId): bool
        {
            return true;
        }

        public function meetingDetails(string $meetingId): ?array
        {
            return null;
        }

        public function attendees(string $meetingId): array
        {
            return [];
        }

        public function sendInvitations(string $meetingId, array $emails): bool
        {
            return true;
        }
    };

    $result = app(VideoConferencingService::class)->createMeeting($event, [$provider]);

    expect($result['meeting_id'])->toBe('meeting-1')
        ->and($event->fresh()->join_url)->toBe('https://example.test/meeting-1');
});
