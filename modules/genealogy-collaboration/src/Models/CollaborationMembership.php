<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Collaboration\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Liberu\Genealogy\GenealogyCore\Concerns\BelongsToTeam;

final class CollaborationMembership extends Model
{
    public const ROLES = ['viewer', 'contributor', 'reviewer', 'editor', 'owner'];

    use BelongsToTeam;
    use HasUuids;

    protected $table = 'genealogy_collaboration_memberships';

    protected $fillable = ['team_id', 'space_id', 'user_id', 'role', 'status', 'joined_at'];

    protected function casts(): array
    {
        return ['joined_at' => 'datetime'];
    }
}
