<?php

namespace Liberu\Foundation\Audit;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Liberu\Foundation\Audit\Contracts\AuditRecorder;
use Liberu\Foundation\Audit\Support\DatabaseAuditRecorder;
use Liberu\Foundation\Audit\Support\ModelAuditListener;

final class AuditServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(AuditRecorder::class, DatabaseAuditRecorder::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        Event::listen('eloquent.*: *', [ModelAuditListener::class, 'handle']);
    }
}
