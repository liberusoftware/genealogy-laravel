<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Collaboration\Actions;

use Illuminate\Support\Facades\DB;
use Liberu\Genealogy\Collaboration\Models\CollaborationWatch;
use Liberu\Genealogy\GenealogyCore\TeamContext;

final class ToggleCollaborationWatch
{
    public function execute(string $watchableType, string $watchableId, string|int $userId): ?CollaborationWatch
    {
        $teamId = app(TeamContext::class)->require();
        $watch = CollaborationWatch::query()->where([
            'team_id' => $teamId, 'user_id' => $userId, 'watchable_type' => $watchableType, 'watchable_id' => $watchableId,
        ])->first();
        if ($watch !== null) {
            $watch->delete();
            app(RecordCollaborationAttribution::class)->execute($watchableType, $watchableId, 'unwatched', [], $userId);

            return null;
        }

        $watch = DB::transaction(fn (): CollaborationWatch => CollaborationWatch::query()->create([
            'team_id' => $teamId, 'user_id' => $userId, 'watchable_type' => $watchableType, 'watchable_id' => $watchableId,
        ]));
        app(RecordCollaborationAttribution::class)->execute($watchableType, $watchableId, 'watched', [], $userId);

        return $watch;
    }
}
