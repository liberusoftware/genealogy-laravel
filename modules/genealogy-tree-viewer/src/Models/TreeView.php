<?php

declare(strict_types=1);

namespace Liberu\Genealogy\TreeViewer\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Liberu\Genealogy\GenealogyCore\Concerns\BelongsToTeam;
use Liberu\Genealogy\People\Models\Person;

final class TreeView extends Model
{
    use BelongsToTeam;
    use HasUuids;
    use SoftDeletes;

    protected $table = 'genealogy_trees';

    protected $fillable = ['team_id', 'name', 'status', 'root_person_id', 'is_public', 'metadata'];

    protected function casts(): array
    {
        return ['metadata' => 'array', 'is_public' => 'boolean'];
    }

    protected $attributes = ['status' => 'draft', 'is_public' => false];

    public function rootPerson(): BelongsTo
    {
        return $this->belongsTo(Person::class, 'root_person_id');
    }
}
