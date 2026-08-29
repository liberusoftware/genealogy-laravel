<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Collaboration\Actions;

use Illuminate\Support\Arr;
use InvalidArgumentException;
use Liberu\Genealogy\Collaboration\Models\CollaborationAttribution;
use Liberu\Genealogy\GenealogyCore\TeamContext;

final class RecordCollaborationAttribution
{
    public function execute(string $type, string $id, string $action, array $metadata = [], string|int|null $actorId = null): CollaborationAttribution
    {
        $type = trim($type);
        $id = trim($id);
        $action = trim($action);
        if ($type === '' || $id === '' || $action === '') {
            throw new InvalidArgumentException('Attribution type, identifier, and action are required.');
        }

        return CollaborationAttribution::query()->create([
            'team_id' => app(TeamContext::class)->require(),
            'actor_id' => $actorId ?? (function_exists('auth') && auth()->check() ? auth()->id() : null),
            'attributable_type' => $type,
            'attributable_id' => $id,
            'action' => $action,
            'metadata' => Arr::where($metadata, static fn (mixed $value): bool => is_scalar($value) || is_array($value)),
            'created_at' => now(),
        ]);
    }
}
