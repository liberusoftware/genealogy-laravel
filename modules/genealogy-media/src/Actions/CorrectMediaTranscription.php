<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Media\Actions;

use InvalidArgumentException;
use Liberu\Genealogy\GenealogyCore\TeamContext;
use Liberu\Genealogy\Media\Models\MediaAsset;
use Liberu\Genealogy\Media\Models\MediaTranscriptionCorrection;

final class CorrectMediaTranscription
{
    public function execute(MediaAsset $asset, string $correctedText, ?string $actorId = null): MediaTranscriptionCorrection
    {
        if (app()->bound(TeamContext::class) && (string) $asset->team_id !== app(TeamContext::class)->require()) {
            throw new InvalidArgumentException('The media asset must belong to the active team.');
        }
        if (trim($correctedText) === '') {
            throw new InvalidArgumentException('A transcription correction cannot be empty.');
        }

        $original = (string) $asset->transcription;
        $correction = $asset->getConnection()->transaction(function () use ($asset, $correctedText, $actorId, $original): MediaTranscriptionCorrection {
            $correction = MediaTranscriptionCorrection::query()->create([
                'team_id' => $asset->team_id,
                'media_asset_id' => $asset->getKey(),
                'original_text' => $original,
                'corrected_text' => $correctedText,
                'actor_id' => $actorId,
                'metadata' => ['corrected_at' => now()->toISOString()],
            ]);
            $asset->update(['transcription' => $correctedText, 'transcription_status' => 'completed']);

            return $correction;
        });

        return $correction;
    }
}
