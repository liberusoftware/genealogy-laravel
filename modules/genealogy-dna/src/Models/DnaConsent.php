<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Dna\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Liberu\Genealogy\GenealogyCore\Concerns\BelongsToTeam;

final class DnaConsent extends Model
{
    use BelongsToTeam;
    use HasUuids;

    protected $table = 'genealogy_dna_consents';

    protected $fillable = ['team_id', 'kit_id', 'scope', 'granted', 'policy_version', 'granted_at', 'revoked_at', 'revocation_reason', 'metadata'];

    protected function casts(): array
    {
        return ['granted' => 'boolean', 'granted_at' => 'datetime', 'revoked_at' => 'datetime', 'metadata' => 'array'];
    }

    public function kit(): BelongsTo
    {
        return $this->belongsTo(DnaKit::class, 'kit_id');
    }
}
