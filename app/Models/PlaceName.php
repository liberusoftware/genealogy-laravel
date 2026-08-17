<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlaceName extends Model
{
    use BelongsToTenant;
    use HasFactory;

    protected $fillable = [
        'place_id',
        'name',
        'jurisdiction',
        'start_date',
        'end_date',
        'is_default',
        'team_id',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function place(): BelongsTo
    {
        return $this->belongsTo(Place::class);
    }
}
