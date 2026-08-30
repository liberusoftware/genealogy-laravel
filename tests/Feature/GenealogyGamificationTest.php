<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Liberu\Foundation\Organizations\Models\Team;
use Liberu\Genealogy\Gamification\Events\AchievementUnlocked;
use Liberu\Genealogy\Gamification\GamificationService;
use Liberu\Genealogy\Gamification\Models\Achievement;
use Liberu\Genealogy\Gamification\Models\UserAchievement;
use Liberu\Genealogy\Gamification\Models\UserProgress;
use Liberu\Genealogy\GenealogyCore\TeamContext;

uses(RefreshDatabase::class);

it('awards team-scoped points and calculates user stats', function (): void {
    $user = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $user->id]);
    app(TeamContext::class)->set($team->getKey());

    $point = app(GamificationService::class)->awardPoints($user, 'person_added', 25, 'Added an ancestor');
    $stats = app(GamificationService::class)->stats($user);

    expect($point->team_id)->toBe((string) $team->getKey())
        ->and($point->points)->toBe(25)
        ->and($stats)->toMatchArray([
            'total_points' => 25,
            'achievements_count' => 0,
            'leaderboard_rank' => 1,
            'team_id' => (string) $team->getKey(),
        ]);
});

it('keeps the leaderboard isolated to the active team and unlocks achievements once', function (): void {
    Event::fake([AchievementUnlocked::class]);

    $user = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $user->id]);
    $otherUser = User::factory()->create();
    $otherTeam = Team::factory()->create(['user_id' => $otherUser->id]);
    $achievement = Achievement::query()->create([
        'key' => 'first-person',
        'name' => 'First Person',
        'description' => 'Add a person to a family tree.',
        'points' => 10,
        'requirements' => ['people_added' => 1],
        'is_active' => true,
    ]);

    app(TeamContext::class)->set($team->getKey());
    $service = app(GamificationService::class);
    $service->awardPoints($user, 'person_added', 10);
    $service->unlock($user, $achievement, ['people_added' => 1]);
    $service->unlock($user, $achievement, ['people_added' => 2]);

    app(TeamContext::class)->set($otherTeam->getKey());
    $service->awardPoints($otherUser, 'person_added', 100);
    app(TeamContext::class)->set($team->getKey());

    expect($service->leaderboard())->toBe([['user_id' => $user->getKey(), 'points' => 10]])
        ->and(UserAchievement::query()->count())->toBe(1);

    Event::assertDispatchedTimes(AchievementUnlocked::class, 1);
});

it('tracks achievement progress and removes it when the achievement unlocks', function (): void {
    $user = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $user->id]);
    app(TeamContext::class)->set($team->getKey());
    $achievement = Achievement::query()->create(['key' => 'researcher', 'name' => 'Researcher', 'points' => 20]);
    $service = app(GamificationService::class);

    $progress = $service->updateProgress($user, $achievement, 3, 10, ['source' => 'records']);

    expect($progress)->toBeInstanceOf(UserProgress::class)
        ->and($progress->progressPercentage())->toBe(30.0)
        ->and($progress->isComplete())->toBeFalse()
        ->and(UserProgress::query()->count())->toBe(1);

    $service->unlock($user, $achievement);

    expect(UserProgress::query()->count())->toBe(0);
});
