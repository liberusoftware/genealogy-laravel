<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Liberu\Foundation\Organizations\Models\Team;

class TeamIntegration extends Model
{
    /** @var list<string> */
    protected $fillable = ['team_id', 'provider', 'label', 'credentials', 'enabled'];

    /** @var array<string, string> */
    protected $casts = [
        'credentials' => 'encrypted:array',
        'enabled' => 'boolean',
    ];

    /** @return BelongsTo<Team, $this> */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}
