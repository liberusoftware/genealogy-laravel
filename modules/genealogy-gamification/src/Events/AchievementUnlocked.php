<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Gamification\Events;

use Liberu\Genealogy\Gamification\Models\UserAchievement;

final class AchievementUnlocked
{
    public bool $afterCommit = true;

    public function __construct(public UserAchievement $achievement) {}
}
