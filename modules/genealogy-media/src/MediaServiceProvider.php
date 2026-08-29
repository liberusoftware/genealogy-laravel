<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Media;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Liberu\Genealogy\GenealogyCore\Policies\TeamOwnedPolicy;
use Liberu\Genealogy\Media\Models\MediaAsset;
use Liberu\Genealogy\Media\Models\MediaFaceTag;
use Liberu\Genealogy\Media\Models\MediaLink;

final class MediaServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        Gate::policy(MediaAsset::class, TeamOwnedPolicy::class);
        Gate::policy(MediaLink::class, TeamOwnedPolicy::class);
        Gate::policy(MediaFaceTag::class, TeamOwnedPolicy::class);
    }

    public function register(): void
    {
        $this->app->singleton(Capability::class, fn (): Capability => new Capability(
            'genealogy-media',
            'Genealogy Media',
            ['genealogy.media', 'genealogy.media.documents', 'genealogy.media.photographs', 'genealogy.media.audio-video', 'genealogy.media.transcription', 'genealogy.media.rights', 'genealogy.media.links', 'genealogy.media.preservation', 'genealogy.media.lifecycle'],
        ));
    }
}
