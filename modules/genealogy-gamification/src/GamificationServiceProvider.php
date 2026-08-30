<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Gamification;

use Illuminate\Support\ServiceProvider;

final class GamificationServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }

    public function register(): void
    {
        $this->app->singleton(GamificationService::class);
        $this->app->singleton(Capability::class, fn (): Capability => new Capability(
            'genealogy-gamification',
            'Genealogy Gamification',
            ['genealogy.gamification', 'genealogy.gamification.points', 'genealogy.gamification.achievements', 'genealogy.gamification.progress', 'genealogy.gamification.leaderboard', 'genealogy.gamification.lifecycle'],
        ));
    }
}
