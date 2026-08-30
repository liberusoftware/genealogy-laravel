<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Dna\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Liberu\Genealogy\GenealogyCore\Concerns\BelongsToTeam;
use Liberu\Genealogy\People\Models\Person;

final class DnaKit extends Model
{
    public const CONSENT_STATUSES = ['pending', 'granted', 'revoked'];

    public const STATUSES = ['draft', 'active', 'completed'];

    use BelongsToTeam;
    use HasUuids;
    use SoftDeletes;

    protected $table = 'genealogy_dna_kits';

    protected $fillable = ['team_id', 'name', 'provider', 'provider_id', 'external_id', 'person_id', 'test_type', 'consent_status', 'consented_at', 'revoked_at', 'revocation_reason', 'status', 'metadata', 'file_path', 'file_hash', 'file_format', 'snp_count'];

    protected function casts(): array
    {
        return ['consented_at' => 'datetime', 'revoked_at' => 'datetime', 'metadata' => 'array', 'snp_count' => 'integer'];
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    public function dnaProvider(): BelongsTo
    {
        return $this->belongsTo(DnaProvider::class, 'provider_id');
    }

    public function matches(): HasMany
    {
        return $this->hasMany(DnaMatch::class, 'kit_id');
    }

    public function consents(): HasMany
    {
        return $this->hasMany(DnaConsent::class, 'kit_id');
    }
}
