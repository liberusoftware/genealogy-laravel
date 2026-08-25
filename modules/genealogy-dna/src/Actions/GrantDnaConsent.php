<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Dna\Actions;

use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use Liberu\Genealogy\Dna\Models\DnaConsent;
use Liberu\Genealogy\Dna\Models\DnaKit;

final class GrantDnaConsent
{
    public function execute(DnaKit $kit, string $scope, ?string $policyVersion = null): DnaConsent
    {
        if (trim($scope) === '') {
            throw ValidationException::withMessages(['scope' => 'A consent scope is required.']);
        }

        $consent = DnaConsent::query()->create(['kit_id' => $kit->getKey(), 'scope' => $scope, 'granted' => true, 'policy_version' => $policyVersion, 'granted_at' => Carbon::now()]);
        $kit->update(['consent_status' => 'granted', 'consented_at' => $consent->granted_at, 'revoked_at' => null, 'revocation_reason' => null]);

        return $consent;
    }
}
