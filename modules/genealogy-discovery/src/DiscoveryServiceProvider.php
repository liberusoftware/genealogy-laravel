<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Discovery;

use Illuminate\Support\ServiceProvider;
use Liberu\Genealogy\Discovery\Services\SmartMatchingService;
use Liberu\Genealogy\Discovery\Services\SocialFamilyDiscovery;

final class DiscoveryServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }

    public function register(): void
    {
        $this->app->singleton(SocialFamilyDiscovery::class);
        $this->app->singleton(SmartMatchingService::class);
        $this->app->singleton(Capability::class, fn (): Capability => new Capability(
            'genealogy-discovery',
            'Genealogy Discovery',
            ['genealogy.discovery', 'genealogy.discovery.search', 'genealogy.discovery.hints', 'genealogy.discovery.duplicates', 'genealogy.discovery.relationship-paths', 'genealogy.discovery.privacy-indexes', 'genealogy.discovery.social-family', 'genealogy.discovery.smart-matching', 'genealogy.discovery.lifecycle'],
        ));
    }
}
