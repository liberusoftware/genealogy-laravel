<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Place extends \FamilyTree365\LaravelGedcom\Models\Place
{
    use BelongsToTenant;
    use HasFactory;

    /**
     * Get the parent place that this place belongs to.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Place::class, 'parent_id');
    }

    /**
     * Get the child places for this place.
     */
    public function children(): HasMany
    {
        return $this->hasMany(Place::class, 'parent_id');
    }

    /**
     * Get all historical and current names for the place.
     */
    public function names(): HasMany
    {
        return $this->hasMany(PlaceName::class);
    }

    /**
     * Get the default/current name for the place.
     */
    public function defaultName(): HasOne
    {
        return $this->hasOne(PlaceName::class)->where('is_default', true);
    }

    /**
     * Get the type of the place (e.g., City, Country).
     */
    public function type(): BelongsTo
    {
        return $this->belongsTo(PlaceType::class, 'place_type_id');
    }
}
