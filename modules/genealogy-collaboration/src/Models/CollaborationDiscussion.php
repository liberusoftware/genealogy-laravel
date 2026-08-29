<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Collaboration\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Liberu\Genealogy\GenealogyCore\Concerns\BelongsToTeam;

final class CollaborationDiscussion extends Model
{
    public const STATUSES = ['open', 'resolved', 'archived'];

    use BelongsToTeam;
    use HasUuids;
    use SoftDeletes;

    protected $table = 'genealogy_collaboration_discussions';

    protected $fillable = ['team_id', 'space_id', 'proposal_id', 'author_id', 'body', 'status', 'metadata'];

    protected function casts(): array
    {
        return ['metadata' => 'array'];
    }
}
