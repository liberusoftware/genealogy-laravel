<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Dna\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Liberu\Genealogy\GenealogyCore\Concerns\BelongsToTeam;

final class DnaSegment extends Model
{
    use BelongsToTeam;
    use HasUuids;

    protected $table = 'genealogy_dna_segments';

    protected $fillable = ['team_id', 'match_id', 'chromosome', 'start_position', 'end_position', 'centimorgans', 'snps', 'side', 'metadata'];

    protected function casts(): array
    {
        return ['chromosome' => 'integer', 'start_position' => 'integer', 'end_position' => 'integer', 'centimorgans' => 'decimal:2', 'snps' => 'integer', 'metadata' => 'array'];
    }

    public function match(): BelongsTo
    {
        return $this->belongsTo(DnaMatch::class, 'match_id');
    }
}
