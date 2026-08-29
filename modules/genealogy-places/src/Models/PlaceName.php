<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Places\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Liberu\Genealogy\GenealogyCore\Concerns\BelongsToTeam;

final class PlaceName extends Model
{
    use BelongsToTeam;
    use HasUuids;
    use SoftDeletes;

    protected $table = 'genealogy_place_names';

    protected $fillable = ['team_id', 'place_id', 'name', 'type', 'locale', 'valid_from', 'valid_to', 'metadata'];

    protected function casts(): array
    {
        return ['valid_from' => 'date', 'valid_to' => 'date', 'metadata' => 'array'];
    }

    public function place(): BelongsTo
    {
        return $this->belongsTo(Place::class);
    }
}
