<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Gamification;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Liberu\Genealogy\Gamification\Actions\AwardPoints;
use Liberu\Genealogy\Gamification\Models\Achievement;
use Liberu\Genealogy\Gamification\Models\UserAchievement;
use Liberu\Genealogy\Gamification\Models\UserPoint;
use Liberu\Genealogy\Gamification\Models\UserProgress;
use Liberu\Genealogy\GenealogyCore\TeamContext;

final class GamificationService
{
    public function awardPoints(Model $user, string $activityType, int $points, ?string $description = null, array $metadata = [], ?Model $related = null): UserPoint
    {
        return app(AwardPoints::class)->execute($user, $activityType, $points, $description, $metadata, $related);
    }

    /** @return array{total_points: int, achievements_count: int, leaderboard_rank: int|null, team_id: string} */
    public function stats(Model $user): array
    {
        $teamId = app(TeamContext::class)->require();
        $total = (int) UserPoint::query()->where('user_id', $user->getKey())->sum('points');
        $achievements = UserAchievement::query()->where('user_id', $user->getKey())->whereNotNull('unlocked_at')->count();
        $rank = null;
        foreach ($this->leaderboard(0) as $index => $row) {
            if ((string) $row['user_id'] === (string) $user->getKey()) {
                $rank = $index + 1;
                break;
            }
        }

        return ['total_points' => $total, 'achievements_count' => $achievements, 'leaderboard_rank' => $rank, 'team_id' => $teamId];
    }

    /** @return list<array{user_id: mixed, points: int}> */
    public function leaderboard(int $limit = 50, string $period = 'all_time'): array
    {
        $query = UserPoint::query()->select('user_id', DB::raw('SUM(points) as points'))->groupBy('user_id')->orderByDesc('points');
        if ($period !== 'all_time') {
            $query->where('created_at', '>=', match ($period) {
                'today' => now()->startOfDay(),
                'week' => now()->startOfWeek(),
                'month' => now()->startOfMonth(),
                default => now()->startOfDay(),
            });
        }
        if ($limit > 0) {
            $query->limit(min($limit, 100));
        }

        return $query->get()->map(fn (Model $row): array => ['user_id' => $row->user_id, 'points' => (int) $row->points])->values()->all();
    }

    public function unlock(Model $user, Achievement $achievement, array $progressData = []): UserAchievement
    {
        $teamId = app(TeamContext::class)->require();
        $record = UserAchievement::query()->firstOrCreate(
            ['team_id' => $teamId, 'user_id' => $user->getKey(), 'achievement_id' => $achievement->getKey()],
            ['unlocked_at' => now(), 'progress_data' => $progressData],
        );
        if ($record->wasRecentlyCreated) {
            UserProgress::query()->where('user_id', $user->getKey())->where('achievement_id', $achievement->getKey())->delete();
            event(new Events\AchievementUnlocked($record));
        }

        return $record;
    }

    public function updateProgress(Model $user, Achievement $achievement, int $currentProgress, int $targetProgress, array $progressData = []): UserProgress
    {
        $teamId = app(TeamContext::class)->require();
        $progress = UserProgress::query()->firstOrNew([
            'team_id' => $teamId,
            'user_id' => $user->getKey(),
            'achievement_id' => $achievement->getKey(),
        ]);
        $progress->target_progress = max(0, $targetProgress);
        if ($progress->started_at === null) {
            $progress->started_at = now();
        }
        $progress->setProgress($currentProgress, $progressData);

        return $progress->fresh();
    }
}
