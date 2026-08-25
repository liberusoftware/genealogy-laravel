<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Places\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Liberu\Genealogy\GenealogyCore\Concerns\BelongsToTeam;

final class Place extends Model
{
    public const STATUSES = ['draft', 'active', 'completed'];

    use BelongsToTeam;
    use HasUuids;
    use SoftDeletes;

    protected $table = 'genealogy_places';

    protected $fillable = ['team_id', 'name', 'parent_id', 'historical_names', 'latitude', 'longitude', 'jurisdiction', 'is_current', 'status', 'metadata'];

    protected function casts(): array
    {
        return ['historical_names' => 'array', 'latitude' => 'decimal:7', 'longitude' => 'decimal:7', 'is_current' => 'boolean', 'metadata' => 'array'];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function names(): HasMany
    {
        return $this->hasMany(PlaceName::class);
    }

    public function hasCoordinates(): bool
    {
        return $this->latitude !== null && $this->longitude !== null;
    }

    public function mapUrl(): ?string
    {
        return $this->hasCoordinates()
            ? 'https://www.openstreetmap.org/?mlat='.$this->latitude.'&mlon='.$this->longitude.'#map=12/'.$this->latitude.'/'.$this->longitude
            : null;
    }
}
