<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Collaboration\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Liberu\Genealogy\GenealogyCore\Concerns\BelongsToTeam;

final class CollaborationProposal extends Model
{
    public const STATUSES = ['proposed', 'in_review', 'approved', 'rejected', 'withdrawn'];

    use BelongsToTeam;
    use HasUuids;
    use SoftDeletes;

    protected $table = 'genealogy_collaboration_proposals';

    protected $fillable = [
        'team_id', 'proposer_id', 'reviewer_id', 'title', 'description', 'status', 'reviewed_at', 'metadata',
    ];

    protected function casts(): array
    {
        return ['reviewed_at' => 'datetime', 'metadata' => 'array'];
    }

    public function scopePendingReview(Builder $query): Builder
    {
        return $query->whereIn('status', ['proposed', 'in_review']);
    }
}
