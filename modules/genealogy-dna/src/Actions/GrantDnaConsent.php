<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Dna\Actions;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use Liberu\Genealogy\Dna\Models\DnaConsent;
use Liberu\Genealogy\Dna\Models\DnaKit;
use Liberu\Genealogy\GenealogyCore\TeamContext;

final class GrantDnaConsent
{
    public function execute(DnaKit $kit, string $scope, ?string $policyVersion = null): DnaConsent
    {
        $teamId = app(TeamContext::class)->require();

        if ((string) $kit->team_id !== $teamId) {
            throw new InvalidArgumentException('The DNA kit must belong to the active team.');
        }

        if (trim($scope) === '') {
            throw ValidationException::withMessages(['scope' => 'A consent scope is required.']);
        }

        return DB::transaction(function () use ($kit, $scope, $policyVersion, $teamId): DnaConsent {
            $consent = DnaConsent::query()->create([
                'team_id' => $teamId,
                'kit_id' => $kit->getKey(),
                'scope' => trim($scope),
                'granted' => true,
                'policy_version' => $policyVersion,
                'granted_at' => Carbon::now(),
            ]);
            $kit->update(['consent_status' => 'granted', 'consented_at' => $consent->granted_at, 'revoked_at' => null, 'revocation_reason' => null]);

            return $consent;
        });
    }
}
