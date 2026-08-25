<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Research\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Liberu\Genealogy\GenealogyCore\Concerns\BelongsToTeam;

final class ResearchEntry extends Model
{
    use BelongsToTeam;
    use HasUuids;
    use SoftDeletes;

    public const KINDS = ['question', 'plan', 'task', 'log', 'correspondence', 'negative_search', 'finding'];

    public const STATUSES = ['open', 'in_progress', 'completed', 'cancelled'];

    protected $table = 'research_entries';

    protected $fillable = ['team_id', 'research_project_id', 'kind', 'title', 'body', 'status', 'due_date', 'completed_at', 'metadata'];

    protected function casts(): array
    {
        return ['due_date' => 'date', 'completed_at' => 'datetime', 'metadata' => 'array'];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(ResearchProject::class, 'research_project_id');
    }
}
