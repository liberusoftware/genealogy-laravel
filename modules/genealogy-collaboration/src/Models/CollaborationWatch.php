<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Collaboration\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Liberu\Genealogy\GenealogyCore\Concerns\BelongsToTeam;

final class CollaborationWatch extends Model
{
    use BelongsToTeam;
    use HasUuids;

    protected $table = 'genealogy_collaboration_watches';

    protected $fillable = ['team_id', 'user_id', 'watchable_type', 'watchable_id'];
}
