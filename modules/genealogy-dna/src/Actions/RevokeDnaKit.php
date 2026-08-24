<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Dna\Actions;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Liberu\Genealogy\Dna\Models\DnaConsent;
use Liberu\Genealogy\Dna\Models\DnaKit;

final class RevokeDnaKit
{
    public function execute(DnaKit $kit, string $reason): DnaKit
    {
        if (trim($reason) === '') {
            throw ValidationException::withMessages(['reason' => 'A revocation reason is required.']);
        }

        return DB::transaction(function () use ($kit, $reason): DnaKit {
            $now = Carbon::now();
            DnaConsent::query()->where('kit_id', $kit->getKey())->where('granted', true)->whereNull('revoked_at')->update(['granted' => false, 'revoked_at' => $now, 'revocation_reason' => $reason]);
            $kit->update(['consent_status' => 'revoked', 'revoked_at' => $now, 'revocation_reason' => $reason, 'status' => 'revoked']);

            return $kit->refresh();
        });
    }
}
