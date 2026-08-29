<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Collaboration\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Liberu\Genealogy\GenealogyCore\Concerns\BelongsToTeam;

final class CollaborationInvitation extends Model
{
    public const ROLES = ['viewer', 'contributor', 'reviewer', 'editor', 'owner'];

    public const STATUSES = ['pending', 'accepted', 'revoked', 'expired'];

    use BelongsToTeam;
    use HasUuids;

    protected $table = 'genealogy_collaboration_invitations';

    protected $fillable = ['team_id', 'space_id', 'email', 'role', 'status', 'invited_by', 'expires_at', 'accepted_at', 'revoked_at'];

    protected function casts(): array
    {
        return ['expires_at' => 'datetime', 'accepted_at' => 'datetime', 'revoked_at' => 'datetime'];
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending')->where(fn (Builder $query) => $query->whereNull('expires_at')->orWhere('expires_at', '>', now()));
    }
}
