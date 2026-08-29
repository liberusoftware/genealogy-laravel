<?php

declare(strict_types=1);

namespace Liberu\Foundation\Audit\Support;

use Illuminate\Database\Eloquent\Model;
use Liberu\Foundation\Audit\Contracts\AuditRecorder;

final class ModelAuditListener
{
    public function handle(string $event, array $payload): void
    {
        $model = $payload[0] ?? null;

        if (! $model instanceof Model || ! str_starts_with($model->getTable(), 'genealogy_')) {
            return;
        }

        $operation = str_contains($event, '.created:')
            ? 'created'
            : (str_contains($event, '.updated:') ? 'updated' : (str_contains($event, '.deleted:') ? 'deleted' : null));

        if ($operation === null) {
            return;
        }
        if (! app()->bound(AuditRecorder::class)) {
            return;
        }

        $before = $operation === 'created' ? [] : $model->getOriginal();
        $after = $operation === 'deleted' ? [] : $model->getAttributes();
        $request = app()->bound('request') ? request() : null;
        $actor = app()->bound('auth') ? auth()->user() : null;
        $tenantId = $model->getAttribute('team_id');

        app(AuditRecorder::class)->record(
            'genealogy_'.$operation,
            $model->getMorphClass(),
            $model->getKey(),
            $before,
            $after,
            new AuditContext(
                $actor?->getAuthIdentifier(),
                $actor !== null ? $actor->getMorphClass() : null,
                $tenantId !== null ? (string) $tenantId : null,
                $request?->header('X-Request-ID'),
                $request?->header('X-Correlation-ID') ?? $request?->header('X-Request-ID'),
            ),
        );
    }
}
