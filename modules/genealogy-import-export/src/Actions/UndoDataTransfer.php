<?php

declare(strict_types=1);

namespace Liberu\Genealogy\ImportExport\Actions;

use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Liberu\Foundation\Audit\Contracts\AuditRecorder;
use Liberu\Foundation\Audit\Support\AuditContext;
use Liberu\Genealogy\GenealogyCore\TeamContext;
use Liberu\Genealogy\ImportExport\Events\DataTransferUndone;
use Liberu\Genealogy\ImportExport\Models\DataTransfer;
use Liberu\Genealogy\People\Models\Person;
use Liberu\Genealogy\Relationships\Models\Relationship;

final class UndoDataTransfer
{
    public function execute(DataTransfer $transfer): DataTransfer
    {
        if ((string) $transfer->team_id !== app(TeamContext::class)->require()) {
            throw new InvalidArgumentException('The data transfer must belong to the active team.');
        }

        $metadata = $transfer->metadata ?? [];
        $undo = $metadata['undo'] ?? null;
        if ($transfer->status !== 'completed' || ! is_array($undo)) {
            throw new InvalidArgumentException('This data transfer has no available undo operation.');
        }

        $expiresAt = $undo['expires_at'] ?? null;
        if (! is_string($expiresAt) || now()->greaterThan($expiresAt)) {
            throw new InvalidArgumentException('The undo window for this data transfer has expired.');
        }

        $updatedPeople = is_array($undo['updated_people'] ?? null) ? $undo['updated_people'] : [];
        $createdPeople = is_array($undo['created_people'] ?? null) ? $undo['created_people'] : [];
        $createdRelationships = is_array($undo['created_relationships'] ?? null) ? $undo['created_relationships'] : [];
        $before = $transfer->only(['id', 'status', 'metadata']);

        DB::transaction(function () use ($transfer, $metadata, $updatedPeople, $createdPeople, $createdRelationships): void {
            Relationship::query()
                ->where('team_id', app(TeamContext::class)->require())
                ->whereIn('id', $createdRelationships)
                ->delete();

            foreach ($updatedPeople as $snapshot) {
                if (! is_array($snapshot) || ! isset($snapshot['id'], $snapshot['attributes']) || ! is_array($snapshot['attributes'])) {
                    continue;
                }

                $person = Person::withTrashed()
                    ->where('team_id', app(TeamContext::class)->require())
                    ->find($snapshot['id']);
                if ($person !== null) {
                    $person->fill($snapshot['attributes']);
                    $person->save();
                }
            }

            foreach ($createdPeople as $personId) {
                $person = Person::withTrashed()
                    ->where('team_id', app(TeamContext::class)->require())
                    ->find($personId);
                if ($person === null) {
                    continue;
                }

                $person->names()->delete();
                $person->identities()->delete();
                $person->lifeEvents()->delete();
                $person->mergeCandidates()->delete();
                $person->forceDelete();
            }

            $metadata['undo']['undone_at'] = now()->toISOString();
            $metadata['undo']['created_people'] = [];
            $metadata['undo']['updated_people'] = [];
            $metadata['undo']['created_relationships'] = [];
            $transfer->update(['status' => 'rolled_back', 'metadata' => $metadata]);
        });

        $transfer = $transfer->refresh();
        event(new DataTransferUndone($transfer));
        $this->recordAudit($before, $transfer);

        return $transfer;
    }

    /** @param array<string, mixed> $before */
    private function recordAudit(array $before, DataTransfer $transfer): void
    {
        if (! app()->bound(AuditRecorder::class)) {
            return;
        }

        $request = app()->bound('request') ? request() : null;
        $actor = auth()->user();
        $teamId = app(TeamContext::class)->current();

        app(AuditRecorder::class)->record(
            'data_transfer_undone',
            $transfer->getMorphClass(),
            $transfer->getKey(),
            $before,
            $transfer->only(['id', 'status', 'metadata']),
            new AuditContext(
                $actor?->getAuthIdentifier(),
                $actor !== null ? $actor->getMorphClass() : null,
                $teamId !== null ? (string) $teamId : null,
                $request?->header('X-Request-ID'),
                $request?->header('X-Correlation-ID') ?? $request?->header('X-Request-ID'),
                'Import recovery requested by the owning team.',
            ),
        );
    }
}
