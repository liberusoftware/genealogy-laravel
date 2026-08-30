<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Gamification\Actions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Liberu\Genealogy\Gamification\Models\UserPoint;
use Liberu\Genealogy\GenealogyCore\TeamContext;

final class AwardPoints
{
    public function execute(Model $user, string $activityType, int $points, ?string $description = null, array $metadata = [], ?Model $related = null): UserPoint
    {
        $teamId = app(TeamContext::class)->require();
        if ($points <= 0) {
            throw new InvalidArgumentException('Awarded points must be greater than zero.');
        }
        if (trim($activityType) === '') {
            throw new InvalidArgumentException('An activity type is required.');
        }

        return DB::transaction(fn (): UserPoint => UserPoint::query()->create([
            'team_id' => $teamId,
            'user_id' => $user->getKey(),
            'activity_type' => trim($activityType),
            'points' => $points,
            'description' => $description,
            'metadata' => $metadata,
            'related_id' => $related?->getKey(),
            'related_type' => $related?->getMorphClass(),
        ]));
    }
}
