<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Media;

use Illuminate\Support\ServiceProvider;

final class MediaServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(Capability::class, fn (): Capability => new Capability(
            'genealogy-media',
            'Genealogy Media',
            ['genealogy.media', 'genealogy.media.lifecycle'],
        ));
    }
}
